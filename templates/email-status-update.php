<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Variables: $order, $request, $new_status, $site_name, $site_url

$eu2673_status_labels = [
    'approved'  => __( 'Approved',  'eu2673-withdrawal-button' ),
    'rejected'  => __( 'Rejected',  'eu2673-withdrawal-button' ),
    'completed' => __( 'Completed', 'eu2673-withdrawal-button' ),
];
$eu2673_status_colors = [
    'approved'  => '#27ae60',
    'rejected'  => '#e74c3c',
    'completed' => '#2980b9',
];
$eu2673_status_messages = [
    'approved'  => __( 'Your withdrawal request has been <strong>approved</strong>. We will process the full refund within a maximum of <strong>14 calendar days</strong>.', 'eu2673-withdrawal-button' ),
    'rejected'  => __( 'Your withdrawal request has been <strong>rejected</strong>. If you have any questions, please contact us.', 'eu2673-withdrawal-button' ),
    'completed' => __( 'Your withdrawal request has been <strong>completed</strong>. The refund has been processed correctly.', 'eu2673-withdrawal-button' ),
];
$eu2673_status_label   = isset( $eu2673_status_labels[ $new_status ] )   ? $eu2673_status_labels[ $new_status ]   : $new_status;
$eu2673_status_color   = isset( $eu2673_status_colors[ $new_status ] )   ? $eu2673_status_colors[ $new_status ]   : '#1a1a2e';
$eu2673_status_message = isset( $eu2673_status_messages[ $new_status ] ) ? $eu2673_status_messages[ $new_status ] : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
  .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .header { background: #1a1a2e; color: #fff; padding: 30px; text-align: center; }
  .header h1 { margin: 0; font-size: 22px; }
  .badge { border-radius: 20px; padding: 6px 16px; display: inline-block; font-size: 14px; margin-top: 12px; font-weight: bold; }
  .body { padding: 30px; }
  .highlight-box { border-left: 4px solid; padding: 16px 20px; border-radius: 0 6px 6px 0; margin: 20px 0; background: #f8f9fa; }
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
    <div class="badge" style="background:<?php echo esc_attr( $eu2673_status_color ); ?>;color:#fff;"><?php echo esc_html( $eu2673_status_label ); ?></div>
  </div>
  <div class="body">
    <p><?php esc_html_e( 'Dear', 'eu2673-withdrawal-button' ); ?> <strong><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></strong>,</p>
    <p><?php esc_html_e( 'The status of your withdrawal request has been updated.', 'eu2673-withdrawal-button' ); ?></p>
    <div class="highlight-box" style="border-color:<?php echo esc_attr( $eu2673_status_color ); ?>"><?php echo wp_kses_post( $eu2673_status_message ); ?></div>
    <table class="info-table">
      <tr><td><?php esc_html_e( 'Request number', 'eu2673-withdrawal-button' ); ?></td><td>#<?php echo esc_html( $request->id ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Order', 'eu2673-withdrawal-button' ); ?></td><td>#<?php echo esc_html( $order->get_id() ); ?></td></tr>
      <tr><td><?php esc_html_e( 'New status', 'eu2673-withdrawal-button' ); ?></td><td style="color:<?php echo esc_attr( $eu2673_status_color ); ?>"><?php echo esc_html( $eu2673_status_label ); ?></td></tr>
      <tr><td><?php esc_html_e( 'Updated on', 'eu2673-withdrawal-button' ); ?></td><td><?php echo esc_html( current_time( 'j F Y, H:i' ) ); ?></td></tr>
    </table>
    <?php if ( $request->admin_notes ) : ?>
    <p><strong><?php esc_html_e( 'Admin note:', 'eu2673-withdrawal-button' ); ?></strong><br><?php echo esc_html( $request->admin_notes ); ?></p>
    <?php endif; ?>
    <p><?php
    printf(
        wp_kses_post(
            /* translators: %s: contact page URL */
            __( 'If you have any questions, you can <a href="%s">contact us</a>.', 'eu2673-withdrawal-button' )
        ),
        esc_url( $site_url . '/contact' )
    ); ?></p>
    <p><?php
    printf(
        /* translators: %s: store name */
        esc_html__( 'Thank you for trusting %s.', 'eu2673-withdrawal-button' ),
        esc_html( $site_name )
    ); ?></p>
  </div>
  <div class="footer">
    <p><?php esc_html_e( 'Automatic withdrawal request update.', 'eu2673-withdrawal-button' ); ?></p>
    <p><?php esc_html_e( 'Legal basis: EU Directive 2023/2673 | Art. 102 TRLGDCU', 'eu2673-withdrawal-button' ); ?></p>
    <p><a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html( $site_name ); ?></a></p>
  </div>
</div>
</body>
</html>
