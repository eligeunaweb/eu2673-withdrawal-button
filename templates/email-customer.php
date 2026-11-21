<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Variables available: $order, $reason, $request_id
$site_name = get_bloginfo( 'name' );
$site_url  = get_bloginfo( 'url' );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $site_name ); ?></title>
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
  .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .header { background: #1a1a2e; color: #fff; padding: 30px; text-align: center; }
  .header h1 { margin: 0; font-size: 22px; }
  .badge { background: #e8f5e9; color: #2e7d32; border-radius: 20px; padding: 6px 16px; display: inline-block; font-size: 14px; margin-top: 12px; }
  .body { padding: 30px; }
  .highlight-box { background: #f8f9fa; border-left: 4px solid #1a1a2e; padding: 16px 20px; border-radius: 0 6px 6px 0; margin: 20px 0; }
  .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
  .info-table td { padding: 10px 14px; border-bottom: 1px solid #eee; font-size: 14px; }
  .info-table td:first-child { color: #666; width: 40%; }
  .info-table td:last-child { font-weight: bold; }
  .footer { background: #f8f9fa; padding: 20px 30px; font-size: 12px; color: #666; text-align: center; }
  .footer a { color: #1a1a2e; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1><?php echo esc_html( $site_name ); ?></h1>
    <div class="badge">✓ <?php esc_html_e( 'Solicitud de desistimiento registrada', 'eu2673-withdrawal-button' ); ?></div>
  </div>
  <div class="body">
    <p><?php
    printf(
        /* translators: %s: customer name */
        esc_html__( 'Hola, %s:', 'eu2673-withdrawal-button' ),
        '<strong>' . esc_html( $order->get_formatted_billing_full_name() ) . '</strong>'
    ); ?></p>
    <p><?php esc_html_e( 'Hemos recibido correctamente tu solicitud de desistimiento. Este correo es tu acuse de recibo conforme a la Directiva (UE) 2023/2673.', 'eu2673-withdrawal-button' ); ?></p>

    <div class="highlight-box">
      <strong><?php esc_html_e( '¿Qué ocurre ahora?', 'eu2673-withdrawal-button' ); ?></strong><br>
      <?php esc_html_e( 'Procesaremos el reembolso en un plazo máximo de 14 días naturales desde la recepción de esta solicitud.', 'eu2673-withdrawal-button' ); ?>
    </div>

    <table class="info-table">
      <tr><td><?php esc_html_e( 'Nº de solicitud', 'eu2673-withdrawal-button' ); ?></td><td>#<?php echo esc_html( $request_id ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Pedido', 'eu2673-withdrawal-button' ); ?></td><td>#<?php echo esc_html( $order->get_order_number() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Nombre', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Recibirás la confirmación en', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $order->get_billing_email() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Fecha y hora de la solicitud', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( EU2673_Core::format_datetime( $submitted_at ?? null ) ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Importe del pedido', 'eu2673-withdrawal-button' ); ?></td><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
      <?php if ( $reason ) : ?>
      <tr><td><?php esc_html_e( 'Motivo indicado', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( $reason ); ?></td></tr>
      <?php endif; ?>
    </table>

    <p><?php esc_html_e( 'Si tienes alguna pregunta, responde a este correo.', 'eu2673-withdrawal-button' ); ?></p>
    <p><?php
    printf(
        /* translators: %s: store name */
        esc_html__( 'Gracias por confiar en %s.', 'eu2673-withdrawal-button' ),
        esc_html( $site_name )
    ); ?></p>
  </div>
  <div class="footer">
    <p><?php esc_html_e( 'Este correo es el acuse de recibo de tu solicitud de desistimiento.', 'eu2673-withdrawal-button' ); ?></p>
    <p><?php echo esc_html( EU2673_Core::legal_basis() ); ?></p>
    <p><a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html( $site_name ); ?></a></p>
  </div>
</div>
</body>
</html>
