<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Frontend {

    const ENDPOINT = 'wb-withdrawals';

    /** @param bool $hooks false = solo para usar los cálculos de plazo, sin registrar acciones. */
    public function __construct( $hooks = true ) {
        if ( ! $hooks ) return;
        add_action( 'wp_enqueue_scripts',                          [ $this, 'enqueue_assets' ] );
        add_action( 'woocommerce_order_details_after_order_table', [ $this, 'render_button_order_detail' ], 5 );
        add_action( 'woocommerce_thankyou',                        [ $this, 'render_button_thankyou' ], 10 );
        add_action( 'wp_footer',                                   [ $this, 'render_modal' ] );
        add_filter( 'woocommerce_get_query_vars',                  [ $this, 'add_query_var' ] );
        add_filter( 'woocommerce_endpoint_' . self::ENDPOINT . '_title', [ $this, 'endpoint_title' ] );
        add_filter( 'woocommerce_account_menu_items',              [ $this, 'add_my_account_menu_item' ] );
        add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', [ $this, 'render_my_account_page' ] );
    }

    /* ── Recursos ───────────────────────────────────────────────────────── */

    public function enqueue_assets() {
        if ( ! $this->is_relevant_page() ) return;
        wp_enqueue_style( 'eu2673-frontend', EU2673_PLUGIN_URL . 'public/css/frontend.css', [], EU2673_VERSION );
        wp_enqueue_script( 'eu2673-modal', EU2673_PLUGIN_URL . 'public/js/modal.js', [], EU2673_VERSION, true );
        wp_localize_script( 'eu2673-modal', 'EU2673_Modal', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'i18n'    => [
                'confirm'         => __( 'Confirmar desistimiento', 'eu2673-withdrawal-button' ),
                'cancel'          => __( 'Cancelar', 'eu2673-withdrawal-button' ),
                'close'           => __( 'Cerrar', 'eu2673-withdrawal-button' ),
                'processing'      => __( 'Procesando...', 'eu2673-withdrawal-button' ),
                'exercised'       => __( 'Desistimiento ejercido', 'eu2673-withdrawal-button' ),
                'unknownError'    => __( 'Error desconocido.', 'eu2673-withdrawal-button' ),
                'connectionError' => __( 'Error de conexión.', 'eu2673-withdrawal-button' ),
                'annexTitle'      => __( 'Model withdrawal form (Annex I.B)', 'eu2673-withdrawal-button' ),
            ],
        ] );
    }

    private function is_relevant_page() {
        if ( is_admin() ) return false;
        global $post;
        $is_guest_page = $post instanceof WP_Post && has_shortcode( $post->post_content, EU2673_Core::SHORTCODE );
        return $is_guest_page
            || is_wc_endpoint_url( 'order-received' )
            || is_wc_endpoint_url( 'view-order' )
            || is_wc_endpoint_url( self::ENDPOINT );
    }

    /* ── Plazo y elegibilidad ───────────────────────────────────────────── */

    /** Días de plazo: nunca menos de los 14 legales, más los días de cortesía. */
    public function get_period_days() {
        return max( 14, (int) get_option( 'eu2673_withdrawal_days', 14 ) ) + max( 0, (int) get_option( 'eu2673_grace_days', 0 ) );
    }

    /**
     * Inicio del plazo. Con «fecha de entrega» se usa la fecha en que el pedido pasó a Completado;
     * si aún no se ha completado, el plazo no ha empezado y se devuelve null.
     */
    public function get_start_date( $order ) {
        if ( 'delivery_date' === get_option( 'eu2673_start_from', 'order_date' ) ) {
            return $order->get_date_completed() ?: null;
        }
        return $order->get_date_created();
    }

    /**
     * Último instante para desistir: fin del último día natural del plazo, en la zona horaria de la tienda.
     * null = el plazo todavía no ha empezado (pedido no entregado).
     */
    public function get_deadline( $order ) {
        $start = $this->get_start_date( $order );
        if ( ! $start ) return null;
        $date = ( new DateTimeImmutable( '@' . $start->getTimestamp() ) )->setTimezone( wp_timezone() );
        return $date->setTime( 23, 59, 59 )->modify( '+' . $this->get_period_days() . ' days' );
    }

    /** Días que quedan, o null si el plazo aún no ha empezado. */
    public function get_days_remaining( $order ) {
        $deadline = $this->get_deadline( $order );
        if ( ! $deadline ) return null;
        return max( 0, (int) ceil( ( $deadline->getTimestamp() - time() ) / DAY_IN_SECONDS ) );
    }

    /** Texto del plazo para el cliente: fecha límite, o aviso si aún no ha empezado. */
    public function deadline_text( $order ) {
        $deadline = $this->get_deadline( $order );
        if ( ! $deadline ) {
            return __( 'El plazo de desistimiento empieza cuando recibas el pedido.', 'eu2673-withdrawal-button' );
        }
        /* translators: %s: last day of the withdrawal period */
        return sprintf( __( 'Puedes desistir hasta el %s.', 'eu2673-withdrawal-button' ), wp_date( get_option( 'date_format' ), $deadline->getTimestamp() ) );
    }

    /** true si todos los productos del pedido están marcados como excluidos (art. 16). */
    private function all_items_excluded( $order ) {
        $items = $order->get_items();
        if ( ! $items ) return false;
        foreach ( $items as $item ) {
            if ( 'excluded' !== EU2673_Product::get_product_status( (int) $item->get_product_id() ) ) {
                return false;
            }
        }
        return true;
    }

    public function order_is_eligible( $order ) {
        if ( ! $order instanceof WC_Order ) return false;
        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) return false;
        if ( in_array( $order->get_status(), [ 'pending', 'checkout-draft' ], true ) ) return false;
        $excluded = (array) get_option( 'eu2673_exclude_statuses', [ 'wc-refunded', 'wc-cancelled', 'wc-failed' ] );
        if ( in_array( 'wc-' . $order->get_status(), $excluded, true ) ) return false;
        if ( $this->all_items_excluded( $order ) ) return false;
        $deadline = $this->get_deadline( $order );
        return ! $deadline || time() <= $deadline->getTimestamp();
    }

    /* ── Botón ──────────────────────────────────────────────────────────── */

    public function render_button_order_detail( $order ) {
        if ( ! $order instanceof WC_Order ) return;
        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) {
            echo $this->done_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en done_html()
            return;
        }
        if ( ! $this->order_is_eligible( $order ) ) return;
        echo $this->button_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en button_html()
        $this->render_annex_ib( $order );
    }

    public function render_button_thankyou( $order_id ) {
        if ( '1' !== (string) get_option( 'eu2673_show_on_guest_orders', '1' ) ) return;
        $order = wc_get_order( $order_id );
        if ( ! $order || ! $this->order_is_eligible( $order ) ) return;
        echo $this->button_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en button_html()
    }

    /** Clave del pedido solo cuando el visitante no es el titular con sesión iniciada. */
    private function key_for_visitor( $order ) {
        $user_id = get_current_user_id();
        return ( $user_id && $user_id === (int) $order->get_user_id() ) ? '' : $order->get_order_key();
    }

    /**
     * HTML del botón. Puede pintarse más de una vez en la misma página (detalle del pedido y
     * «pedido recibido», o temas y plugins que generan el contenido dos veces): modal.js deja solo uno.
     */
    public function button_html( $order, $force_key = false ) {
        $id = $order->get_id();

        $note = $this->deadline_text( $order );

        return sprintf(
            '<div class="wbdesist-section" data-order-id="%1$d"><button type="button" class="wbdesist-btn button" data-order-id="%1$d" data-order-number="%2$s" data-nonce="%3$s" data-order-key="%4$s" data-customer-name="%5$s" data-email="%6$s">%7$s</button><p class="wbdesist-note">%8$s</p></div>',
            $id,
            esc_attr( $order->get_order_number() ),
            esc_attr( wp_create_nonce( 'eu2673_withdrawal_' . $id ) ),
            esc_attr( $force_key ? $order->get_order_key() : $this->key_for_visitor( $order ) ),
            esc_attr( $order->get_formatted_billing_full_name() ),
            esc_attr( $order->get_billing_email() ),
            esc_html( EU2673_Core::text_option( 'eu2673_button_text' ) ),
            esc_html( $note )
        );
    }

    /** Aviso de desistimiento ya ejercido. */
    private function done_html( $order ) {
        $id = $order->get_id();
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $request = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, created_at FROM {$wpdb->prefix}withdrawal_requests WHERE order_id = %d ORDER BY id DESC LIMIT 1",
            $id
        ) );
        if ( ! $request ) return '';
        return sprintf(
            '<div class="wbdesist-section wbdesist-done" data-order-id="%6$d"><p class="wbdesist-done-title">%1$s</p><p>%2$s: <strong>#%3$d</strong> | %4$s: <strong>%5$s</strong></p></div>',
            esc_html__( 'Withdrawal successfully exercised', 'eu2673-withdrawal-button' ),
            esc_html__( 'Request ref.', 'eu2673-withdrawal-button' ),
            (int) $request->id,
            esc_html__( 'Date', 'eu2673-withdrawal-button' ),
            esc_html( EU2673_Core::format_db_datetime( $request->created_at ) ),
            $id
        );
    }

    /* ── Formulario modelo (anexo I.B de la Directiva 2011/83/UE) ───────── */

    public function render_annex_ib( $order ) {
        if ( ! $order instanceof WC_Order ) return;
        $countries    = WC()->countries;
        $country_code = $countries->get_base_country();
        $country_name = $countries->countries[ $country_code ] ?? $country_code;
        $address      = array_filter( [ $countries->get_base_address(), $countries->get_base_address_2(), trim( $countries->get_base_postcode() . ' ' . $countries->get_base_city() ), $country_name ] );
        $order_date   = $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '';
        $panel_id     = 'eu2673-annex-' . $order->get_id();
        ?>
        <div class="eu2673-annex-ib">
            <details>
                <summary><?php esc_html_e( 'Model withdrawal form (Annex I.B)', 'eu2673-withdrawal-button' ); ?> <span class="eu2673-annex-ref"><?php esc_html_e( 'EU Directive 2011/83/UE', 'eu2673-withdrawal-button' ); ?></span></summary>
                <div class="eu2673-annex-body" id="<?php echo esc_attr( $panel_id ); ?>">
                    <p><strong><?php esc_html_e( 'To:', 'eu2673-withdrawal-button' ); ?></strong><br>
                    <?php echo esc_html( get_bloginfo( 'name' ) ); ?><br>
                    <?php echo esc_html( implode( ', ', $address ) ); ?><br>
                    <?php echo esc_html( EU2673_Core::from_address() ); ?></p>
                    <p><?php esc_html_e( 'I/We (*) hereby give notice that I/We (*) withdraw from my/our (*) contract of sale of the following goods (*)/for the provision of the following service (*)', 'eu2673-withdrawal-button' ); ?></p>
                    <ul>
                        <?php foreach ( $order->get_items() as $item ) : ?>
                        <li><?php echo esc_html( $item->get_name() . ' × ' . $item->get_quantity() ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong><?php esc_html_e( 'Ordered on:', 'eu2673-withdrawal-button' ); ?></strong> <?php echo esc_html( $order_date ); ?><br>
                    <strong><?php esc_html_e( 'Order number:', 'eu2673-withdrawal-button' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?></p>
                    <p><strong><?php esc_html_e( 'Name of consumer(s):', 'eu2673-withdrawal-button' ); ?></strong> <?php echo esc_html( $order->get_formatted_billing_full_name() ); ?><br>
                    <strong><?php esc_html_e( 'Address of consumer(s):', 'eu2673-withdrawal-button' ); ?></strong><br>
                    <?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?></p>
                    <p><strong><?php esc_html_e( 'Signature of consumer(s) (only if this form is notified on paper):', 'eu2673-withdrawal-button' ); ?></strong> ____________________</p>
                    <p><strong><?php esc_html_e( 'Date:', 'eu2673-withdrawal-button' ); ?></strong> ____________________</p>
                    <p class="eu2673-annex-note"><?php esc_html_e( '(*) Delete as appropriate.', 'eu2673-withdrawal-button' ); ?></p>
                </div>
                <p class="eu2673-annex-print"><button type="button" class="button eu2673-print-annex" data-target="<?php echo esc_attr( $panel_id ); ?>"><?php esc_html_e( 'Print this form', 'eu2673-withdrawal-button' ); ?></button></p>
            </details>
        </div>
        <?php
    }

    /* ── Modal (una sola vez por página) ────────────────────────────────── */

    public function render_modal() {
        static $printed = false;
        if ( $printed || ! $this->is_relevant_page() ) return;
        $printed = true;

        // Algunos temas no ejecutan el gancho de detalle del pedido: se deja el botón en una plantilla
        // y el script lo coloca debajo de la tabla del pedido si no se ha pintado.
        if ( is_wc_endpoint_url( 'view-order' ) ) {
            $order = wc_get_order( absint( get_query_var( 'view-order' ) ) );
            if ( $order && current_user_can( 'view_order', $order->get_id() ) ) {
                $html = $order->get_meta( '_eu2673_withdrawal_done' )
                    ? $this->done_html( $order )
                    : ( $this->order_is_eligible( $order ) ? $this->button_html( $order ) : '' );
                if ( $html ) {
                    echo '<template id="eu2673-fallback">' . $html . '</template>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado al generarlo
                }
            }
        }

        include EU2673_PLUGIN_DIR . 'templates/modal.php';
    }

    /* ── Mi cuenta → Mis desistimientos ─────────────────────────────────── */

    public function add_query_var( $vars ) {
        $vars[ self::ENDPOINT ] = self::ENDPOINT;
        return $vars;
    }

    public function endpoint_title() {
        return __( 'My withdrawal requests', 'eu2673-withdrawal-button' );
    }

    public function add_my_account_menu_item( $items ) {
        $logout = $items['customer-logout'] ?? null;
        unset( $items['customer-logout'] );
        $items[ self::ENDPOINT ] = __( 'My withdrawals', 'eu2673-withdrawal-button' );
        if ( $logout ) {
            $items['customer-logout'] = $logout;
        }
        return $items;
    }

    public function render_my_account_page() {
        if ( ! is_user_logged_in() ) return;

        global $wpdb;
        $order_ids = array_map( 'intval', wc_get_orders( [ 'customer' => get_current_user_id(), 'limit' => -1, 'return' => 'ids' ] ) );
        $requests  = [];
        if ( $order_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $order_ids ), '%d' ) );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $requests = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}withdrawal_requests WHERE order_id IN ($placeholders) ORDER BY created_at DESC", $order_ids ) );
        }

        $labels = self::status_labels();

        echo '<div class="wb-myaccount-withdrawals">';
        if ( empty( $requests ) ) {
            echo '<p class="wb-myaccount-empty">' . esc_html__( 'You have no withdrawal requests registered.', 'eu2673-withdrawal-button' ) . '</p>';
        } else {
            echo '<table class="wb-myaccount-table woocommerce-orders-table shop_table shop_table_responsive"><thead><tr>';
            echo '<th scope="col">' . esc_html__( 'Ref.', 'eu2673-withdrawal-button' ) . '</th>';
            echo '<th scope="col">' . esc_html__( 'Order', 'eu2673-withdrawal-button' ) . '</th>';
            echo '<th scope="col">' . esc_html__( 'Date', 'eu2673-withdrawal-button' ) . '</th>';
            echo '<th scope="col">' . esc_html__( 'Status', 'eu2673-withdrawal-button' ) . '</th>';
            echo '</tr></thead><tbody>';
            foreach ( $requests as $req ) {
                $order = wc_get_order( $req->order_id );
                printf(
                    '<tr><td data-title="%1$s">#%2$d</td><td data-title="%3$s"><a href="%4$s">#%5$s</a></td><td data-title="%6$s">%7$s</td><td data-title="%8$s"><span class="wb-myaccount-status wb-myaccount-status--%9$s">%10$s</span></td></tr>',
                    esc_attr__( 'Ref.', 'eu2673-withdrawal-button' ),
                    (int) $req->id,
                    esc_attr__( 'Order', 'eu2673-withdrawal-button' ),
                    esc_url( $order ? $order->get_view_order_url() : wc_get_account_endpoint_url( 'orders' ) ),
                    esc_html( $order ? $order->get_order_number() : $req->order_id ),
                    esc_attr__( 'Date', 'eu2673-withdrawal-button' ),
                    esc_html( EU2673_Core::format_db_datetime( $req->created_at ) ),
                    esc_attr__( 'Status', 'eu2673-withdrawal-button' ),
                    esc_attr( $req->status ),
                    esc_html( $labels[ $req->status ] ?? $req->status )
                );
            }
            echo '</tbody></table>';
        }
        echo '</div>';
    }

    /** Nombres de los estados de una solicitud. */
    public static function status_labels() {
        return [
            'pending'   => __( 'Pending', 'eu2673-withdrawal-button' ),
            'approved'  => __( 'Approved', 'eu2673-withdrawal-button' ),
            'rejected'  => __( 'Rejected', 'eu2673-withdrawal-button' ),
            'completed' => __( 'Completed', 'eu2673-withdrawal-button' ),
        ];
    }
}
