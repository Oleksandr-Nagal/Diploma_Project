function initEmojiPicker(config) {
    const { buttonId, inputId, emojis, isPremium } = config;
    const btn = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    if (!btn || !input) return;

    let picker = null;

    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (picker) {
            picker.remove();
            picker = null;
            return;
        }

        picker = document.createElement('div');
        picker.className = 'emoji-picker';
        picker.innerHTML = buildPickerHTML(emojis, isPremium);
        btn.parentElement.style.position = 'relative';
        btn.parentElement.appendChild(picker);

        picker.querySelectorAll('.emoji-item').forEach(item => {
            item.addEventListener('click', () => {
                input.value += item.textContent;
                input.focus();
            });
        });

        picker.querySelectorAll('.sticker-item').forEach(item => {
            item.addEventListener('click', () => {
                input.value = '[sticker:' + item.dataset.code + ']';
                input.form.dispatchEvent(new Event('submit'));
                picker.remove();
                picker = null;
            });
        });

        setTimeout(() => {
            document.addEventListener('click', closePicker);
        }, 0);
    });

    function closePicker(e) {
        if (picker && !picker.contains(e.target) && e.target !== btn) {
            picker.remove();
            picker = null;
            document.removeEventListener('click', closePicker);
        }
    }

    function buildPickerHTML(emojis, isPremium) {
        let html = '<div class="emoji-picker-section">';
        emojis.standard.forEach(em => {
            html += `<span class="emoji-item">${em}</span>`;
        });
        html += '</div>';

        if (isPremium && emojis.stickers) {
            html += '<div class="emoji-picker-divider"><span>★ Стікери PRO</span></div>';
            html += '<div class="emoji-picker-section sticker-grid">';
            for (const [code, data] of Object.entries(emojis.stickers)) {
                html += `<div class="sticker-item" data-code="${code}" title="${data.label}">
                    <div class="sticker-preview" style="background:linear-gradient(135deg,${data.color1},${data.color2});">
                        <i class="fas ${data.icon}"></i>
                    </div>
                </div>`;
            }
            html += '</div>';
        }

        return html;
    }
}
