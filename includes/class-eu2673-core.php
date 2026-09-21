<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Core {

    private static $instance = null;

    public static function init() {
        if ( self::$instance ) return self::$instance;
        self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->load_textdomain();

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
    }

    private function load_textdomain() {
        // WordPress 4.6+ loads translations automatically for plugins hosted on WordPress.org
    }

    private function woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }

    public function notice_woocommerce_missing() {
        echo '<div class="notice notice-error"><p>' .
            esc_html__( 'Withdrawal Button requires WooCommerce to be active.', 'eu2673-withdrawal-button' ) .
        '</p></div>';
    }
}
