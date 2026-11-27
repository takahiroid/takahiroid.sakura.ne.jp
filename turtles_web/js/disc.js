(function () {
    var overlay = document.getElementById('disc-modal');
    var body = document.getElementById('disc-modal-body');
    if (!overlay || !body) return;

    var lastFocus = null;
    var open = false;

    function idFromHash() {
        var match = location.hash.match(/^#detail-(.+)$/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    function showModal() {
        overlay.hidden = false;
        document.body.classList.add('disc-open');
        open = true;
        var closeButton = overlay.querySelector('.disc-modal-close');
        if (closeButton) closeButton.focus();
    }

    function hideModal() {
        if (!open && overlay.hidden) return;
        overlay.hidden = true;
        document.body.classList.remove('disc-open');
        open = false;
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    function render(html) {
        body.innerHTML = html + '<div class="disc-modal-close-bottom"><button type="button" class="disc-modal-close-btn">CLOSE</button></div>';
        var title = body.querySelector('#disc-detail-title');
        if (title) overlay.setAttribute('aria-labelledby', 'disc-detail-title');
    }

    function openModal(id) {
        lastFocus = document.activeElement;
        body.innerHTML = '<div class="disc-modal-loading"><span class="disc-modal-spinner"></span><span>読み込み中</span></div>';
        showModal();
        fetch('/disc.php?id=' + encodeURIComponent(id) + '&fragment=1', {
            headers: { 'X-Requested-With': 'fetch' }
        }).then(function (response) {
            if (!response.ok) throw new Error('load failed');
            return response.text();
        }).then(function (html) {
            if (idFromHash() !== id) return;
            render(html);
        }).catch(function () {
            if (idFromHash() !== id) return;
            body.innerHTML = '<p class="disc-missing">作品を読み込めませんでした。</p>';
        });
    }

    function closeModal() {
        if (/^#detail-/.test(location.hash)) {
            history.back();
            return;
        }
        hideModal();
    }

    function syncHash() {
        var id = idFromHash();
        if (id) openModal(id);
        else hideModal();
    }

    document.addEventListener('click', function (event) {
        var card = event.target.closest('.disc-item');
        if (!card || !document.body.contains(card)) return;
        var id = card.getAttribute('data-id');
        if (!id) return;
        event.preventDefault();
        if (idFromHash() === id) openModal(id);
        else location.hash = 'detail-' + id;
    });

    overlay.addEventListener('click', function (event) {
        if (event.target === overlay || event.target.closest('.disc-modal-close, .disc-modal-close-btn')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && open) closeModal();
    });

    window.addEventListener('hashchange', syncHash);
    syncHash();
})();
