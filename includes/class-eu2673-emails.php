<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Emails {

    public function send_customer_email( $order, $reason, $request_id ) {
        $to      = $order->get_billing_email();
        $subject = $this->parse_subject(
            get_option( 'eu2673_email_subject_customer', 'Confirmación de desistimiento - Pedido #{order_id}' ),
            $order
        );

        ob_start();
        include EU2673_PLUGIN_DIR . 'templates/email-customer.php';
        $body = ob_get_clean();

        // PDF adjunto eliminado — funcionalidad Pro
        $this->send( $to, $subject, $body );
    }

    public function send_admin_email( $order, $reason, $request_id ) {
        $to      = get_option( 'eu2673_admin_email', get_option( 'admin_email' ) );
        $subject = $this->parse_subject(
            get_option( 'eu2673_email_subject_admin', '[Desistimiento] Pedido #{order_id} - {customer_name}' ),
            $order
        );

        ob_start();
        include EU2673_PLUGIN_DIR . 'templates/email-admin.php';
        $body = ob_get_clean();

        $this->send( $to, $subject, $body );
    }

    public function send_status_update_email( $request_id, $new_status ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $request = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}withdrawal_requests WHERE id = %d",
            $request_id
        ) );
        if ( ! $request ) return;

        $order = wc_get_order( $request->order_id );
        if ( ! $order ) return;

        $status_subjects = [
            'approved'  => __( '[Desistimiento aprobado] Pedido #{order_id}', 'eu2673-withdrawal-button' ),
            'rejected'  => __( '[Desistimiento rechazado] Pedido #{order_id}', 'eu2673-withdrawal-button' ),
            'completed' => __( '[Desistimiento completado] Pedido #{order_id}', 'eu2673-withdrawal-button' ),
        ];
        $subject   = $this->parse_subject( $status_subjects[ $new_status ] ?? '[Desistimiento] Pedido #{order_id}', $order );
        $site_name = get_bloginfo( 'name' );
        $site_url  = get_bloginfo( 'url' );

        ob_start();
        include EU2673_PLUGIN_DIR . 'templates/email-status-update.php';
        $body = ob_get_clean();

        $this->send( $request->customer_email, $subject, $body );
        // log_email eliminado — trazabilidad legal es funcionalidad Pro
    }

    private function send( $to, $subject, $body, $attachments = [] ) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
        ];
        wp_mail( $to, $subject, $body, $headers, $attachments );
    }

    private function parse_subject( $subject, $order ) {
        return str_replace(
            [ '{order_id}', '{customer_name}' ],
            [ $order->get_id(), $order->get_formatted_billing_full_name() ],
            $subject
        );
    }
}
