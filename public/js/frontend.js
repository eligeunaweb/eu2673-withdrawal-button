/* Withdrawal Button Lite - Frontend JS */
(function ($) {
    'use strict';
    var currentOrderId  = null;
    var currentNonce    = null;
    var currentOrderKey = null;

    $(document).ready(function () {
        $('#wb-modal-overlay').hide().css('display', 'none');
        $('body').css('overflow', '');

        $('body').on('click', '.wb-withdrawal-btn:not(#wb-btn-confirm)', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            currentOrderId  = $(this).attr('data-order-id');
            currentNonce    = $(this).attr('data-nonce');
            currentOrderKey = $(this).attr('data-order-key') || '';
            openModal();
        });

        $('body').on('click', '#wb-btn-cancel, .wb-modal-close', function (e) {
            e.preventDefault();
            closeModal();
        });

        $('body').on('click', '#wb-modal-overlay', function (e) {
            if ($(e.target).is('#wb-modal-overlay')) closeModal();
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        $('body').on('click', '#wb-btn-confirm', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (!currentOrderId) { showMessage('Error: no se pudo identificar el pedido.', 'error'); return; }
            $(this).prop('disabled', true).text('Procesando…');
            $.ajax({
                url:    WB.ajax_url,
                method: 'POST',
                data: { action: 'eu2673_process_withdrawal', order_id: currentOrderId, nonce: currentNonce, order_key: currentOrderKey, reason: $('#wb-reason').val() || '' },
                success: function (res) {
                    if (res.success) {
                        showMessage(res.data.message, 'success');
                        $('#wb-btn-confirm').hide();
                        $('#wb-btn-cancel').text('Cerrar');
                        $('.wb-withdrawal-btn[data-order-id="' + currentOrderId + '"]').prop('disabled', true).text('✓ Desistimiento ejercido').css('opacity', '0.6');
                    } else {
                        showMessage(res.data ? res.data.message : 'Error.', 'error');
                        $('#wb-btn-confirm').prop('disabled', false).text('Confirmar desistimiento');
                    }
                },
                error: function () {
                    showMessage('Error de conexión.', 'error');
                    $('#wb-btn-confirm').prop('disabled', false).text('Confirmar desistimiento');
                }
            });
        });

        function openModal() {
            $('#wb-modal-message').hide();
            $('#wb-btn-confirm').prop('disabled', false).text('Confirmar desistimiento').show();
            $('#wb-btn-cancel').text('Cancelar').show();
            $('#wb-modal-overlay').css('display', 'flex');
            $('body').css('overflow', 'hidden');
        }
        function closeModal() {
            $('#wb-modal-overlay').hide();
            $('body').css('overflow', '');
            currentOrderId  = null;
            currentNonce    = null;
            currentOrderKey = null;
        }
        function showMessage(text, type) {
            $('#wb-modal-message').removeClass('success error').addClass(type).text(text).show();
        }
    });
}(jQuery));
