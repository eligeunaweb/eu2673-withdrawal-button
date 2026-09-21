<?php
defined( 'ABSPATH' ) || exit;

/**
 * EU2673_Product
 * Añade el selector de estado de desistimiento en la ficha de cada producto.
 * Conforme al art. 16 de la Directiva 2011/83/UE (modificada por 2023/2673).
 */
class EU2673_Product {

    public function __construct() {
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'render_withdrawal_status_field' ] );
        add_action( 'woocommerce_process_product_meta',                 [ $this, 'save_withdrawal_status_field' ] );
    }

    /**
     * Opciones del selector.
     */
    public static function get_options(): array {
        return [
            ''          => __( '— Standard (subject to withdrawal right) —', 'eu2673-withdrawal-button' ),
            'excluded'  => __( 'Excluded — Art. 16 exception (no withdrawal right)', 'eu2673-withdrawal-button' ),
            'consent'   => __( 'Digital content — requires consent at checkout (Art. 16m)', 'eu2673-withdrawal-button' ),
        ];
    }

    /**
     * Renderiza el campo en la pestaña General del producto.
     */
    public function render_withdrawal_status_field(): void {
        global $post;
        $value = get_post_meta( $post->ID, '_eu2673_withdrawal_status', true );
        echo '<div class="options_group">';
        woocommerce_wp_select( [
            'id'          => '_eu2673_withdrawal_status',
            'label'       => __( 'Withdrawal status', 'eu2673-withdrawal-button' ),
            'value'       => $value,
            'options'     => self::get_options(),
            'desc_tip'    => true,
            'description' => __( 'Controls how the withdrawal right applies to this product. "Standard" follows the general plugin settings. "Excluded" hides the withdrawal button for orders containing this product. "Digital content" shows a mandatory consent checkbox at checkout.', 'eu2673-withdrawal-button' ),
        ] );
        echo '</div>';
    }

    /**
     * Guarda el campo al actualizar el producto.
     */
    public function save_withdrawal_status_field( int $product_id ): void {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-post_' . $product_id ) ) {
            return;
        }
        $value = sanitize_text_field( wp_unslash( $_POST['_eu2673_withdrawal_status'] ?? '' ) );
        update_post_meta( $product_id, '_eu2673_withdrawal_status', $value );
    }

    /**
     * Obtiene el estado de desistimiento de un producto.
     */
    public static function get_product_status( int $product_id ): string {
        return get_post_meta( $product_id, '_eu2673_withdrawal_status', true ) ?: '';
    }

    /**
     * Comprueba si un carrito/pedido tiene productos excluidos o que requieren consentimiento.
     * Devuelve: '' | 'excluded' | 'consent'
     */
    public static function get_cart_withdrawal_status(): string {
        if ( ! WC()->cart ) return '';
        $status = '';
        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id     = (int) ( $item['product_id'] ?? 0 );
            $product_status = self::get_product_status( $product_id );
            if ( $product_status === 'excluded' ) return 'excluded';
            if ( $product_status === 'consent' )  $status = 'consent';
        }
        return $status;
    }
}
