<?php
defined( 'ABSPATH' ) || exit;

/**
 * EU2673_Guest
 * Página pública de desistimiento para compradores sin cuenta (art. 11 bis.1: la función
 * de desistimiento debe estar disponible durante todo el plazo).
 * Shortcode: [eu2673_withdrawal_form]
 */
class EU2673_Guest {

    public function __construct() {
        add_shortcode( EU2673_Core::SHORTCODE, [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts ): string {
        ob_start();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- se verifica en lookup()
        if ( isset( $_POST['eu2673_guest_lookup'] ) ) {
            $result = $this->lookup();
            if ( $result instanceof WC_Order ) {
                $this->render_verified( $result );
            } else {
                $this->render_form( $result );
            }
        } else {
            $this->render_form( '' );
        }
        return ob_get_clean();
    }

    /** Devuelve el pedido verificado o un código de error. */
    private function lookup() {
        $nonce = sanitize_text_field( wp_unslash( $_POST['eu2673_guest_nonce'] ?? '' ) );
        if ( ! wp_verify_nonce( $nonce, 'eu2673_guest_lookup' ) ) {
            return 'invalid';
        }
        $order_ref = sanitize_text_field( wp_unslash( $_POST['eu_order'] ?? '' ) );
        $email     = sanitize_email( wp_unslash( $_POST['eu_email'] ?? '' ) );
        $order     = $this->find_order( $order_ref );
        if ( ! $order || ! $email || strtolower( $order->get_billing_email() ) !== strtolower( $email ) ) {
            return 'not_found';
        }
        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) {
            return 'done';
        }
        $frontend = new EU2673_Frontend( false );
        if ( ! $frontend->order_is_eligible( $order ) ) {
            return 'expired';
        }
        return $order;
    }

    /**
     * Busca el pedido por el número que ve el cliente. Usa el mismo filtro que el
     * seguimiento de pedidos de WooCommerce, que respetan los plugins de numeración propia.
     */
    private function find_order( $ref ) {
        $ref = ltrim( trim( (string) $ref ), '#' );
        if ( '' === $ref ) return null;
        $order_id = absint( apply_filters( 'woocommerce_shortcode_order_tracking_order_id', $ref ) );
        $order    = $order_id ? wc_get_order( $order_id ) : null;
        return $order instanceof WC_Order ? $order : null;
    }

    private function render_form( $error ): void {
        $messages = [
            'not_found' => __( 'We could not find an order with those details.', 'eu2673-withdrawal-button' ),
            'expired'   => __( 'The withdrawal period for this order has expired (14 calendar days).', 'eu2673-withdrawal-button' ),
            'done'      => __( 'You have already exercised the right of withdrawal for this order.', 'eu2673-withdrawal-button' ),
            'invalid'   => __( 'An error occurred. Please try again.', 'eu2673-withdrawal-button' ),
        ];
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- solo para volver a rellenar el formulario
        $order_value = sanitize_text_field( wp_unslash( $_POST['eu_order'] ?? '' ) );
        $email_value = sanitize_email( wp_unslash( $_POST['eu_email'] ?? '' ) );
        // phpcs:enable
        ?>
        <div class="eu2673-guest-wrap">
            <div class="eu2673-guest-box">
                <h2><?php esc_html_e( 'Exercise your right of withdrawal', 'eu2673-withdrawal-button' ); ?></h2>
                <p><?php esc_html_e( 'Enter your order number and email address to check your eligibility.', 'eu2673-withdrawal-button' ); ?></p>
                <?php if ( $error ) : ?>
                <div class="eu2673-guest-error" role="alert"><?php echo esc_html( $messages[ $error ] ?? $messages['invalid'] ); ?></div>
                <?php endif; ?>
                <form method="post" action="<?php echo esc_url( get_permalink() ); ?>">
                    <div class="eu2673-guest-field">
                        <label for="eu2673-guest-order"><?php esc_html_e( 'Order number', 'eu2673-withdrawal-button' ); ?> <span aria-hidden="true">*</span></label>
                        <input type="text" id="eu2673-guest-order" name="eu_order" required inputmode="numeric" autocomplete="off" value="<?php echo esc_attr( $order_value ); ?>">
                    </div>
                    <div class="eu2673-guest-field">
                        <label for="eu2673-guest-email"><?php esc_html_e( 'Email address used to purchase', 'eu2673-withdrawal-button' ); ?> <span aria-hidden="true">*</span></label>
                        <input type="email" id="eu2673-guest-email" name="eu_email" required autocomplete="email" value="<?php echo esc_attr( $email_value ); ?>">
                    </div>
                    <?php wp_nonce_field( 'eu2673_guest_lookup', 'eu2673_guest_nonce' ); ?>
                    <button type="submit" name="eu2673_guest_lookup" value="1" class="eu2673-guest-submit"><?php esc_html_e( 'Check my order', 'eu2673-withdrawal-button' ); ?></button>
                </form>
            </div>
        </div>
        <?php
    }

    private function render_verified( WC_Order $order ): void {
        $frontend = new EU2673_Frontend( false );
        ?>
        <div class="eu2673-guest-wrap">
            <div class="eu2673-guest-ok" role="status">
                <?php
                /* translators: %s: order number */
                printf( esc_html__( 'Pedido #%s verificado.', 'eu2673-withdrawal-button' ), esc_html( $order->get_order_number() ) );
                echo ' ' . esc_html( $frontend->deadline_text( $order ) );
                ?>
            </div>
            <table class="eu2673-guest-table">
                <tr><th scope="row"><?php esc_html_e( 'Order', 'eu2673-withdrawal-button' ); ?></th><td>#<?php echo esc_html( $order->get_order_number() ); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e( 'Date', 'eu2673-withdrawal-button' ); ?></th><td><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></td></tr>
                <tr><th scope="row"><?php esc_html_e( 'Total', 'eu2673-withdrawal-button' ); ?></th><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
            </table>
            <?php
            // El visitante ha demostrado conocer el pedido y su correo: el botón lleva la clave del pedido.
            echo $frontend->button_html( $order, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en button_html()
            $frontend->render_annex_ib( $order );
            ?>
            <p><a href="<?php echo esc_url( get_permalink() ); ?>">&larr; <?php esc_html_e( 'Check a different order', 'eu2673-withdrawal-button' ); ?></a></p>
        </div>
        <?php
    }
}
