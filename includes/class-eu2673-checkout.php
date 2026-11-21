<?php
defined( 'ABSPATH' ) || exit;

/**
 * EU2673_Checkout
 * Consentimiento para contenido digital en el checkout (art. 16, letra m, de la Directiva 2011/83/UE).
 * Funciona en el checkout clásico y en el bloque de checkout de WooCommerce.
 */
class EU2673_Checkout {

    const FIELD_ID = 'eu2673/digital-consent';

    public function __construct() {
        // Checkout clásico.
        add_action( 'woocommerce_review_order_before_submit', [ $this, 'render_digital_consent' ] );
        add_action( 'woocommerce_checkout_process',           [ $this, 'validate_digital_consent' ] );
        add_action( 'woocommerce_checkout_order_created',     [ $this, 'save_digital_consent' ] );

        // Bloque de checkout.
        add_action( 'woocommerce_init',                       [ $this, 'register_block_field' ] );
        add_action( 'woocommerce_blocks_loaded',              [ $this, 'register_store_api_data' ] );
        add_action( 'woocommerce_store_api_checkout_update_order_from_request', [ $this, 'validate_block_consent' ], 10, 2 );
    }

    public static function consent_text() {
        return __( 'Solicito expresamente el acceso inmediato al contenido digital y reconozco que, en cuanto comience a acceder a él, a visualizarlo o a descargarlo, perderé el derecho de desistimiento de 14 días previsto en el artículo 16, letra m), de la Directiva 2011/83/UE (art. 103.m de la TRLGDCU en España).', 'eu2673-withdrawal-button' );
    }

    private static function error_text() {
        return __( 'Debes aceptar las condiciones sobre contenido digital para completar tu pedido.', 'eu2673-withdrawal-button' );
    }

    /**
     * true si el carrito contiene contenido digital: productos marcados como tales
     * o, si el producto no tiene estado propio, productos descargables.
     * Los productos virtuales no descargables suelen ser servicios y no entran aquí.
     */
    public static function cart_needs_consent(): bool {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) return false;
        foreach ( WC()->cart->get_cart() as $item ) {
            $status  = EU2673_Product::get_product_status( (int) ( $item['product_id'] ?? 0 ) );
            $product = $item['data'] ?? null;
            if ( 'consent' === $status ) return true;
            if ( '' === $status && $product && $product->is_downloadable() ) return true;
        }
        return false;
    }

    private static function store_consent( $order ) {
        $order->update_meta_data( '_eu2673_digital_consent', '1' );
        $order->update_meta_data( '_eu2673_digital_consent_text', self::consent_text() );
        $order->update_meta_data( '_eu2673_digital_consent_date', current_time( 'mysql' ) );
        $order->update_meta_data( '_eu2673_digital_consent_ip', WC_Geolocation::get_ip_address() );
    }

    /* ── Checkout clásico ───────────────────────────────────────────────── */

    public function render_digital_consent(): void {
        if ( ! self::cart_needs_consent() ) return;
        echo '<p class="form-row eu2673-digital-consent validate-required">';
        echo '<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" for="eu2673_digital_consent">';
        echo '<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="eu2673_digital_consent" id="eu2673_digital_consent" value="1" required aria-required="true"> ';
        echo '<span>' . esc_html( self::consent_text() ) . '</span> <abbr class="required" aria-hidden="true">*</abbr>';
        echo '</label></p>';
    }

    public function validate_digital_consent(): void {
        if ( ! self::cart_needs_consent() ) return;
        if ( empty( $_POST['eu2673_digital_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce del checkout verificado por WC_Checkout
            wc_add_notice( self::error_text(), 'error' );
        }
    }

    public function save_digital_consent( $order ): void {
        if ( ! self::cart_needs_consent() ) return;
        if ( ! empty( $_POST['eu2673_digital_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce del checkout verificado por WC_Checkout
            self::store_consent( $order );
            $order->save();
        }
    }

    /* ── Bloque de checkout ─────────────────────────────────────────────── */

    /** Expone en la API de la tienda si el carrito necesita el consentimiento. */
    public function register_store_api_data(): void {
        if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) || ! class_exists( '\Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema' ) ) return;
        woocommerce_store_api_register_endpoint_data( [
            'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema::IDENTIFIER,
            'namespace'       => 'eu2673',
            'data_callback'   => function () {
                return [ 'needs_consent' => self::cart_needs_consent() ];
            },
            'schema_callback' => function () {
                return [
                    'needs_consent' => [
                        'description' => 'Whether the cart contains digital content that requires consent.',
                        'type'        => 'boolean',
                        'context'     => [ 'view', 'edit' ],
                        'readonly'    => true,
                    ],
                ];
            },
            'schema_type'     => ARRAY_A,
        ] );
    }

    public function register_block_field(): void {
        if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) return;
        $field = [
            'id'       => self::FIELD_ID,
            'label'    => self::consent_text(),
            'location' => 'order',
            'type'     => 'checkbox',
        ];
        // Con reglas condicionales (WooCommerce reciente) la casilla solo aparece, y solo es
        // obligatoria, cuando hay contenido digital. Sin ellas, se muestra opcional y se valida en el servidor.
        if ( method_exists( '\Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields', 'is_conditional_field' ) ) {
            $needs = function ( $value ) {
                return [ 'cart' => [ 'properties' => [ 'extensions' => [ 'properties' => [ 'eu2673' => [ 'properties' => [ 'needs_consent' => [ 'const' => $value ] ] ] ] ] ] ] ];
            };
            $field['required'] = $needs( true );
            $field['hidden']   = $needs( false );
        }
        woocommerce_register_additional_checkout_field( $field );
    }

    public function validate_block_consent( $order, $request ): void {
        if ( ! self::cart_needs_consent() ) return;
        $fields = (array) ( $request['additional_fields'] ?? [] );
        if ( empty( $fields[ self::FIELD_ID ] ) ) {
            throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'eu2673_digital_consent', esc_html( self::error_text() ), 400 );
        }
        self::store_consent( $order );
    }
}
