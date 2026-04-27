(function () {
    const MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024;
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const ALLOWED_DOC_EXT = ['pdf', 'doc', 'docx'];

    window.validateFile = function (file) {
        if (!file) return false;
        if (file.size > MAX_ATTACHMENT_BYTES) {
            alert('Файл занадто великий. Максимум 5 МБ.');
            return false;
        }
        const isImage = ALLOWED_IMAGE_TYPES.includes(file.type);
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        const isDoc = ALLOWED_DOC_EXT.includes(ext);
        if (!isImage && !isDoc) {
            alert('Дозволено лише зображення (JPG, PNG, GIF, WEBP) або документи (PDF, DOC, DOCX).');
            return false;
        }
        return true;
    };
})();
