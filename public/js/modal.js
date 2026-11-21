/* EU2673 Withdrawal Button — ventana de desistimiento (art. 11 bis de la Directiva 2011/83/UE) */
(function () {
    'use strict';
    if ( typeof EU2673_Modal === 'undefined' ) return;
    var i18n    = EU2673_Modal.i18n;
    var overlay = document.getElementById( 'wbdesist-overlay' );

    // Un solo botón por pedido aunque el servidor lo haya pintado varias veces.
    var seen = {};
    document.querySelectorAll( '.wbdesist-section[data-order-id]' ).forEach( function ( el ) {
        var id = el.getAttribute( 'data-order-id' );
        if ( seen[ id ] ) { el.parentNode.removeChild( el ); } else { seen[ id ] = true; }
    } );

    // Botón de respaldo para temas que no ejecutan el gancho del detalle del pedido.
    var fallback = document.getElementById( 'eu2673-fallback' );
    if ( fallback && ! document.querySelector( '.wbdesist-section' ) ) {
        var table = document.querySelector( '.woocommerce-order-details, .woocommerce table.shop_table' );
        if ( table ) table.parentNode.insertBefore( fallback.content.cloneNode( true ), table.nextSibling );
    }

    // Imprimir el formulario modelo (anexo I.B) solo, sin el resto de la página.
    document.addEventListener( 'click', function ( e ) {
        var btn = e.target.closest ? e.target.closest( '.eu2673-print-annex' ) : null;
        if ( ! btn ) return;
        var panel = document.getElementById( btn.getAttribute( 'data-target' ) );
        if ( ! panel ) return;
        var w = window.open( '', '_blank' );
        if ( ! w ) { window.print(); return; }
        var d = w.document;
        d.open();
        d.write( '<!doctype html><html><head><meta charset="utf-8"><title></title>' +
            '<style>body{font:14px/1.6 Arial,sans-serif;max-width:700px;margin:32px auto;padding:0 16px;color:#000}h1{font-size:18px}</style></head><body></body></html>' );
        d.close();
        d.documentElement.lang = document.documentElement.lang;
        d.title = i18n.annexTitle;
        var h = d.createElement( 'h1' );
        h.textContent = i18n.annexTitle;
        d.body.appendChild( h );
        d.body.appendChild( d.importNode( panel, true ) );
        w.focus();
        w.print();
    } );

    if ( ! overlay ) return;
    var msgEl    = document.getElementById( 'wbdesist-msg' );
    var titleEl  = document.getElementById( 'wbdesist-title' );
    var confirmB = document.getElementById( 'wbdesist-confirm' );
    var cancelB  = document.getElementById( 'wbdesist-cancel' );
    var reasonEl = document.getElementById( 'wbdesist-reason' );
    var current  = null, triggerBtn = null;
    titleEl.setAttribute( 'tabindex', '-1' );

    function focusables() {
        return Array.prototype.filter.call(
            overlay.querySelectorAll( 'button,textarea' ),
            function ( el ) { return ! el.disabled && ! el.hidden; }
        );
    }
    function onKey( e ) {
        if ( e.key === 'Escape' ) { closeModal(); return; }
        if ( e.key !== 'Tab' ) return;
        var f = focusables();
        if ( ! f.length ) return;
        var first = f[0], last = f[ f.length - 1 ];
        if ( e.shiftKey && ( document.activeElement === first || document.activeElement === titleEl ) ) { e.preventDefault(); last.focus(); }
        else if ( ! e.shiftKey && document.activeElement === last ) { e.preventDefault(); first.focus(); }
    }
    function openModal() {
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        document.addEventListener( 'keydown', onKey );
        setTimeout( function () { titleEl.focus(); }, 30 );
    }
    function closeModal() {
        overlay.hidden = true;
        document.body.style.overflow = '';
        document.removeEventListener( 'keydown', onKey );
        var back = triggerBtn;
        if ( back ) setTimeout( function () { back.focus(); }, 30 );
    }
    function showMsg( txt, ok ) {
        msgEl.hidden = false;
        msgEl.className = 'wbdesist-msg ' + ( ok ? 'is-ok' : 'is-error' );
        msgEl.textContent = txt;
    }
    function setText( id, txt ) { document.getElementById( id ).textContent = txt || '—'; }

    cancelB.addEventListener( 'click', closeModal );
    document.getElementById( 'wbdesist-close' ).addEventListener( 'click', closeModal );
    overlay.addEventListener( 'click', function ( e ) { if ( e.target === overlay ) closeModal(); } );

    document.addEventListener( 'click', function ( e ) {
        var btn = e.target.closest ? e.target.closest( 'button.wbdesist-btn[data-order-id]' ) : null;
        if ( ! btn || btn.disabled ) return;
        e.preventDefault();
        current = {
            orderId:  btn.getAttribute( 'data-order-id' ),
            nonce:    btn.getAttribute( 'data-nonce' ),
            orderKey: btn.getAttribute( 'data-order-key' ) || ''
        };
        triggerBtn = btn;
        setText( 'wbdesist-name', btn.getAttribute( 'data-customer-name' ) );
        setText( 'wbdesist-order', '#' + ( btn.getAttribute( 'data-order-number' ) || current.orderId ) );
        setText( 'wbdesist-email', btn.getAttribute( 'data-email' ) );
        if ( reasonEl ) { reasonEl.value = ''; reasonEl.disabled = false; }
        msgEl.hidden = true;
        confirmB.hidden = false;
        confirmB.removeAttribute( 'aria-disabled' );
        confirmB.removeAttribute( 'aria-busy' );
        confirmB.textContent = i18n.confirm;
        cancelB.textContent = i18n.cancel;
        openModal();
    } );

    confirmB.addEventListener( 'click', function () {
        if ( ! current ) { showMsg( i18n.unknownError, false ); return; }
        // aria-disabled en vez de disabled: el foco se queda en el botón mientras se procesa.
        if ( confirmB.getAttribute( 'aria-disabled' ) === 'true' ) return;
        confirmB.setAttribute( 'aria-disabled', 'true' );
        confirmB.setAttribute( 'aria-busy', 'true' );
        confirmB.textContent = i18n.processing;
        var fd = new FormData();
        fd.append( 'action', 'eu2673_process_withdrawal' );
        fd.append( 'order_id', current.orderId );
        fd.append( 'nonce', current.nonce );
        fd.append( 'order_key', current.orderKey );
        fd.append( 'reason', reasonEl ? reasonEl.value : '' );
        fetch( EU2673_Modal.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( data ) {
                confirmB.removeAttribute( 'aria-busy' );
                confirmB.removeAttribute( 'aria-disabled' );
                if ( data && data.success ) {
                    showMsg( data.data.message, true );
                    confirmB.hidden = true;
                    if ( reasonEl ) reasonEl.disabled = true;
                    cancelB.textContent = i18n.close;
                    cancelB.focus();
                    document.querySelectorAll( '.wbdesist-btn[data-order-id="' + current.orderId + '"]' ).forEach( function ( b ) {
                        b.disabled = true;
                        b.textContent = i18n.exercised;
                    } );
                    // El botón original queda desactivado: al cerrar, el foco va al encabezado de la página.
                    triggerBtn = null;
                } else {
                    showMsg( data && data.data ? data.data.message : i18n.unknownError, false );
                    confirmB.textContent = i18n.confirm;
                }
            } )
            .catch( function () {
                confirmB.removeAttribute( 'aria-busy' );
                confirmB.removeAttribute( 'aria-disabled' );
                showMsg( i18n.connectionError, false );
                confirmB.textContent = i18n.confirm;
            } );
    } );
})();
