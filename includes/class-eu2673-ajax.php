<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_eu2673_process_withdrawal',        [ $this, 'process' ] );
        add_action( 'wp_ajax_nopriv_eu2673_process_withdrawal', [ $this, 'process' ] );
    }

    public function process() {
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $nonce    = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if ( ! wp_verify_nonce( $nonce, 'eu2673_withdrawal_' . $order_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Solicitud no válida (nonce).', 'eu2673-withdrawal-button' ) ] );
        }

        $order = $order_id ? wc_get_order( $order_id ) : null;
        if ( ! $order instanceof WC_Order ) {
            wp_send_json_error( [ 'message' => __( 'Pedido no encontrado.', 'eu2673-withdrawal-button' ) ] );
        }

        // Puede desistir el titular con sesión iniciada o quien tenga la clave del pedido
        // (compra como invitado, página de «pedido recibido» o formulario público).
        $order_key = sanitize_text_field( wp_unslash( $_POST['order_key'] ?? '' ) );
        $is_owner  = is_user_logged_in() && get_current_user_id() === (int) $order->get_user_id();
        $has_key   = '' !== $order_key && hash_equals( (string) $order->get_order_key(), $order_key );
        if ( ! $is_owner && ! $has_key ) {
            wp_send_json_error( [ 'message' => __( 'No tienes permiso para esta acción.', 'eu2673-withdrawal-button' ) ] );
        }

        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) {
            wp_send_json_error( [ 'message' => __( 'Ya has ejercido el desistimiento para este pedido.', 'eu2673-withdrawal-button' ), 'code' => 'already_done' ] );
        }

        $frontend = new EU2673_Frontend( false );
        if ( ! $frontend->order_is_eligible( $order ) ) {
            wp_send_json_error( [ 'message' => __( 'Este pedido no es elegible para desistimiento.', 'eu2673-withdrawal-button' ), 'code' => 'expired' ] );
        }

        $reason = '';
        if ( '1' === (string) get_option( 'eu2673_require_reason', '0' ) ) {
            $reason = mb_substr( sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) ), 0, 1000 );
        }

        // Marcar primero el pedido para que una segunda pulsación simultánea no cree otra solicitud.
        $order->update_meta_data( '_eu2673_withdrawal_done', current_time( 'mysql' ) );
        $order->save_meta_data();

        $request_id = $this->save_to_db( $order, $reason );
        if ( ! $request_id ) {
            $order->delete_meta_data( '_eu2673_withdrawal_done' );
            $order->save_meta_data();
            wp_send_json_error( [ 'message' => __( 'Error desconocido.', 'eu2673-withdrawal-button' ) ] );
        }

        $order->update_meta_data( '_eu2673_withdrawal_request_id', $request_id );
        $order->add_order_note(
            sprintf(
                /* translators: 1: request ID, 2: reason */
                __( 'Withdrawal exercised by customer. Request ID: %1$d. Reason: %2$s', 'eu2673-withdrawal-button' ),
                $request_id,
                $reason ?: __( 'Not provided', 'eu2673-withdrawal-button' )
            )
        );

        // Cambio de estado opcional; nunca a «Reembolsado» de forma automática.
        $new_status = (string) get_option( 'eu2673_new_order_status', 'wc-on-hold' );
        if ( '1' === (string) get_option( 'eu2673_change_order_status', '0' ) && 'wc-refunded' !== $new_status && $new_status ) {
            $order->update_status( substr( $new_status, 0, 3 ) === 'wc-' ? substr( $new_status, 3 ) : $new_status, __( 'Estado cambiado automáticamente por solicitud de desistimiento.', 'eu2673-withdrawal-button' ) );
        }
        $order->save();

        // Acuse de recibo al cliente: obligatorio (art. 11 bis.4 de la Directiva 2011/83/UE).
        $emails = new EU2673_Emails();
        $emails->send_customer_email( $order, $reason, $request_id );
        if ( '1' === (string) get_option( 'eu2673_send_admin_email', '1' ) ) {
            $emails->send_admin_email( $order, $reason, $request_id );
        }

        wp_send_json_success( [
            'message'    => __( 'Tu solicitud de desistimiento ha sido registrada correctamente. Recibirás un email de confirmación en breve.', 'eu2673-withdrawal-button' ),
            'request_id' => $request_id,
        ] );
    }

    private function save_to_db( $order, $reason ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $ok = $wpdb->insert(
            $wpdb->prefix . 'withdrawal_requests',
            [
                'order_id'       => $order->get_id(),
                'customer_name'  => $order->get_formatted_billing_full_name(),
                'customer_email' => $order->get_billing_email(),
                'reason'         => $reason,
                'status'         => 'pending',
                'ip_address'     => WC_Geolocation::get_ip_address(),
                'user_agent'     => mb_substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 500 ),
                'created_at'     => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
        return $ok ? (int) $wpdb->insert_id : 0;
    }
}
