<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_eu2673_process_withdrawal',        [ $this, 'process' ] );
        add_action( 'wp_ajax_nopriv_eu2673_process_withdrawal', [ $this, 'process' ] );
        // download_annex_b eliminado — funcionalidad Pro
    }

    public function process() {
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $nonce    = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        $valid    = wp_verify_nonce( $nonce, 'eu2673_withdrawal_' . $order_id )
                 || wp_verify_nonce( $nonce, 'eu2673_withdrawal_nonce' );
        if ( ! $valid ) {
            wp_send_json_error( [ 'message' => __( 'Solicitud no válida (nonce).', 'eu2673-withdrawal-button' ) ] );
        }

        if ( ! $order_id ) {
            wp_send_json_error( [ 'message' => __( 'Pedido no encontrado.', 'eu2673-withdrawal-button' ) ] );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( [ 'message' => __( 'Pedido no encontrado.', 'eu2673-withdrawal-button' ) ] );
        }

        // Verificar que el pedido pertenece al usuario actual (o es guest con clave válida)
        if ( is_user_logged_in() ) {
            if ( get_current_user_id() !== (int) $order->get_user_id() ) {
                wp_send_json_error( [ 'message' => __( 'No tienes permiso para esta acción.', 'eu2673-withdrawal-button' ) ] );
            }
        } else {
            $order_key = sanitize_text_field( wp_unslash( $_POST['order_key'] ?? '' ) );
            if ( $order->get_order_key() !== $order_key ) {
                wp_send_json_error( [ 'message' => __( 'No tienes permiso para esta acción.', 'eu2673-withdrawal-button' ) ] );
            }
        }

        // Verificar si ya se ejerció el desistimiento
        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) {
            wp_send_json_error( [ 'message' => __( 'Ya has ejercido el desistimiento para este pedido.', 'eu2673-withdrawal-button' ), 'code' => 'already_done' ] );
        }

        // Verificar elegibilidad (plazo)
        $frontend = new EU2673_Frontend();
        if ( ! $frontend->order_is_eligible( $order ) ) {
            wp_send_json_error( [ 'message' => __( 'Este pedido no es elegible para desistimiento.', 'eu2673-withdrawal-button' ), 'code' => 'expired' ] );
        }

        $reason = sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) );

        // 1. Guardar en BD
        $request_id = $this->save_to_db( $order, $reason );

        // 2. Marcar meta del pedido
        $order->update_meta_data( '_eu2673_withdrawal_done', current_time( 'mysql' ) );
        $order->update_meta_data( '_eu2673_withdrawal_request_id', $request_id );
        $order->add_order_note(
            sprintf(
                /* translators: 1: request ID, 2: reason */
                __( 'Withdrawal exercised by customer. Request ID: %1$d. Reason: %2$s', 'eu2673-withdrawal-button' ),
                $request_id,
                $reason ?: __( 'Not provided', 'eu2673-withdrawal-button' )
            )
        );

        // 3. Cambiar estado del pedido
        if ( get_option( 'eu2673_change_order_status', '1' ) ) {
            $new_status = get_option( 'eu2673_new_order_status', 'wc-refunded' );
            $new_status = str_replace( 'wc-', '', $new_status );
            $order->update_status( $new_status, __( 'Estado cambiado automáticamente por solicitud de desistimiento.', 'eu2673-withdrawal-button' ) );
        }

        $order->save();

        // 4. Enviar emails
        $emails = new EU2673_Emails();
        if ( get_option( 'eu2673_send_customer_email', '1' ) ) {
            $emails->send_customer_email( $order, $reason, $request_id );
        }
        if ( get_option( 'eu2673_send_admin_email', '1' ) ) {
            $emails->send_admin_email( $order, $reason, $request_id );
        }

        wp_send_json_success( [
            'message'    => __( 'Tu solicitud de desistimiento ha sido registrada correctamente. Recibirás un email de confirmación en breve.', 'eu2673-withdrawal-button' ),
            'request_id' => $request_id,
        ] );
    }

    private function save_to_db( $order, $reason ) {
        global $wpdb;
        $ip         = WC_Geolocation::get_ip_address();
        $user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
        $created_at = current_time( 'mysql' );

        // request_hash (SHA-256) is a Pro feature — not generated in Lite
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
            $wpdb->prefix . 'withdrawal_requests',
            [
                'order_id'       => $order->get_id(),
                'customer_name'  => $order->get_formatted_billing_full_name(),
                'customer_email' => $order->get_billing_email(),
                'reason'         => $reason,
                'status'         => 'pending',
                'ip_address'     => $ip,
                'user_agent'     => $user_agent,
                'created_at'     => $created_at,
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
        return (int) $wpdb->insert_id;
    }
}
