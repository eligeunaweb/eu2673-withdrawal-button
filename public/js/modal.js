(function(){
    if ( typeof EU2673_Modal === 'undefined' ) return;
    var ajaxUrl = EU2673_Modal.ajaxUrl;
    var i18n    = EU2673_Modal.i18n;
    var overlay  = document.getElementById('wbdesist-overlay');
    if ( !overlay ) return;
    var msgEl    = document.getElementById('wbdesist-msg');
    var orderId  = null, nonce = null, triggerBtn = null;

    function getFocusable() {
        return Array.from( overlay.querySelectorAll('button:not([disabled]),[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])') );
    }
    function trapFocus(e) {
        if ( e.key !== 'Tab' ) return;
        var f = getFocusable(), first = f[0], last = f[f.length-1];
        if ( e.shiftKey ) { if ( document.activeElement === first ) { e.preventDefault(); last.focus(); } }
        else { if ( document.activeElement === last ) { e.preventDefault(); first.focus(); } }
    }
    function openModal() {
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', trapFocus);
        setTimeout(function(){ var f = getFocusable(); if(f.length) f[0].focus(); }, 50);
    }
    function closeModal() {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
        document.removeEventListener('keydown', trapFocus);
        if ( triggerBtn ) setTimeout(function(){ triggerBtn.focus(); }, 50);
    }
    function showMsg(txt, ok) {
        msgEl.style.cssText = 'display:block;padding:12px 16px;border-radius:6px;font-size:14px;margin-top:12px;' + (ok ? 'background:#e8f5e9;color:#2e7d32;' : 'background:#ffebee;color:#c62828;');
        msgEl.textContent = txt;
    }

    document.getElementById('wbdesist-cancel').addEventListener('click', closeModal);
    document.getElementById('wbdesist-close').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e){ if(e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && overlay.style.display === 'flex') closeModal(); });

    document.addEventListener('click', function(e) {
        var btn = null;
        if ( e.target.classList.contains('wbdesist-btn') ) btn = e.target;
        else if ( e.target.closest ) btn = e.target.closest('.wbdesist-btn');
        if ( !btn || btn.id === 'wbdesist-confirm' ) return;
        e.preventDefault(); e.stopImmediatePropagation();
        orderId    = btn.getAttribute('data-order-id');
        nonce      = btn.getAttribute('data-nonce');
        triggerBtn = btn;
        msgEl.style.display = 'none';
        var conf = document.getElementById('wbdesist-confirm');
        conf.disabled = false;
        conf.textContent = i18n.confirm;
        conf.style.display = 'inline-block';
        document.getElementById('wbdesist-cancel').textContent = i18n.cancel;
        openModal();
    }, true);

    document.getElementById('wbdesist-confirm').addEventListener('click', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        if ( !orderId ) { showMsg(i18n.unknownError, false); return; }
        var btn = this;
        btn.disabled = true; btn.setAttribute('aria-busy', 'true');
        btn.textContent = i18n.processing;
        var fd = new FormData();
        fd.append('action',   'eu2673_process_withdrawal');
        fd.append('order_id', orderId);
        fd.append('nonce',    nonce);
        fd.append('reason',   '');
        fetch(ajaxUrl, { method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'} })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if ( data.success ) {
                showMsg(data.data.message, true);
                btn.style.display = 'none'; btn.removeAttribute('aria-busy');
                document.getElementById('wbdesist-cancel').textContent = i18n.close;
                document.querySelectorAll('.wbdesist-btn[data-order-id="'+orderId+'"]').forEach(function(b){
                    b.disabled = true;
                    b.textContent = i18n.exercised;
                    b.style.opacity = '0.6';
                    b.setAttribute('aria-disabled', 'true');
                });
            } else {
                showMsg(data.data ? data.data.message : i18n.unknownError, false);
                btn.disabled = false; btn.removeAttribute('aria-busy');
                btn.textContent = i18n.confirm;
            }
        })
        .catch(function(){
            showMsg(i18n.connectionError, false);
            btn.disabled = false; btn.removeAttribute('aria-busy');
            btn.textContent = i18n.confirm;
        });
    }, true);
})();
