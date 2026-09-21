<?php defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wrap wb-admin-wrap">
    <h1><?php esc_html_e( 'Configuración — Botón de Desistimiento', 'eu2673-withdrawal-button' ); ?></h1>

    <div style="background:linear-gradient(135deg,#1a1a2e 0%,#2c3e7a 100%);border-radius:10px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;box-shadow:0 4px 12px rgba(44,62,122,0.25);">
        <div style="flex:1;">
            <p style="margin:0 0 6px;font-size:15px;font-weight:700;color:#fff;">
                ⚠️ <?php esc_html_e( 'Versión Lite — documentación probatoria limitada', 'eu2673-withdrawal-button' ); ?>
            </p>
            <p style="margin:0 0 10px;font-size:13px;color:rgba(255,255,255,0.8);line-height:1.6;">
                <?php esc_html_e( 'La versión Pro incluye documentación que puede ser determinante en caso de litigio. AdaptaTuWeb no asume responsabilidad por su ausencia en la versión Lite.', 'eu2673-withdrawal-button' ); ?>
            </p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ PDF legal oficial</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Hash SHA-256</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Formulario Anexo B</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Trazabilidad emails</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Antifraude</span>
            </div>
        </div>
        <a href="https://adaptatuweb.com/withdrawal-button-es/" target="_blank"
           style="background:#f59e0b;color:#1a1a2e;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:800;text-decoration:none;white-space:nowrap;flex-shrink:0;box-shadow:0 2px 8px rgba(245,158,11,0.4);">
            ⭐ <?php esc_html_e( 'Actualizar a Pro — €39', 'eu2673-withdrawal-button' ); ?> →
        </a>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field( 'eu2673_settings_save' ); ?>
        <div class="wb-settings-grid">
            <div class="wb-settings-card">
                <h2><?php esc_html_e( 'General', 'eu2673-withdrawal-button' ); ?></h2>
                <table class="form-table">
                    <tr><th><?php esc_html_e( 'Días de desistimiento', 'eu2673-withdrawal-button' ); ?></th><td><input type="number" name="eu2673_withdrawal_days" value="<?php echo esc_attr( get_option( 'eu2673_withdrawal_days', 14 ) ); ?>" min="1" max="30" class="small-text"><p class="description"><?php esc_html_e( 'El mínimo legal son 14 días naturales.', 'eu2673-withdrawal-button' ); ?></p></td></tr>
                    <tr><th><?php esc_html_e( 'Inicio del plazo', 'eu2673-withdrawal-button' ); ?></th><td><select name="eu2673_start_from"><option value="order_date" <?php selected( get_option( 'eu2673_start_from', 'order_date' ), 'order_date' ); ?>><?php esc_html_e( 'Fecha del pedido (por defecto)', 'eu2673-withdrawal-button' ); ?></option><option value="delivery_date" <?php selected( get_option( 'eu2673_start_from', 'order_date' ), 'delivery_date' ); ?>><?php esc_html_e( 'Fecha de entrega (cuando el pedido se completa)', 'eu2673-withdrawal-button' ); ?></option></select><p class="description"><?php esc_html_e( 'Para productos físicos, la ley permite iniciar el plazo desde la recepción del producto.', 'eu2673-withdrawal-button' ); ?></p></td></tr>
                    <tr><th><?php esc_html_e( 'Mostrar inicio del plazo en email', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_show_start_date_email" value="1" <?php checked( get_option( 'eu2673_show_start_date_email', '0' ), '1' ); ?>> <?php esc_html_e( 'Incluir en el email al cliente la fecha desde la que empieza a contar el plazo', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                    <tr><th><?php esc_html_e( 'Días de cortesía', 'eu2673-withdrawal-button' ); ?></th><td><input type="number" name="eu2673_grace_days" value="<?php echo esc_attr( get_option( 'eu2673_grace_days', 0 ) ); ?>" min="0" max="30" class="small-text"><p class="description"><?php esc_html_e( 'Días adicionales voluntarios que se suman a los 14 días legales.', 'eu2673-withdrawal-button' ); ?></p></td></tr>
                    <tr><th><?php esc_html_e( 'Texto del botón', 'eu2673-withdrawal-button' ); ?></th><td><input type="text" name="eu2673_button_text" value="<?php echo esc_attr( get_option( 'eu2673_button_text' ) ); ?>" class="regular-text"></td></tr>
                    <tr><th><?php esc_html_e( 'Pedir motivo al cliente', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_require_reason" value="1" <?php checked( '1', get_option( 'eu2673_require_reason' ) ); ?>> <?php esc_html_e( 'Mostrar campo de motivo (siempre opcional)', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                    <tr><th><?php esc_html_e( 'Mostrar en pedidos de invitados', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_show_on_guest_orders" value="1" <?php checked( '1', get_option( 'eu2673_show_on_guest_orders', '1' ) ); ?>> <?php esc_html_e( 'Mostrar en la página de gracias (thank-you)', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                </table>
            </div>
            <div class="wb-settings-card">
                <h2><?php esc_html_e( 'WooCommerce', 'eu2673-withdrawal-button' ); ?></h2>
                <table class="form-table">
                    <tr><th><?php esc_html_e( 'Cambiar estado del pedido', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_change_order_status" value="1" <?php checked( '1', get_option( 'eu2673_change_order_status', '1' ) ); ?>> <?php esc_html_e( 'Cambiar automáticamente el estado del pedido', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                    <tr><th><?php esc_html_e( 'Nuevo estado del pedido', 'eu2673-withdrawal-button' ); ?></th><td><?php $statuses = wc_get_order_statuses(); $saved_status = get_option( 'eu2673_new_order_status', 'wc-refunded' ); ?><select name="eu2673_new_order_status"><?php foreach ( $statuses as $slug => $label ) : ?><option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $saved_status, $slug ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td></tr>
                </table>
            </div>
            <div class="wb-settings-card">
                <h2><?php esc_html_e( 'Emails', 'eu2673-withdrawal-button' ); ?></h2>
                <table class="form-table">
                    <tr><th><?php esc_html_e( 'Email al cliente', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_send_customer_email" value="1" <?php checked( '1', get_option( 'eu2673_send_customer_email', '1' ) ); ?>> <?php esc_html_e( 'Enviar acuse de recibo al cliente', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                    <tr><th><?php esc_html_e( 'Asunto email cliente', 'eu2673-withdrawal-button' ); ?></th><td><input type="text" name="eu2673_email_subject_customer" value="<?php echo esc_attr( get_option( 'eu2673_email_subject_customer' ) ); ?>" class="regular-text"><p class="description"><?php esc_html_e( 'Usa {order_id} para el número de pedido.', 'eu2673-withdrawal-button' ); ?></p></td></tr>
                    <tr><th><?php esc_html_e( 'Email de actualización de estado', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_send_status_email" value="1" <?php checked( '1', get_option( 'eu2673_send_status_email', '1' ) ); ?>> <?php esc_html_e( 'Notificar al cliente cuando el admin cambia el estado de su solicitud', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                    <tr><th><?php esc_html_e( 'Email al administrador', 'eu2673-withdrawal-button' ); ?></th><td><label><input type="checkbox" name="eu2673_send_admin_email" value="1" <?php checked( '1', get_option( 'eu2673_send_admin_email', '1' ) ); ?>> <?php esc_html_e( 'Notificar al administrador de la tienda', 'eu2673-withdrawal-button' ); ?></label></td></tr>
                    <tr><th><?php esc_html_e( 'Email del administrador', 'eu2673-withdrawal-button' ); ?></th><td><input type="email" name="eu2673_admin_email" value="<?php echo esc_attr( get_option( 'eu2673_admin_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"></td></tr>
                    <tr><th><?php esc_html_e( 'Asunto email admin', 'eu2673-withdrawal-button' ); ?></th><td><input type="text" name="eu2673_email_subject_admin" value="<?php echo esc_attr( get_option( 'eu2673_email_subject_admin' ) ); ?>" class="regular-text"><p class="description"><?php esc_html_e( 'Usa {order_id} y {customer_name}.', 'eu2673-withdrawal-button' ); ?></p></td></tr>
                </table>
            </div>
        </div>
        <p class="submit"><button type="submit" name="eu2673_save_settings" class="button button-primary button-large"><?php esc_html_e( 'Guardar configuración', 'eu2673-withdrawal-button' ); ?></button></p>
    </form>
</div>
