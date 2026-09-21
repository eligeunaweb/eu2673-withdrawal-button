<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Activator {

    public static function activate() {
        self::create_tables();
        self::set_defaults();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $table   = $wpdb->prefix . 'withdrawal_requests';

        // Tabla principal de solicitudes (sin request_hash ni timezone — funcionalidades Pro)
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id        BIGINT(20) UNSIGNED NOT NULL,
            customer_name   VARCHAR(200) NOT NULL,
            customer_email  VARCHAR(200) NOT NULL,
            reason          TEXT,
            status          VARCHAR(30) NOT NULL DEFAULT 'pending',
            ip_address      VARCHAR(100),
            user_agent      TEXT,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processed_at    DATETIME,
            admin_notes     TEXT,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY customer_email (customer_email),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'eu2673_db_version', EU2673_VERSION );
    }

    private static function set_defaults() {
        $defaults = [
            'eu2673_withdrawal_days'        => 14,
            'eu2673_button_text'            => __( 'Ejercer derecho de desistimiento', 'eu2673-withdrawal-button' ),
            'eu2673_button_position'        => 'order_details',
            'eu2673_send_customer_email'    => '1',
            'eu2673_send_admin_email'       => '1',
            'eu2673_change_order_status'    => '1',
            'eu2673_new_order_status'       => 'wc-refunded',
            'eu2673_require_reason'         => '0',
            'eu2673_admin_email'            => get_option( 'admin_email' ),
            'eu2673_email_subject_customer' => __( 'Confirmación de desistimiento - Pedido #{order_id}', 'eu2673-withdrawal-button' ),
            'eu2673_email_subject_admin'    => __( '[Desistimiento] Pedido #{order_id} - {customer_name}', 'eu2673-withdrawal-button' ),
            'eu2673_exclude_statuses'       => [ 'wc-completed', 'wc-refunded', 'wc-cancelled', 'wc-failed' ],
            'eu2673_show_on_guest_orders'   => '1',
            'eu2673_send_status_email'      => '1',
            'eu2673_grace_days'             => 0,
            'eu2673_start_from'             => 'order_date',
        ];

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                update_option( $key, $value );
            }
        }
    }
}
