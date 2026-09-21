<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Variables available: $order, $reason, $request_id
$site_name = get_bloginfo( 'name' );
$site_url  = get_bloginfo( 'url' );
?>
<!DOCTYPE html>
<html>
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
    <div class="badge">✓ Solicitud de desistimiento registrada</div>
  </div>
  <div class="body">
    <p>Estimado/a <strong><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></strong>,</p>
    <p>Hemos recibido correctamente tu solicitud de desistimiento. Este email es tu acuse de recibo oficial conforme a la <strong>Directiva UE 2023/2673</strong>.</p>

    <div class="highlight-box">
      <strong>¿Qué ocurre ahora?</strong><br>
      Procesaremos el reembolso íntegro en un plazo máximo de <strong>14 días naturales</strong> desde la recepción de esta solicitud.
    </div>

    <table class="info-table">
      <tr><td>Nº de solicitud</td><td>#<?php echo esc_html( $request_id ); ?></td></tr>
      <tr><td>Pedido</td><td>#<?php echo esc_html( $order->get_id() ); ?></td></tr>
      <tr><td>Fecha de solicitud</td><td><?php echo esc_html( current_time( 'j \d\e F \d\e Y, H:i' ) ); ?></td></tr>
      <tr><td>Importe a reembolsar</td><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
      <?php if ( $reason ) : ?>
      <tr><td>Motivo indicado</td><td><?php echo esc_html( $reason ); ?></td></tr>
      <?php endif; ?>
      <?php if ( get_option( 'eu2673_show_start_date_email', '0' ) === '1' ) :
        $start_from  = get_option( 'eu2673_start_from', 'order_date' );
        $start_label = __( 'Inicio del plazo', 'eu2673-withdrawal-button' );
        $start_value = ( $start_from === 'delivery_date' )
            ? __( 'Fecha de entrega del producto', 'eu2673-withdrawal-button' )
            : esc_html( $order->get_date_created()->date_i18n( 'j \d\e F \d\e Y' ) );
      ?>
      <tr><td><?php echo esc_html( $start_label ); ?></td><td><?php echo esc_html( $start_value ); ?></td></tr>
      <?php endif; ?>
    </table>

    <p>Si tienes alguna pregunta, puedes <a href="<?php echo esc_url( $site_url . '/contacto' ); ?>">contactar con nosotros</a>.</p>
    <p>Gracias por confiar en <?php echo esc_html( $site_name ); ?>.</p>
  </div>
  <div class="footer">
    <p>Este email es el acuse de recibo oficial de tu solicitud de desistimiento.</p>
    <p>Base legal: Directiva UE 2023/2673 | Art. 102 TRLGDCU | Plazo de reembolso: 14 días naturales</p>
    <p><a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html( $site_name ); ?></a></p>
  </div>
</div>
</body>
</html>
