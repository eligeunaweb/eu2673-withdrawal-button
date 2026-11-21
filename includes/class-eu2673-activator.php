<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Activator {

    public static function activate() {
        self::create_tables();
        self::set_defaults();
        // Página pública para que los compradores sin cuenta puedan desistir durante todo el plazo.
        if ( class_exists( 'EU2673_Core' ) && post_type_exists( 'page' ) ) {
            // El título de la página se escribe en el idioma de la tienda.
            add_filter( 'load_textdomain_mofile', [ 'EU2673_Core', 'fallback_mofile' ], 10, 2 );
            unload_textdomain( 'eu2673-withdrawal-button' );
            load_textdomain( 'eu2673-withdrawal-button', EU2673_PLUGIN_DIR . 'languages/eu2673-withdrawal-button-' . get_locale() . '.mo', get_locale() );
            EU2673_Core::create_withdrawal_page();
        }
        // Los enlaces permanentes se regeneran en la siguiente carga, cuando el punto de «Mi cuenta» ya está registrado.
        update_option( 'eu2673_flush_rewrite', '1' );
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $table   = $wpdb->prefix . 'withdrawal_requests';

        // Tabla principal de solicitudes (sin request_hash ni timezone — funcionalidades Pro)
        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            customer_name varchar(200) NOT NULL,
            customer_email varchar(200) NOT NULL,
            reason text,
            status varchar(30) NOT NULL DEFAULT 'pending',
            ip_address varchar(100),
            user_agent text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime,
            admin_notes text,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY customer_email (customer_email),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        if ( ! get_option( 'eu2673_db_version' ) ) {
            update_option( 'eu2673_db_version', EU2673_VERSION );
        }
    }

    private static function set_defaults() {
        $defaults = [
            'eu2673_withdrawal_days'        => 14,
            // Vacío = texto por defecto traducido al idioma de la tienda (EU2673_Core::text_option).
            'eu2673_button_text'            => '',
            'eu2673_send_admin_email'       => '1',
            'eu2673_change_order_status'    => '0',
            'eu2673_new_order_status'       => 'wc-on-hold',
            'eu2673_require_reason'         => '0',
            'eu2673_admin_email'            => get_option( 'admin_email' ),
            'eu2673_email_subject_customer' => '',
            'eu2673_email_subject_admin'    => '',
            'eu2673_exclude_statuses'       => [ 'wc-refunded', 'wc-cancelled', 'wc-failed' ],
            'eu2673_show_on_guest_orders'   => '1',
            'eu2673_send_status_email'      => '1',
            'eu2673_grace_days'             => 0,
            // Para bienes, el plazo empieza con la entrega (art. 9.2.b de la Directiva 2011/83/UE).
            'eu2673_start_from'             => 'delivery_date',
            'eu2673_footer_link'            => '1',
            'eu2673_email_link'             => '1',
        ];

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                update_option( $key, $value );
            }
        }
    }
}
