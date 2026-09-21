<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$site_name = get_bloginfo( 'name' );
$order_url = admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );
?>
<!DOCTYPE html>
<html>
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
  <div class="header"><h1>⚠ Nueva solicitud de desistimiento</h1></div>
  <div class="body">
    <p>Se ha registrado una nueva solicitud de desistimiento en <strong><?php echo esc_html( $site_name ); ?></strong>.</p>
    <table class="info-table">
      <tr><td>Nº solicitud</td><td>#<?php echo esc_html( $request_id ); ?></td></tr>
      <tr><td>Pedido</td><td>#<?php echo esc_html( $order->get_id() ); ?></td></tr>
      <tr><td>Cliente</td><td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td></tr>
      <tr><td>Email cliente</td><td><?php echo esc_html( $order->get_billing_email() ); ?></td></tr>
      <tr><td>Teléfono</td><td><?php echo esc_html( $order->get_billing_phone() ?: '—' ); ?></td></tr>
      <tr><td>Importe pedido</td><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
      <tr><td>Fecha solicitud</td><td><?php echo esc_html( current_time( 'j \d\e F \d\e Y, H:i' ) ); ?></td></tr>
      <tr><td>Motivo</td><td><?php echo esc_html( $reason ?: 'No indicado' ); ?></td></tr>
    </table>
    <p><strong>Recuerda:</strong> debes procesar el reembolso en un máximo de <strong>14 días naturales</strong>.</p>
    <table border="0" cellspacing="0" cellpadding="0" style="margin-top:16px;"><tr><td bgcolor="#1a1a2e" style="border-radius:6px;padding:12px 24px;"><a href="<?php echo esc_url( $order_url ); ?>" style="font-size:14px;font-family:Arial,sans-serif;color:#ffffff;text-decoration:none;font-weight:bold;display:inline-block;">Ver pedido en WooCommerce &rarr;</a></td></tr></table>
  </div>
  <div class="footer"><p>Generado automáticamente por Withdrawal Button Lite | <?php echo esc_html( $site_name ); ?></p></div>
</div>
</body>
</html>
