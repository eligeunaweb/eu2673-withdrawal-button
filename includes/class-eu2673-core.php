<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Core {

    const SHORTCODE = 'eu2673_withdrawal_form';

    private static $instance = null;

    public static function init() {
        if ( self::$instance ) return self::$instance;
        self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->load_textdomain();
        $this->maybe_migrate();

        if ( ! $this->woocommerce_active() ) {
            add_action( 'admin_notices', [ $this, 'notice_woocommerce_missing' ] );
            return;
        }

        new EU2673_Frontend();
        new EU2673_Ajax();
        new EU2673_Emails();
        new EU2673_Checkout();
        new EU2673_Product();
        new EU2673_Guest();

        if ( is_admin() ) {
            new EU2673_Admin();
        }

        add_action( 'wp_footer', [ $this, 'render_footer_link' ], 5 );
        add_action( 'woocommerce_email_after_order_table', [ $this, 'render_email_link' ], 20, 4 );
        add_action( 'init', [ $this, 'maybe_flush_rewrite_rules' ], 99 );
    }

    /* ── Idioma ─────────────────────────────────────────────────────────── */

    private function load_textdomain() {
        // Variantes regionales (fr_BE, fr_CA, de_AT, es_MX…) usan el archivo del idioma base.
        add_filter( 'load_textdomain_mofile', [ __CLASS__, 'fallback_mofile' ], 10, 2 );
        // Al cambiar de idioma (correos al cliente enviados desde el escritorio) se recarga el dominio.
        add_action( 'change_locale', [ __CLASS__, 'reload_textdomain' ] );
        self::reload_textdomain();
    }

    public static function reload_textdomain() {
        $domain = 'eu2673-withdrawal-button';
        // Con switch_to_locale() activo manda el idioma cambiado (get_locale);
        // si no, el del usuario o del sitio (determine_locale), como hace WordPress.
        // El gestor de idiomas no existe todavía en plugins_loaded: comprobarlo antes de usarlo.
        $switched = ! empty( $GLOBALS['wp_locale_switcher'] ) && function_exists( 'is_locale_switched' ) && is_locale_switched();
        $locale   = $switched ? get_locale() : determine_locale();
        $locale   = apply_filters( 'plugin_locale', $locale, $domain );
        unload_textdomain( $domain );
        load_textdomain( $domain, EU2673_PLUGIN_DIR . 'languages/' . $domain . '-' . $locale . '.mo', $locale );
    }

    public static function fallback_mofile( $mofile, $domain ) {
        if ( 'eu2673-withdrawal-button' !== $domain || is_readable( $mofile ) ) {
            return $mofile;
        }
        if ( preg_match( '/^(.*-)([a-z]{2,3})_[A-Za-z0-9_]+\.mo$/', $mofile, $m ) ) {
            $candidates = array_merge(
                glob( $m[1] . $m[2] . '.mo' ) ?: [],
                glob( $m[1] . $m[2] . '_*.mo' ) ?: []
            );
            if ( $candidates ) {
                return $candidates[0];
            }
        }
        return $mofile;
    }

    /* ── Textos y fechas ────────────────────────────────────────────────── */

    /**
     * Texto configurable con valor por defecto traducido.
     * Si el campo está vacío o conserva un texto por defecto de versiones anteriores
     * (guardado en español al activar), se usa el texto por defecto en el idioma actual.
     */
    public static function text_option( $key ) {
        $defaults = [
            // Rótulo del art. 11 bis.1 de la Directiva 2011/83/UE: «desistir del contrato aquí».
            'eu2673_button_text'            => [
                __( 'Desistir del contrato aquí', 'eu2673-withdrawal-button' ),
                [ 'Ejercer derecho de desistimiento', 'Exercise right of withdrawal' ],
            ],
            'eu2673_email_subject_customer' => [
                __( 'Confirmación de desistimiento - Pedido #{order_id}', 'eu2673-withdrawal-button' ),
                [ 'Confirmación de desistimiento - Pedido #{order_id}' ],
            ],
            'eu2673_email_subject_admin'    => [
                __( '[Desistimiento] Pedido #{order_id} - {customer_name}', 'eu2673-withdrawal-button' ),
                [ '[Desistimiento] Pedido #{order_id} - {customer_name}' ],
            ],
        ];
        if ( ! isset( $defaults[ $key ] ) ) {
            return get_option( $key, '' );
        }
        list( $default, $legacy ) = $defaults[ $key ];
        $value = trim( (string) get_option( $key, '' ) );
        if ( '' === $value || in_array( $value, $legacy, true ) ) {
            return $default;
        }
        return $value;
    }

    /** Fecha y hora con el formato y el idioma del sitio. */
    public static function format_datetime( $timestamp = null ) {
        return wp_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $timestamp ?: time() );
    }

    /** Fecha y hora guardadas en la tabla (hora local del sitio, formato MySQL). */
    public static function format_db_datetime( $mysql ) {
        $date = date_create_immutable( (string) $mysql, wp_timezone() );
        return $date ? self::format_datetime( $date->getTimestamp() ) : (string) $mysql;
    }

    /** Línea de base legal: la directiva siempre; el TRLGDCU solo si la tienda está en España. */
    public static function legal_basis() {
        $text = __( 'Base legal: artículo 11 bis de la Directiva 2011/83/UE, introducido por la Directiva (UE) 2023/2673', 'eu2673-withdrawal-button' );
        if ( function_exists( 'WC' ) && WC()->countries && 'ES' === WC()->countries->get_base_country() ) {
            $text .= ' | Art. 102 TRLGDCU';
        }
        return $text;
    }

    /* ── Remitente de los correos ───────────────────────────────────────── */

    public static function from_address() {
        $email = get_option( 'woocommerce_email_from_address' );
        return is_email( $email ) ? $email : get_option( 'admin_email' );
    }

    public static function from_name() {
        $name = get_option( 'woocommerce_email_from_name' );
        return $name ? wp_specialchars_decode( $name, ENT_QUOTES ) : get_bloginfo( 'name' );
    }

    /* ── Página pública de desistimiento ────────────────────────────────── */

    /**
     * ID de la página publicada que contiene el formulario de desistimiento, o 0.
     * Los compradores sin cuenta necesitan una forma permanente de desistir
     * durante todo el plazo (art. 11 bis.1 de la Directiva 2011/83/UE).
     */
    public static function withdrawal_page_id() {
        $id   = (int) get_option( 'eu2673_withdrawal_page_id', 0 );
        $page = $id ? get_post( $id ) : null;
        if ( $page && 'publish' === $page->post_status && has_shortcode( $page->post_content, self::SHORTCODE ) ) {
            return $id;
        }
        $found = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            's'              => '[' . self::SHORTCODE,
            'posts_per_page' => 5,
            'fields'         => 'ids',
        ] );
        foreach ( $found as $page_id ) {
            if ( has_shortcode( get_post_field( 'post_content', $page_id ), self::SHORTCODE ) ) {
                update_option( 'eu2673_withdrawal_page_id', (int) $page_id );
                return (int) $page_id;
            }
        }
        return 0;
    }

    /** Crea la página pública con el formulario. Devuelve su ID o 0. */
    public static function create_withdrawal_page() {
        $existing = self::withdrawal_page_id();
        if ( $existing ) return $existing;
        // El título se escribe en el idioma de la tienda, no en el del administrador.
        $switched = ! empty( $GLOBALS['wp_locale_switcher'] ) && switch_to_locale( get_locale() );
        $title    = __( 'Desistimiento del contrato', 'eu2673-withdrawal-button' );
        if ( $switched ) {
            restore_previous_locale();
        }
        $id = wp_insert_post( [
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_content' => '<!-- wp:shortcode -->[' . self::SHORTCODE . ']<!-- /wp:shortcode -->',
        ] );
        if ( is_wp_error( $id ) || ! $id ) return 0;
        update_option( 'eu2673_withdrawal_page_id', (int) $id );
        return (int) $id;
    }

    public function render_footer_link() {
        if ( '1' !== (string) get_option( 'eu2673_footer_link', '1' ) ) return;
        $page_id = self::withdrawal_page_id();
        if ( ! $page_id || is_page( $page_id ) ) return;
        printf(
            '<p class="eu2673-footer-link" style="text-align:center;margin:16px auto;font-size:14px;"><a href="%s">%s</a></p>',
            esc_url( get_permalink( $page_id ) ),
            esc_html( self::text_option( 'eu2673_button_text' ) )
        );
    }

    /** Enlace a la página de desistimiento en los correos de WooCommerce al cliente. */
    public function render_email_link( $order, $sent_to_admin, $plain_text, $email = null ) {
        if ( $sent_to_admin || ! $order instanceof WC_Order ) return;
        if ( '1' !== (string) get_option( 'eu2673_email_link', '1' ) ) return;
        $email_id = is_object( $email ) && isset( $email->id ) ? $email->id : '';
        if ( ! in_array( $email_id, [ 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order', 'customer_invoice' ], true ) ) return;
        $page_id = self::withdrawal_page_id();
        if ( ! $page_id ) return;
        $frontend = new EU2673_Frontend( false );
        if ( ! $frontend->order_is_eligible( $order ) ) return;
        $url  = get_permalink( $page_id );
        $text = __( 'Si quieres desistir de este pedido, puedes hacerlo en línea:', 'eu2673-withdrawal-button' );
        if ( $plain_text ) {
            echo "\n" . esc_html( $text ) . ' ' . esc_url_raw( $url ) . "\n\n";
            return;
        }
        printf(
            '<p style="margin:16px 0;">%s <a href="%s">%s</a></p>',
            esc_html( $text ),
            esc_url( $url ),
            esc_html( self::text_option( 'eu2673_button_text' ) )
        );
    }

    /* ── Migraciones ────────────────────────────────────────────────────── */

    private function woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    public function maybe_migrate() {
        // v1.2.0 — forzar desactivación del cambio automático de estado
        // si el valor es el antiguo 'wc-refunded' o '1'
        if ( get_option( 'eu2673_new_order_status' ) === 'wc-refunded' ) {
            update_option( 'eu2673_new_order_status', 'wc-on-hold' );
        }

        $db_version = (string) get_option( 'eu2673_db_version', '0' );
        if ( version_compare( $db_version, '1.2.3', '<' ) ) {
            if ( '1' === (string) get_option( 'eu2673_change_order_status' ) && version_compare( $db_version, '1.2.0', '<' ) ) {
                update_option( 'eu2673_change_order_status', '0' );
            }

            // Los pedidos «Completado» quedaban sin botón: el valor por defecto los excluía,
            // y el desistimiento de bienes suele pedirse justo después de la entrega.
            $excluded = get_option( 'eu2673_exclude_statuses' );
            if ( is_array( $excluded ) ) {
                update_option( 'eu2673_exclude_statuses', array_values( array_diff( $excluded, [ 'wc-completed' ] ) ) );
            }

            // La pantalla de ajustes de 1.2.x guardaba el plazo con otro nombre.
            $old_days = (int) get_option( 'eu2673_days', 0 );
            if ( $old_days > 14 ) {
                update_option( 'eu2673_withdrawal_days', $old_days );
            }
            foreach ( [ 'eu2673_days', 'eu2673_change_status', 'eu2673_new_status', 'eu2673_show_on_confirm', 'eu2673_installed_date', 'eu2673_dismissed_review', 'eu2673_dismissed_pro_upsell' ] as $obsolete ) {
                delete_option( $obsolete );
            }

            // Opciones nuevas (sin pisar las existentes).
            add_option( 'eu2673_footer_link', '1' );
            add_option( 'eu2673_email_link', '1' );

            // El punto de «Mi cuenta» necesita regenerar los enlaces permanentes.
            update_option( 'eu2673_flush_rewrite', '1' );
            update_option( 'eu2673_db_version', '1.2.3' );
        }
    }

    public function maybe_flush_rewrite_rules() {
        if ( get_option( 'eu2673_flush_rewrite' ) ) {
            delete_option( 'eu2673_flush_rewrite' );
            flush_rewrite_rules( false );
        }
    }

    public function notice_woocommerce_missing() {
        echo '<div class="notice notice-error"><p>' .
            esc_html__( 'Withdrawal Button requires WooCommerce to be active.', 'eu2673-withdrawal-button' ) .
        '</p></div>';
    }
}
