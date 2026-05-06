(function () {
    var overlay = document.createElement('div');
    overlay.id = 'chatLightbox';
    overlay.style.cssText = 'display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;cursor:zoom-out;';
    var img = document.createElement('img');
    img.style.cssText = 'max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 0 40px rgba(108,92,231,0.4);';
    overlay.appendChild(img);
    document.body.appendChild(overlay);

    overlay.addEventListener('click', function () {
        overlay.style.display = 'none';
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.style.display === 'flex') {
            overlay.style.display = 'none';
        }
    });

    document.addEventListener('click', function (e) {
        var target = e.target;
        if (target.tagName === 'IMG' && target.closest('.chat-bubble') && target.alt === 'Зображення') {
            img.src = target.src;
            overlay.style.display = 'flex';
        }
    });
})();
