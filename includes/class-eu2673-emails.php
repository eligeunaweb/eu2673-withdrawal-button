<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Emails {

    /** Acuse de recibo al cliente (art. 11 bis.4): contenido de la declaración y fecha y hora. */
    public function send_customer_email( $order, $reason, $request_id ) {
        $to      = $order->get_billing_email();
        $subject = $this->parse_subject( EU2673_Core::text_option( 'eu2673_email_subject_customer' ), $order );
        $submitted_at = time();

        ob_start();
        include EU2673_PLUGIN_DIR . 'templates/email-customer.php';
        $body = ob_get_clean();

        return $this->send( $to, $subject, $body, EU2673_Core::from_address() );
    }

    public function send_admin_email( $order, $reason, $request_id ) {
        $to = get_option( 'eu2673_admin_email' );
        if ( ! is_email( $to ) ) {
            $to = get_option( 'admin_email' );
        }
        $subject = $this->parse_subject( EU2673_Core::text_option( 'eu2673_email_subject_admin' ), $order );
        $submitted_at = time();
        $order_url    = $order->get_edit_order_url();

        ob_start();
        include EU2673_PLUGIN_DIR . 'templates/email-admin.php';
        $body = ob_get_clean();

        // Responder al aviso escribe directamente al cliente.
        return $this->send( $to, $subject, $body, $order->get_billing_email() );
    }

    /** Correo al cliente cuando la tienda cambia el estado de su solicitud desde el escritorio. */
    public function send_status_update_email( $request_id, $new_status ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $request = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}withdrawal_requests WHERE id = %d",
            $request_id
        ) );
        if ( ! $request ) return false;

        $order = wc_get_order( $request->order_id );
        if ( ! $order ) return false;

        // Este correo sale desde el escritorio: se escribe en el idioma de la tienda,
        // no en el del administrador que cambia el estado.
        $switched = switch_to_locale( get_locale() );

        $status_subjects = [
            'approved'  => __( '[Desistimiento aprobado] Pedido #{order_id}', 'eu2673-withdrawal-button' ),
            'rejected'  => __( '[Desistimiento rechazado] Pedido #{order_id}', 'eu2673-withdrawal-button' ),
            'completed' => __( '[Desistimiento completado] Pedido #{order_id}', 'eu2673-withdrawal-button' ),
        ];
        $subject   = $this->parse_subject( $status_subjects[ $new_status ] ?? __( '[Desistimiento] Pedido #{order_id}', 'eu2673-withdrawal-button' ), $order );
        $site_name = get_bloginfo( 'name' );
        $site_url  = home_url( '/' );

        ob_start();
        include EU2673_PLUGIN_DIR . 'templates/email-status-update.php';
        $body = ob_get_clean();

        if ( $switched ) {
            restore_previous_locale();
        }

        return $this->send( $request->customer_email, $subject, $body, EU2673_Core::from_address() );
    }

    private function send( $to, $subject, $body, $reply_to = '' ) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . EU2673_Core::from_name() . ' <' . EU2673_Core::from_address() . '>',
        ];
        if ( $reply_to && is_email( $reply_to ) ) {
            $headers[] = 'Reply-To: ' . $reply_to;
        }
        return wp_mail( $to, wp_specialchars_decode( $subject, ENT_QUOTES ), $body, $headers );
    }

    private function parse_subject( $subject, $order ) {
        return str_replace(
            [ '{order_id}', '{customer_name}' ],
            [ $order->get_order_number(), $order->get_formatted_billing_full_name() ],
            $subject
        );
    }
}
