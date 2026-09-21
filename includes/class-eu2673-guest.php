<?php
defined( 'ABSPATH' ) || exit;

/**
 * EU2673_Guest
 * Página pública de desistimiento para compradores sin cuenta.
 * Shortcode: [eu2673_withdrawal_form]
 */
class EU2673_Guest {

    public function __construct() {
        add_shortcode( 'eu2673_withdrawal_form', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_footer', [ $this, 'maybe_render_modal' ] );
    }

    public function maybe_render_modal(): void {
        if ( ! $this->is_withdrawal_page() ) return;
        $step = isset( $_GET['eu_step'] ) ? sanitize_text_field( $_GET['eu_step'] ) : 'form';
        if ( $step !== 'verify' ) return;
        $frontend = new EU2673_Frontend();
        $frontend->render_modal();
    }

    public function enqueue_assets(): void {
        if ( ! $this->is_withdrawal_page() ) return;
        wp_enqueue_style(
            'eu2673-frontend',
            plugin_dir_url( EU2673_PLUGIN_FILE ) . 'public/css/frontend.css',
            [],
            EU2673_VERSION
        );
        wp_enqueue_script(
            'eu2673-frontend',
            plugin_dir_url( EU2673_PLUGIN_FILE ) . 'public/js/frontend.js',
            [ 'jquery' ],
            EU2673_VERSION,
            true
        );
        wp_localize_script( 'eu2673-frontend', 'WB', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'eu2673_withdrawal_nonce' ),
        ] );
    }

    private function is_withdrawal_page(): bool {
        global $post;
        return $post && has_shortcode( $post->post_content, 'eu2673_withdrawal_form' );
    }

    public function render_shortcode( $atts ): string {
        ob_start();
        $step     = isset( $_GET['eu_step'] ) ? sanitize_text_field( $_GET['eu_step'] ) : 'form';
        $order_id = isset( $_GET['eu_order'] ) ? absint( $_GET['eu_order'] ) : 0;
        $email    = isset( $_GET['eu_email'] ) ? sanitize_email( $_GET['eu_email'] ) : '';
        $token    = isset( $_GET['eu_token'] ) ? sanitize_text_field( $_GET['eu_token'] ) : '';
        $error    = isset( $_GET['eu_error'] ) ? sanitize_text_field( $_GET['eu_error'] ) : '';

        if ( $step === 'verify' && $order_id && $email && $token ) {
            $this->render_verify( $order_id, $email, $token );
        } else {
            $this->render_form( $error );
        }
        return ob_get_clean();
    }

    private function render_form( string $error = '' ): void {
        $action = esc_url( get_permalink() );
        ?>
        <div class="eu2673-guest-wrap" style="max-width:480px;margin:0 auto;">
            <div style="background:#f8f9ff;border:1px solid #e0e0e0;border-radius:8px;padding:28px 32px;">
                <h2 style="margin:0 0 8px;font-size:20px;color:#1a1a2e;">
                    <?php esc_html_e( 'Exercise your right of withdrawal', 'eu2673-withdrawal-button' ); ?>
                </h2>
                <p style="margin:0 0 20px;font-size:14px;color:#555;">
                    <?php esc_html_e( 'Enter your order number and email address to check your eligibility.', 'eu2673-withdrawal-button' ); ?>
                </p>
                <?php if ( $error ) : ?>
                <div style="background:#ffebee;border-left:4px solid #c62828;padding:12px 16px;border-radius:4px;margin-bottom:16px;font-size:14px;color:#c62828;">
                    <?php
                    switch ( $error ) {
                        case 'not_found':
                            esc_html_e( 'We could not find an order with those details.', 'eu2673-withdrawal-button' );
                            break;
                        case 'expired':
                            esc_html_e( 'The withdrawal period for this order has expired (14 calendar days).', 'eu2673-withdrawal-button' );
                            break;
                        case 'done':
                            esc_html_e( 'You have already exercised the right of withdrawal for this order.', 'eu2673-withdrawal-button' );
                            break;
                        default:
                            esc_html_e( 'An error occurred. Please try again.', 'eu2673-withdrawal-button' );
                    }
                    ?>
                </div>
                <?php endif; ?>
                <form method="GET" action="<?php echo $action; ?>">
                    <input type="hidden" name="eu_step" value="verify">
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">
                            <?php esc_html_e( 'Order number', 'eu2673-withdrawal-button' ); ?> <span style="color:#e2401c;">*</span>
                        </label>
                        <input type="number" name="eu_order" required min="1"
                            style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#333;margin-bottom:6px;">
                            <?php esc_html_e( 'Email address used to purchase', 'eu2673-withdrawal-button' ); ?> <span style="color:#e2401c;">*</span>
                        </label>
                        <input type="email" name="eu_email" required
                            style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;">
                    </div>
                    <?php wp_nonce_field( 'eu2673_guest_lookup', 'eu_token' ); ?>
                    <button type="submit"
                        style="width:100%;padding:12px;background:#2c3e7a;color:#fff;border:none;border-radius:6px;font-size:15px;font-weight:600;cursor:pointer;">
                        <?php esc_html_e( 'Check my order', 'eu2673-withdrawal-button' ); ?>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    private function render_verify( int $order_id, string $email, string $token ): void {
        if ( ! wp_verify_nonce( $token, 'eu2673_guest_lookup' ) ) {
            $this->redirect_with_error( 'invalid' );
            return;
        }
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            $this->redirect_with_error( 'not_found' );
            return;
        }
        if ( strtolower( $order->get_billing_email() ) !== strtolower( $email ) ) {
            $this->redirect_with_error( 'not_found' );
            return;
        }
        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) {
            $this->redirect_with_error( 'done' );
            return;
        }
        $frontend = new EU2673_Frontend();
        if ( ! $frontend->order_is_eligible( $order ) ) {
            $this->redirect_with_error( 'expired' );
            return;
        }
        $days_left  = $frontend->get_days_remaining( $order );
        $nonce      = wp_create_nonce( 'eu2673_withdrawal_' . $order_id );
        $order_key  = $order->get_order_key();
        $btn_text   = get_option( 'eu2673_button_text', __( 'Exercise right of withdrawal', 'eu2673-withdrawal-button' ) );
        $ajax_url   = admin_url( 'admin-ajax.php' );
        $order_total = wc_price( $order->get_total() );
        $order_date  = wc_format_datetime( $order->get_date_created() );
        $page_url   = remove_query_arg( [ 'eu_step', 'eu_order', 'eu_email', 'eu_token' ] );
        ?>
        <div class="eu2673-guest-wrap" style="max-width:580px;margin:0 auto;">
            <div style="background:#f0f7f4;border-left:4px solid #1A6B4A;border-radius:0 8px 8px 0;padding:16px 20px;margin-bottom:20px;">
                <p style="margin:0;font-size:14px;color:#1A6B4A;font-weight:600;">
                    <?php printf( esc_html__( 'Order #%1$s verified. You have %2$d day(s) remaining.', 'eu2673-withdrawal-button' ), esc_html( $order_id ), (int) $days_left ); ?>
                </p>
            </div>
            <div style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:20px 24px;margin-bottom:16px;">
                <table style="width:100%;font-size:14px;border-collapse:collapse;">
                    <tr><td style="padding:6px 0;color:#555;width:140px;"><?php esc_html_e( 'Order', 'eu2673-withdrawal-button' ); ?></td><td style="padding:6px 0;font-weight:600;">#<?php echo esc_html( $order_id ); ?></td></tr>
                    <tr><td style="padding:6px 0;color:#555;"><?php esc_html_e( 'Date', 'eu2673-withdrawal-button' ); ?></td><td style="padding:6px 0;"><?php echo esc_html( $order_date ); ?></td></tr>
                    <tr><td style="padding:6px 0;color:#555;"><?php esc_html_e( 'Total', 'eu2673-withdrawal-button' ); ?></td><td style="padding:6px 0;"><?php echo wp_kses_post( $order_total ); ?></td></tr>
                </table>
            </div>
            <button type="button"
                class="wb-withdrawal-btn"
                data-order-id="<?php echo esc_attr( $order_id ); ?>"
                data-nonce="<?php echo esc_attr( $nonce ); ?>"
                data-order-key="<?php echo esc_attr( $order_key ); ?>"
                data-ajax-url="<?php echo esc_attr( $ajax_url ); ?>"
                style="display:block;width:100%;padding:14px;background:#2c3e7a;color:#fff;border:none;border-radius:6px;font-size:16px;font-weight:700;cursor:pointer;text-align:center;">
                <?php echo esc_html( $btn_text ); ?>
            </button>
            <p style="margin:12px 0 0;font-size:12px;color:#888;text-align:center;">
                <a href="<?php echo esc_url( $page_url ); ?>" style="color:#2c3e7a;">&larr; <?php esc_html_e( 'Check a different order', 'eu2673-withdrawal-button' ); ?></a>
            </p>
        </div>
        <?php
        $frontend->render_modal();
    }

    private function redirect_with_error( string $error ): void {
        $url = add_query_arg( 'eu_error', $error, remove_query_arg( [ 'eu_step', 'eu_token', 'eu_error' ] ) );
        wp_safe_redirect( $url );
        exit;
    }
}
