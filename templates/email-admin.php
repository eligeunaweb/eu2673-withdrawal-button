<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$site_name = get_bloginfo( 'name' );
// $order_url llega desde EU2673_Emails::send_admin_email() (compatible con HPOS).
$order_url = $order_url ?? $order->get_edit_order_url();
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
  .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .header { background: #c0392b; color: #fff; padding: 24px 30px; }
  .header h1 { margin: 0; font-size: 20px; }
  .body { padding: 30px; }
  .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
  .info-table td { padding: 10px 14px; border-bottom: 1px solid #eee; font-size: 14px; }
  .info-table td:first-child { color: #666; width: 40%; }
  .info-table td:last-child { font-weight: bold; }
  .footer { background: #f8f9fa; padding: 16px 30px; font-size: 12px; color: #888; }
</style>
</head>
<body>
<div class="container">
  <div class="header"><h1>⚠ <?php esc_html_e( 'Nueva solicitud de desistimiento', 'eu2673-withdrawal-button' ); ?></h1></div>
  <div class="body">
    <p><?php
    printf(
        /* translators: %s: store name */
        esc_html__( 'Se ha registrado una nueva solicitud de desistimiento en %s.', 'eu2673-withdrawal-button' ),
        '<strong>' . esc_html( $site_name ) . '</strong>'
    ); ?></p>
    <table class="info-table">
      <tr><td><?php esc_html_e( 'Nº de solicitud', 'eu2673-withdrawal-button' ); ?></td><td>#<?php echo esc_html( $request_id ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Pedido', 'eu2673-withdrawal-button' ); ?></td><td>#<?php echo esc_html( $order->get_order_number() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Cliente', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Correo del cliente', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $order->get_billing_email() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Teléfono', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $order->get_billing_phone() ?: '—' ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Importe del pedido', 'eu2673-withdrawal-button' ); ?></td><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Fecha y hora de la solicitud', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( EU2673_Core::format_datetime( $submitted_at ?? null ) ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Motivo', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $reason ?: __( 'No indicado', 'eu2673-withdrawal-button' ) ); ?></td></tr>
    </table>
    <p><?php esc_html_e( 'Recuerda: debes procesar el reembolso en un máximo de 14 días naturales.', 'eu2673-withdrawal-button' ); ?></p>
    <table border="0" cellspacing="0" cellpadding="0" style="margin-top:16px;"><tr><td bgcolor="#1a1a2e" style="border-radius:6px;padding:12px 24px;"><a href="<?php echo esc_url( $order_url ); ?>" style="font-size:14px;font-family:Arial,sans-serif;color:#ffffff;text-decoration:none;font-weight:bold;display:inline-block;"><?php esc_html_e( 'Ver pedido en WooCommerce', 'eu2673-withdrawal-button' ); ?> &rarr;</a></td></tr></table>
  </div>
  <div class="footer"><p><?php
    printf(
        /* translators: %s: store name */
        esc_html__( 'Generado automáticamente por Withdrawal Button Lite | %s', 'eu2673-withdrawal-button' ),
        esc_html( $site_name )
    ); ?></p></div>
</div>
</body>
</html>
