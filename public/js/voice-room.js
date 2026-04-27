(function () {
    class VoiceRoom {
        constructor(config) {
            this.config = config;
            this.peers = {};
            this.stream = null;
            this.eventSource = null;
            this.muted = false;
            this.listeners = {};
        }

        on(event, cb) {
            (this.listeners[event] = this.listeners[event] || []).push(cb);
        }

        emit(event, ...args) {
            (this.listeners[event] || []).forEach(cb => cb(...args));
        }

        async join() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (err) {
                alert('Немає доступу до мікрофона');
                throw err;
            }

            const url = new URL(this.config.mercure_url);
            url.searchParams.append('topic', this.config.topic);
            url.searchParams.append('authorization', this.config.mercure_token);

            this.eventSource = new EventSource(url.toString());
            this.eventSource.onmessage = (e) => this.handleSignal(JSON.parse(e.data));
            this.eventSource.onerror = () => this.emit('error', 'SSE connection error');

            await this.sendSignal({ type: 'join' });
            this.emit('joined');
        }

        async handleSignal(data) {
            if (data.from === this.config.user_id) return;
            if (data.to && data.to !== this.config.user_id) return;

            switch (data.type) {
                case 'join':
                    await this.createOffer(data.from, data.from_name);
                    break;
                case 'offer':
                    await this.createAnswer(data.from, data.from_name, data.sdp);
                    break;
                case 'answer':
                    if (this.peers[data.from]) {
                        await this.peers[data.from].pc.setRemoteDescription({ type: 'answer', sdp: data.sdp });
                    }
                    break;
                case 'ice':
                    if (this.peers[data.from] && data.candidate) {
                        try {
                            await this.peers[data.from].pc.addIceCandidate(data.candidate);
                        } catch (err) {
                            console.warn('ICE add failed', err);
                        }
                    }
                    break;
                case 'leave':
                    this.removePeer(data.from);
                    break;
            }
        }

        createPeer(targetId, targetName) {
            const pc = new RTCPeerConnection({ iceServers: this.config.ice_servers });

            this.stream.getTracks().forEach(t => pc.addTrack(t, this.stream));

            pc.ontrack = ({ streams }) => {
                let audio = document.getElementById(`voice-audio-${targetId}`);
                if (!audio) {
                    audio = document.createElement('audio');
                    audio.id = `voice-audio-${targetId}`;
                    audio.autoplay = true;
                    audio.srcObject = streams[0];
                    document.body.appendChild(audio);
                }
            };

            pc.onicecandidate = ({ candidate }) => {
                if (candidate) this.sendSignal({ type: 'ice', to: targetId, candidate });
            };

            pc.onconnectionstatechange = () => {
                if (['failed', 'closed', 'disconnected'].includes(pc.connectionState)) {
                    this.removePeer(targetId);
                }
            };

            this.peers[targetId] = { pc, name: targetName };
            this.emit('peer_added', { id: targetId, name: targetName });
            return this.peers[targetId];
        }

        async createOffer(targetId, targetName) {
            const peer = this.createPeer(targetId, targetName);
            const offer = await peer.pc.createOffer();
            await peer.pc.setLocalDescription(offer);
            this.sendSignal({ type: 'offer', to: targetId, sdp: offer.sdp });
        }

        async createAnswer(fromId, fromName, offerSdp) {
            const peer = this.createPeer(fromId, fromName);
            await peer.pc.setRemoteDescription({ type: 'offer', sdp: offerSdp });
            const answer = await peer.pc.createAnswer();
            await peer.pc.setLocalDescription(answer);
            this.sendSignal({ type: 'answer', to: fromId, sdp: answer.sdp });
        }

        removePeer(id) {
            if (!this.peers[id]) return;
            try { this.peers[id].pc.close(); } catch (e) {}
            const audio = document.getElementById(`voice-audio-${id}`);
            if (audio) audio.remove();
            delete this.peers[id];
            this.emit('peer_removed', { id });
        }

        async sendSignal(data) {
            try {
                await fetch(this.config.signal_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(data),
                });
            } catch (err) {
                console.error('Signal send error', err);
            }
        }

        toggleMute() {
            this.muted = !this.muted;
            if (this.stream) {
                this.stream.getAudioTracks().forEach(t => { t.enabled = !this.muted; });
            }
            this.emit('mute_changed', this.muted);
            return this.muted;
        }

        leave() {
            this.sendSignal({ type: 'leave' });
            Object.keys(this.peers).forEach(id => this.removePeer(id));
            if (this.stream) this.stream.getTracks().forEach(t => t.stop());
            if (this.eventSource) this.eventSource.close();
            this.stream = null;
            this.eventSource = null;
            this.emit('left');
        }
    }

    window.VoiceRoom = VoiceRoom;
})();
