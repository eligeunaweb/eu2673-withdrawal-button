<?php
defined( 'ABSPATH' ) || exit;

/**
 * EU2673_Checkout
 * Gestiona el consentimiento obligatorio en el checkout para productos digitales
 * conforme al art. 16, letra m) de la Directiva 2011/83/UE (modificada por 2023/2673).
 */
class EU2673_Checkout {

    public function __construct() {
        add_action( 'woocommerce_review_order_before_submit', [ $this, 'render_digital_consent' ] );
        add_action( 'woocommerce_checkout_process',           [ $this, 'validate_digital_consent' ] );
        add_action( 'woocommerce_checkout_order_created',     [ $this, 'save_digital_consent' ] );
    }

    /**
     * Comprueba si el carrito necesita consentimiento digital.
     * Prioriza el estado por producto; si no hay estado por producto,
     * detecta productos virtuales/descargables automáticamente.
     */
    private function cart_needs_consent(): bool {
        if ( ! WC()->cart ) return false;

        // Primero: comprobar estado por producto (EU2673_Product)
        if ( class_exists( 'EU2673_Product' ) ) {
            $status = EU2673_Product::get_cart_withdrawal_status();
            if ( $status === 'consent' ) return true;
            if ( $status === 'excluded' ) return false;
        }

        // Fallback: detectar productos virtuales/descargables
        foreach ( WC()->cart->get_cart() as $item ) {
            $product = $item['data'] ?? null;
            if ( $product && ( $product->is_downloadable() || $product->is_virtual() ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Renderiza el checkbox de consentimiento en el checkout.
     */
    public function render_digital_consent(): void {
        if ( ! $this->cart_needs_consent() ) return;

        $text = __( 'Solicito expresamente el acceso inmediato al contenido digital y reconozco que, en cuanto comience a acceder a él, a visualizarlo o a descargarlo, perderé el derecho de desistimiento de 14 días previsto en el artículo 16, letra m), de la Directiva 2011/83/UE (art. 103.m de la TRLGDCU en España).', 'eu2673-withdrawal-button' );

        echo '<div class="eu2673-digital-consent" style="margin:12px 0;padding:10px;border:1px solid #e0e0e0;border-radius:4px;background:#f9f9f9;">';
        echo '<label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;">';
        echo '<input type="checkbox" name="eu2673_digital_consent" id="eu2673_digital_consent" style="margin-top:3px;flex-shrink:0;" required>';
        echo '<span>' . esc_html( $text ) . ' <span style="color:#e2401c;">*</span></span>';
        echo '</label>';
        echo '</div>';
    }

    /**
     * Valida que el checkbox esté marcado antes de procesar el pedido.
     */
    public function validate_digital_consent(): void {
        if ( ! $this->cart_needs_consent() ) return;
        if ( empty( $_POST['eu2673_digital_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce checkout nonce verified by WC_Checkout
            wc_add_notice(
                __( 'Debes aceptar las condiciones sobre contenido digital para completar tu pedido.', 'eu2673-withdrawal-button' ),
                'error'
            );
        }
    }

    /**
     * Guarda el consentimiento en los meta del pedido.
     */
    public function save_digital_consent( $order ): void {
        if ( ! $this->cart_needs_consent() ) return;
        if ( ! empty( $_POST['eu2673_digital_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce checkout nonce verified by WC_Checkout
            $order->update_meta_data( '_eu2673_digital_consent', '1' );
            $order->update_meta_data( '_eu2673_digital_consent_text', __( 'Solicito expresamente el acceso inmediato al contenido digital y reconozco que, en cuanto comience a acceder a él, a visualizarlo o a descargarlo, perderé el derecho de desistimiento de 14 días previsto en el artículo 16, letra m), de la Directiva 2011/83/UE (art. 103.m de la TRLGDCU en España).', 'eu2673-withdrawal-button' ) );
            $order->update_meta_data( '_eu2673_digital_consent_date', current_time( 'mysql' ) );
            $order->update_meta_data( '_eu2673_digital_consent_ip', WC_Geolocation::get_ip_address() );
            $order->save();
        }
    }
}
