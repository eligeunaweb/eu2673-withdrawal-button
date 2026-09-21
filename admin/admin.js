/* global EU2673_Admin, jQuery */
(function ($) {
    'use strict';
    $(document).ready(function () {
        $(document).on('change', '.wb-status-select', function () {
            var $select = $(this);
            var id      = $select.data('id');
            var status  = $select.val();
            $.ajax({
                url:    ajaxurl,
                method: 'POST',
                data: {
                    action:     'eu2673_update_request_status',
                    nonce:      EU2673_Admin.nonce,
                    request_id: id,
                    status:     status
                },
                success: function (res) {
                    if (res.success) {
                        var $row   = $('#wb-row-' + id);
                        var $badge = $row.find('.wb-status');
                        var labels = { pending: 'Pendiente', approved: 'Aprobada', rejected: 'Rechazada', completed: 'Completada' };
                        $badge.attr('class', 'wb-status wb-status-' + status).text(labels[status] || status);
                    }
                }
            });
        });
    });
}(jQuery));
