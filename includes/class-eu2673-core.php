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
            add_action( 'admin_notices', [ $this, 'notice_review_request' ] );
            add_action( 'admin_notices', [ $this, 'notice_pro_upsell' ] );
            add_action( 'wp_ajax_eu2673_dismiss_notice', [ $this, 'ajax_dismiss_notice' ] );
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

    public function ajax_dismiss_notice() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die();
        $notice = sanitize_key( $_POST['notice'] ?? '' );
        if ( $notice ) update_option( 'eu2673_dismissed_' . $notice, time() );
        wp_send_json_success();
    }

    public function notice_review_request() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( get_option( 'eu2673_dismissed_review' ) ) return;

        $installed = (int) get_option( 'eu2673_installed_date', 0 );
        if ( ! $installed ) {
            update_option( 'eu2673_installed_date', time() );
            return;
        }
        if ( ( time() - $installed ) < 14 * DAY_IN_SECONDS ) return;

        // Comprobar que hay al menos 1 solicitud gestionada
        global $wpdb;
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}eu2673_requests" );
        if ( $count < 1 ) return;
        ?>
        <div class="notice notice-info is-dismissible eu2673-notice" data-notice="review" style="border-left-color:#2271b1;padding:12px 16px;">
            <p style="margin:0;font-size:14px;">
                <strong>⭐ <?php esc_html_e( 'You\'re helping stores comply with EU law!', 'eu2673-withdrawal-button' ); ?></strong><br>
                <?php printf(
                    /* translators: %d: number of requests */
                    esc_html__( 'You\'ve already managed %d withdrawal request(s) with EU2673 Withdrawal Button. If it\'s been useful, a review on WordPress.org takes 1 minute and helps other store owners find it.', 'eu2673-withdrawal-button' ),
                    $count
                ); ?>
            </p>
            <p style="margin:8px 0 0;">
                <a href="https://wordpress.org/support/plugin/eu2673-withdrawal-button/reviews/#new-post" target="_blank" class="button button-primary"><?php esc_html_e( '⭐ Leave a review', 'eu2673-withdrawal-button' ); ?></a>
                &nbsp;
                <a href="#" class="eu2673-dismiss-notice button button-secondary" data-notice="review"><?php esc_html_e( 'Already done / Not now', 'eu2673-withdrawal-button' ); ?></a>
            </p>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.eu2673-dismiss-notice').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var notice = btn.getAttribute('data-notice');
                    btn.closest('.eu2673-notice').style.display = 'none';
                    fetch(ajaxurl, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=eu2673_dismiss_notice&notice=' + notice + '&_wpnonce=<?php echo wp_create_nonce("eu2673_dismiss"); ?>'});
                });
            });
        });
        </script>
        <?php
    }

    public function notice_pro_upsell() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( get_option( 'eu2673_dismissed_pro_upsell' ) ) return;

        // Solo en las páginas del plugin
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'eu2673' ) === false ) return;

        // Solo si hay solicitudes gestionadas
        global $wpdb;
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}eu2673_requests" );
        if ( $count < 1 ) return;
        ?>
        <div class="notice notice-warning eu2673-notice" data-notice="pro_upsell" style="border-left-color:#dba617;padding:16px;display:flex;align-items:flex-start;gap:16px;">
            <div style="font-size:32px;line-height:1;">🛡️</div>
            <div style="flex:1;">
                <p style="margin:0 0 6px;font-size:15px;font-weight:600;">
                    <?php printf(
                        esc_html__( 'You have managed %d withdrawal request(s) — do you have legal proof of all of them?', 'eu2673-withdrawal-button' ),
                        $count
                    ); ?>
                </p>
                <p style="margin:0 0 10px;color:#555;">
                    <?php esc_html_e( 'Without the legal PDF and SHA-256 hash, if a customer files a complaint you have no documented proof. The Pro version protects you:', 'eu2673-withdrawal-button' ); ?>
                    <strong><?php esc_html_e( 'official PDF acknowledgement, SHA-256 cryptographic hash, Annex B form and full email traceability.', 'eu2673-withdrawal-button' ); ?></strong>
                </p>
                <p style="margin:0;">
                    <a href="https://adaptatuweb.com/withdrawal-button-es/" target="_blank" class="button button-primary" style="background:#dba617;border-color:#dba617;">
                        <?php esc_html_e( '🔐 Upgrade to Pro — €39 one-time payment', 'eu2673-withdrawal-button' ); ?>
                    </a>
                    &nbsp;
                    <a href="#" class="eu2673-dismiss-notice button button-secondary" data-notice="pro_upsell"><?php esc_html_e( 'Remind me later', 'eu2673-withdrawal-button' ); ?></a>
                </p>
            </div>
        </div>
        <?php
    }

    public function notice_woocommerce_missing() {
        echo '<div class="notice notice-error"><p>' .
            esc_html__( 'Withdrawal Button requires WooCommerce to be active.', 'eu2673-withdrawal-button' ) .
        '</p></div>';
    }
}
