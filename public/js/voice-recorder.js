(function () {
    const MAX_SECONDS = 60;

    function pickMimeType() {
        if (!window.MediaRecorder) return null;
        if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) return 'audio/webm;codecs=opus';
        if (MediaRecorder.isTypeSupported('audio/webm')) return 'audio/webm';
        if (MediaRecorder.isTypeSupported('audio/mp4')) return 'audio/mp4';
        return '';
    }

    window.initVoiceRecorder = function initVoiceRecorder(opts) {
        const btn = document.getElementById(opts.buttonId || 'voiceBtn');
        const icon = document.getElementById(opts.iconId || 'voiceIcon');
        const timer = document.getElementById(opts.timerId || 'voiceTimer');
        if (!btn || !icon || !timer) return;

        const sendUrl = opts.sendUrl;
        const onMessage = typeof opts.onMessage === 'function' ? opts.onMessage : null;
        const maxSeconds = opts.maxSeconds || MAX_SECONDS;

        let mediaRecorder = null;
        let chunks = [];
        let stream = null;
        let startedAt = 0;
        let tickInterval = null;
        let autoStopTimeout = null;

        function setIdle() {
            icon.className = 'fas fa-microphone';
            btn.classList.remove('btn-gf-danger');
            btn.classList.add('btn-gf-outline');
            timer.style.display = 'none';
            timer.textContent = '';
        }

        function setRecording() {
            icon.className = 'fas fa-stop';
            btn.classList.remove('btn-gf-outline');
            btn.classList.add('btn-gf-danger');
            timer.style.display = 'inline';
        }

        function tick() {
            const s = Math.floor((Date.now() - startedAt) / 1000);
            timer.textContent = s + 'с';
        }

        async function sendBlob(blob) {
            const fd = new FormData();
            fd.append('voice', blob, 'voice.webm');
            try {
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (data.message && onMessage) onMessage(data.message);
            } catch (err) {
                console.error('Voice send error:', err);
            }
        }

        async function start() {
            if (!navigator.mediaDevices || !window.MediaRecorder) {
                alert('Ваш браузер не підтримує запис звуку.');
                return;
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (err) {
                alert('Немає доступу до мікрофона.');
                return;
            }

            chunks = [];
            const mime = pickMimeType();
            mediaRecorder = mime ? new MediaRecorder(stream, { mimeType: mime }) : new MediaRecorder(stream);

            mediaRecorder.ondataavailable = e => { if (e.data.size > 0) chunks.push(e.data); };
            mediaRecorder.onstop = () => {
                clearInterval(tickInterval);
                clearTimeout(autoStopTimeout);
                if (stream) stream.getTracks().forEach(t => t.stop());
                setIdle();

                const blob = new Blob(chunks, { type: chunks[0]?.type || 'audio/webm' });
                if (blob.size < 1000) return;
                sendBlob(blob);
            };

            startedAt = Date.now();
            mediaRecorder.start();
            setRecording();
            tick();
            tickInterval = setInterval(tick, 500);
            autoStopTimeout = setTimeout(() => {
                if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
            }, maxSeconds * 1000);
        }

        btn.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
            } else {
                start();
            }
        });
    };

    window.renderVoiceBubble = function (attachmentUrl) {
        return '<audio controls preload="metadata" src="' + attachmentUrl + '" style="max-width:260px;"></audio>';
    };
})();
