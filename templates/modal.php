<?php
defined( 'ABSPATH' ) || exit;
$eu2673_ask_reason = '1' === (string) get_option( 'eu2673_require_reason', '0' );
?>
<div id="wbdesist-overlay" class="wbdesist-overlay" hidden>
    <div class="wbdesist-dialog" role="dialog" aria-modal="true" aria-labelledby="wbdesist-title" aria-describedby="wbdesist-desc">
        <button id="wbdesist-close" type="button" class="wbdesist-close" aria-label="<?php esc_attr_e( 'Cerrar ventana de desistimiento', 'eu2673-withdrawal-button' ); ?>">&#x2715;</button>
        <div class="wbdesist-head">
            <h2 id="wbdesist-title"><?php echo esc_html( EU2673_Core::text_option( 'eu2673_button_text' ) ); ?></h2>
        </div>
        <div class="wbdesist-body">
            <div id="wbdesist-desc">
                <p><?php esc_html_e( 'Tienes derecho a desistir de este contrato sin necesidad de indicar el motivo.', 'eu2673-withdrawal-button' ); ?></p>
                <p><?php esc_html_e( 'Revisa tus datos antes de confirmar:', 'eu2673-withdrawal-button' ); ?></p>
            </div>
            <dl class="wbdesist-summary">
                <dt><?php esc_html_e( 'Nombre', 'eu2673-withdrawal-button' ); ?></dt>
                <dd id="wbdesist-name"></dd>
                <dt><?php esc_html_e( 'Pedido', 'eu2673-withdrawal-button' ); ?></dt>
                <dd id="wbdesist-order"></dd>
                <dt><?php esc_html_e( 'Recibirás la confirmación en', 'eu2673-withdrawal-button' ); ?></dt>
                <dd id="wbdesist-email"></dd>
            </dl>
            <?php if ( $eu2673_ask_reason ) : ?>
            <p class="wbdesist-reason">
                <label for="wbdesist-reason"><?php esc_html_e( 'Motivo (opcional)', 'eu2673-withdrawal-button' ); ?></label>
                <textarea id="wbdesist-reason" rows="3" maxlength="1000"></textarea>
            </p>
            <?php endif; ?>
            <p class="wbdesist-refund"><?php esc_html_e( 'Al confirmar, recibirás un email de acuse de recibo y nuestro equipo procesará el reembolso en un máximo de 14 días naturales.', 'eu2673-withdrawal-button' ); ?></p>
            <div id="wbdesist-msg" class="wbdesist-msg" role="alert" hidden></div>
        </div>
        <div class="wbdesist-actions">
            <button id="wbdesist-cancel" type="button" class="wbdesist-cancel"><?php esc_html_e( 'Cancelar', 'eu2673-withdrawal-button' ); ?></button>
            <button id="wbdesist-confirm" type="button" class="wbdesist-confirm"><?php esc_html_e( 'Confirmar desistimiento', 'eu2673-withdrawal-button' ); ?></button>
        </div>
    </div>
</div>
