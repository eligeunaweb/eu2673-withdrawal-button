<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Frontend {

    public function __construct() {
        add_action( 'wp_enqueue_scripts',                          [ $this, 'enqueue_assets' ] );
        add_action( 'woocommerce_order_details_after_order_table', [ $this, 'render_button_order_detail' ], 5 );
        add_action( 'woocommerce_thankyou',                        [ $this, 'render_button_thankyou' ], 10 );
        add_action( 'wp_footer',                                   [ $this, 'render_modal' ] );
        add_action( 'init',                                        [ $this, 'register_my_account_endpoint' ] );
        add_filter( 'woocommerce_account_menu_items',              [ $this, 'add_my_account_menu_item' ] );
        add_action( 'woocommerce_account_wb-withdrawals_endpoint', [ $this, 'render_my_account_page' ] );
    }

    public function enqueue_assets() {
        if ( ! $this->is_relevant_page() ) return;
        wp_enqueue_style(  'wb-frontend', EU2673_PLUGIN_URL . 'public/css/frontend.css', [], EU2673_VERSION );
        wp_enqueue_script( 'wb-frontend', EU2673_PLUGIN_URL . 'public/js/frontend.js',  [ 'jquery' ], EU2673_VERSION, true );
        wp_enqueue_script( 'wb-modal', EU2673_PLUGIN_URL . 'public/js/modal.js', [], EU2673_VERSION, true );
        wp_localize_script( 'wb-modal', 'EU2673_Modal', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'i18n'    => [
                'confirm'         => __( 'Confirmar desistimiento', 'eu2673-withdrawal-button' ),
                'cancel'          => __( 'Cancelar', 'eu2673-withdrawal-button' ),
                'close'           => __( 'Cerrar', 'eu2673-withdrawal-button' ),
                'processing'      => __( 'Procesando...', 'eu2673-withdrawal-button' ),
                'exercised'       => __( 'Desistimiento ejercido', 'eu2673-withdrawal-button' ),
                'unknownError'    => __( 'Error desconocido.', 'eu2673-withdrawal-button' ),
                'connectionError' => __( 'Error de conexión.', 'eu2673-withdrawal-button' ),
            ],
        ] );
    }

    public function render_button_order_detail( $order ) {
        if ( ! $order instanceof WC_Order ) return;

        if ( $order->get_meta( '_eu2673_withdrawal_done' ) ) {
            global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $eu2673_request = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, created_at FROM {$wpdb->prefix}withdrawal_requests WHERE order_id = %d ORDER BY id DESC LIMIT 1",
                $order->get_id()
            ) );
            if ( $eu2673_request ) {
                printf(
                    '<div class="wbdesist-section" style="margin-top:16px;padding:16px;background:#f0f7f4;border-left:4px solid #1A6B4A;border-radius:0 6px 6px 0;">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#1A6B4A;">%s</p>
                        <p style="margin:0;font-size:14px;color:#555;">%s: <strong>#%s</strong> &nbsp;|&nbsp; %s: <strong>%s</strong></p>
                    </div>',
                    esc_html__( 'Withdrawal successfully exercised', 'eu2673-withdrawal-button' ),
                    esc_html__( 'Request ref.', 'eu2673-withdrawal-button' ),
                    esc_html( $eu2673_request->id ),
                    esc_html__( 'Date', 'eu2673-withdrawal-button' ),
                    esc_html( date_i18n( 'd/m/Y H:i', strtotime( $eu2673_request->created_at ) ) )
                );
            }
            return;
        }

        if ( ! $this->order_is_eligible( $order ) ) return;
        $this->render_button( $order );
        $this->render_annex_ib( $order );
    }

    public function render_button_thankyou( $order_id ) {
        static $eu2673_rendered_thankyou = false;
        if ( $eu2673_rendered_thankyou ) return;
        $eu2673_rendered_thankyou = true;
        if ( ! get_option( 'eu2673_show_on_guest_orders', '1' ) ) return;
        $eu2673_order = wc_get_order( $order_id );
        if ( ! $eu2673_order || ! $this->order_is_eligible( $eu2673_order ) ) return;
        $this->render_button( $eu2673_order );
    }

    public function render_annex_ib( $order ): void {
        if ( ! $order instanceof WC_Order ) return;
        $shop_name    = get_bloginfo( 'name' );
        $shop_address = WC()->countries->get_base_address() . ', ' . WC()->countries->get_base_city() . ', ' . WC()->countries->get_base_postcode() . ', ' . WC()->countries->get_base_country();
        $shop_email   = get_option( 'admin_email' );
        $order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) : '';
        $customer     = $order->get_formatted_billing_full_name();
        $address      = $order->get_formatted_billing_address();
        ?>
        <div class="eu2673-annex-ib" style="margin-top:20px;border:1px solid #ddd;border-radius:6px;overflow:hidden;">
            <details>
                <summary style="padding:12px 16px;background:#f5f5f5;cursor:pointer;font-weight:600;list-style:none;display:flex;align-items:center;justify-content:space-between;">
                    <span><?php esc_html_e( 'Model withdrawal form (Annex I.B)', 'eu2673-withdrawal-button' ); ?></span>
                    <span style="font-size:11px;color:#666;font-weight:400;"><?php esc_html_e( 'EU Directive 2011/83/UE', 'eu2673-withdrawal-button' ); ?></span>
                </summary>
                <div style="padding:20px;font-size:14px;line-height:1.6;">
                    <p><strong><?php esc_html_e( 'To:', 'eu2673-withdrawal-button' ); ?></strong><br>
                    <?php echo esc_html( $shop_name ); ?><br>
                    <?php echo esc_html( $shop_address ); ?><br>
                    <?php echo esc_html( $shop_email ); ?></p>
                    <p><?php esc_html_e( 'I/We (*) hereby give notice that I/We (*) withdraw from my/our (*) contract of sale of the following goods (*)/for the provision of the following service (*)', 'eu2673-withdrawal-button' ); ?></p>
                    <p><strong><?php esc_html_e( 'Ordered on:', 'eu2673-withdrawal-button' ); ?></strong> <?php echo esc_html( $order_date ); ?><br>
                    <strong><?php esc_html_e( 'Order number:', 'eu2673-withdrawal-button' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?></p>
                    <p><strong><?php esc_html_e( 'Name of consumer(s):', 'eu2673-withdrawal-button' ); ?></strong> <?php echo esc_html( $customer ); ?><br>
                    <strong><?php esc_html_e( 'Address of consumer(s):', 'eu2673-withdrawal-button' ); ?></strong><br>
                    <?php echo wp_kses_post( $address ); ?></p>
                    <p style="font-size:12px;color:#888;"><?php esc_html_e( '(*) Delete as appropriate.', 'eu2673-withdrawal-button' ); ?></p>
                    <p style="margin-top:16px;">
                        <a href="<?php echo esc_url( add_query_arg( [ 'eu2673_annex_ib' => '1', 'order_id' => $order->get_id(), 'nonce' => wp_create_nonce( 'eu2673_annex_' . $order->get_id() ) ], home_url() ) ); ?>" target="_blank" style="font-size:12px;color:#2c3e7a;">
                            <?php esc_html_e( 'Print this form', 'eu2673-withdrawal-button' ); ?>
                        </a>
                    </p>
                </div>
            </details>
        </div>
        <?php
    }

    public function render_modal() {
        if ( ! $this->is_relevant_page() ) return;

        if ( is_wc_endpoint_url( 'view-order' ) ) {
            $eu2673_order_id = get_query_var( 'view-order' );
            if ( $eu2673_order_id ) {
                $eu2673_order = wc_get_order( absint( $eu2673_order_id ) );
                if ( $eu2673_order ) {
                    if ( $eu2673_order->get_meta( '_eu2673_withdrawal_done' ) ) {
                        global $wpdb;
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $eu2673_request = $wpdb->get_row( $wpdb->prepare(
                            "SELECT id, created_at FROM {$wpdb->prefix}withdrawal_requests WHERE order_id = %d ORDER BY id DESC LIMIT 1",
                            $eu2673_order->get_id()
                        ) );
                        if ( $eu2673_request && ! $this->already_rendered( $eu2673_order->get_id() ) ) {
                            $eu2673_js = sprintf(
                                "document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.wbdesist-section')) return;
    var div = document.createElement('div');
    div.className = 'wbdesist-section';
    div.style.cssText = 'margin-top:16px;padding:16px;background:#f0f7f4;border-left:4px solid #1A6B4A;border-radius:0 6px 6px 0;';
    div.innerHTML = '<p style=\"margin:0 0 8px;font-size:13px;font-weight:600;color:#1A6B4A;\">%s</p><p style=\"margin:0;font-size:14px;color:#555;\">Ref: <strong>%s</strong> | %s: <strong>%s</strong></p>';
    var table = document.querySelector('.woocommerce-order-details, .woocommerce table.shop_table');
    if (table) table.parentNode.insertBefore(div, table.nextSibling);
});",
                                esc_js( __( 'Withdrawal successfully exercised', 'eu2673-withdrawal-button' ) ),
                                esc_js( '#' . $eu2673_request->id ),
                                esc_js( __( 'Date', 'eu2673-withdrawal-button' ) ),
                                esc_js( date_i18n( 'd/m/Y H:i', strtotime( $eu2673_request->created_at ) ) )
                            );
                            wp_add_inline_script( 'wb-frontend', $eu2673_js );
                        }
                    } elseif ( $this->order_is_eligible( $eu2673_order ) && ! $this->already_rendered( $eu2673_order->get_id() ) ) {
                        $eu2673_nonce     = wp_create_nonce( 'eu2673_withdrawal_' . $eu2673_order->get_id() );
                        $eu2673_btn_text  = get_option( 'eu2673_button_text', __( 'Exercise right of withdrawal', 'eu2673-withdrawal-button' ) );
                        $eu2673_oid       = $eu2673_order->get_id();
                        $eu2673_days_left = $this->get_days_remaining( $eu2673_order );
                        /* translators: %d: number of days remaining */
                        $eu2673_days_text = sprintf( __( 'Withdrawal deadline: %d days remaining', 'eu2673-withdrawal-button' ), $eu2673_days_left );

                        $eu2673_js = sprintf(
                            "document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.wbdesist-section')) return;
    var div = document.createElement('div');
    div.className = 'wbdesist-section';
    div.style.cssText = 'margin-top:16px;padding:16px 0;';
    div.innerHTML = '<button type=\"button\" class=\"wbdesist-btn button\" data-order-id=\"%s\" data-nonce=\"%s\" style=\"background:#2c3e7a;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;\">%s</button><p style=\"margin:8px 0 0;font-size:12px;color:#888;\">%s</p>';
    var table = document.querySelector('.woocommerce-order-details, .woocommerce table.shop_table');
    if (table) table.parentNode.insertBefore(div, table.nextSibling);
});",
                            esc_js( $eu2673_oid ),
                            esc_js( $eu2673_nonce ),
                            esc_js( $eu2673_btn_text ),
                            esc_js( $eu2673_days_text )
                        );
                        wp_add_inline_script( 'wb-frontend', $eu2673_js );
                    }
                }
            }
        }

        include EU2673_PLUGIN_DIR . 'templates/modal.php';
    }

    private function already_rendered( $eu2673_order_id ) {
        static $eu2673_rendered = [];
        if ( isset( $eu2673_rendered[ $eu2673_order_id ] ) ) return true;
        $eu2673_rendered[ $eu2673_order_id ] = true;
        return false;
    }

    private function render_button( $eu2673_order ) {
        static $eu2673_rendered_ids = [];
        if ( isset( $eu2673_rendered_ids[ $eu2673_order->get_id() ] ) ) return;
        $eu2673_rendered_ids[ $eu2673_order->get_id() ] = true;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

        $eu2673_nonce     = wp_create_nonce( 'eu2673_withdrawal_' . $eu2673_order->get_id() );
        $eu2673_btn_text  = get_option( 'eu2673_button_text', __( 'Exercise right of withdrawal', 'eu2673-withdrawal-button' ) );
        $eu2673_days_left = $this->get_days_remaining( $eu2673_order );

        printf(
            '<div class="wbdesist-section" style="margin-top:16px;padding:16px 0;">
                <button type="button" class="wbdesist-btn button" data-order-id="%d" data-nonce="%s" style="background:#2c3e7a;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;">%s</button>
                <p style="margin:8px 0 0;font-size:12px;color:#888;">%s</p>
            </div>',
            absint( $eu2673_order->get_id() ),
            esc_attr( $eu2673_nonce ),
            esc_html( $eu2673_btn_text ),
            /* translators: %d: number of days remaining */
            esc_html( sprintf( __( 'Withdrawal deadline: %d days remaining', 'eu2673-withdrawal-button' ), $eu2673_days_left ) )
        );
    }

    private function get_start_date( $eu2673_order ) {
        $eu2673_start_from = get_option( 'eu2673_start_from', 'order_date' );
        if ( 'delivery_date' === $eu2673_start_from ) {
            $eu2673_notes = wc_get_order_notes( [ 'order_id' => $eu2673_order->get_id(), 'type' => 'system' ] );
            foreach ( array_reverse( $eu2673_notes ) as $eu2673_note ) {
                if ( strpos( $eu2673_note->content, 'completed' ) !== false || strpos( $eu2673_note->content, 'Completado' ) !== false ) {
                    return new WC_DateTime( $eu2673_note->date_created );
                }
            }
        }
        return $eu2673_order->get_date_created();
    }

    public function order_is_eligible( $eu2673_order ) {
        if ( ! $eu2673_order instanceof WC_Order ) return false;
        if ( $eu2673_order->get_meta( '_eu2673_withdrawal_done' ) ) return false;
        $eu2673_excluded = get_option( 'eu2673_exclude_statuses', [ 'wc-refunded', 'wc-cancelled', 'wc-failed' ] );
        if ( in_array( 'wc-' . $eu2673_order->get_status(), $eu2673_excluded, true ) ) return false;
        $eu2673_days  = (int) get_option( 'eu2673_withdrawal_days', 14 ) + (int) get_option( 'eu2673_grace_days', 0 );
        $eu2673_start = $this->get_start_date( $eu2673_order );
        if ( ! $eu2673_start ) return false;
        $eu2673_diff = ( time() - $eu2673_start->getTimestamp() ) / DAY_IN_SECONDS;
        return $eu2673_diff <= $eu2673_days;
    }

    public function get_days_remaining( $eu2673_order ) {
        $eu2673_days  = (int) get_option( 'eu2673_withdrawal_days', 14 ) + (int) get_option( 'eu2673_grace_days', 0 );
        $eu2673_start = $this->get_start_date( $eu2673_order );
        if ( ! $eu2673_start ) return 0;
        $eu2673_diff = ( time() - $eu2673_start->getTimestamp() ) / DAY_IN_SECONDS;
        return max( 0, (int) ceil( $eu2673_days - $eu2673_diff ) );
    }

    private function is_relevant_page() {
        global $post;
        $is_guest_page = $post && has_shortcode( $post->post_content, 'eu2673_withdrawal_form' );
        return $is_guest_page
            || is_wc_endpoint_url( 'order-received' )
            || is_wc_endpoint_url( 'view-order' )
            || is_wc_endpoint_url( 'orders' )
            || is_wc_endpoint_url( 'wb-withdrawals' )
            || is_checkout();
    }

    public function register_my_account_endpoint() {
        add_rewrite_endpoint( 'wb-withdrawals', EP_ROOT | EP_PAGES );
    }

    public function add_my_account_menu_item( $eu2673_items ) {
        $eu2673_logout = isset( $eu2673_items['customer-logout'] ) ? $eu2673_items['customer-logout'] : null;
        unset( $eu2673_items['customer-logout'] );
        $eu2673_items['wb-withdrawals'] = __( 'My withdrawals', 'eu2673-withdrawal-button' );
        if ( $eu2673_logout ) {
            $eu2673_items['customer-logout'] = $eu2673_logout;
        }
        return $eu2673_items;
    }

    public function render_my_account_page() {
        if ( ! is_user_logged_in() ) return;

        global $wpdb;
        $eu2673_user_id   = get_current_user_id();
        $eu2673_order_ids = wc_get_orders( [ 'customer' => $eu2673_user_id, 'limit' => -1, 'return' => 'ids' ] );

        $eu2673_requests = [];
        if ( ! empty( $eu2673_order_ids ) ) {
            $eu2673_placeholders = implode( ',', array_fill( 0, count( $eu2673_order_ids ), '%d' ) );
            $eu2673_query        = "SELECT * FROM {$wpdb->prefix}withdrawal_requests WHERE order_id IN (%s) ORDER BY created_at DESC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $eu2673_requests = $wpdb->get_results(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->prepare( $eu2673_query, implode( ',', array_map( 'intval', $eu2673_order_ids ) ) )
            );
        }

        $eu2673_status_labels = [
            'pending'   => __( 'Pending',   'eu2673-withdrawal-button' ),
            'approved'  => __( 'Approved',  'eu2673-withdrawal-button' ),
            'rejected'  => __( 'Rejected',  'eu2673-withdrawal-button' ),
            'completed' => __( 'Completed', 'eu2673-withdrawal-button' ),
        ];

        echo '<div class="wb-myaccount-withdrawals">';
        echo '<h2>' . esc_html__( 'My withdrawal requests', 'eu2673-withdrawal-button' ) . '</h2>';

        if ( empty( $eu2673_requests ) ) {
            echo '<p class="wb-myaccount-empty">' . esc_html__( 'You have no withdrawal requests registered.', 'eu2673-withdrawal-button' ) . '</p>';
        } else {
            echo '<table class="wb-myaccount-table woocommerce-orders-table shop_table shop_table_responsive">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__( 'Ref.',   'eu2673-withdrawal-button' ) . '</th>';
            echo '<th>' . esc_html__( 'Order',  'eu2673-withdrawal-button' ) . '</th>';
            echo '<th>' . esc_html__( 'Date',   'eu2673-withdrawal-button' ) . '</th>';
            echo '<th>' . esc_html__( 'Status', 'eu2673-withdrawal-button' ) . '</th>';
            echo '</tr></thead><tbody>';

            foreach ( $eu2673_requests as $eu2673_req ) {
                $eu2673_status_label = isset( $eu2673_status_labels[ $eu2673_req->status ] ) ? $eu2673_status_labels[ $eu2673_req->status ] : $eu2673_req->status;
                $eu2673_order_url    = wc_get_account_endpoint_url( 'view-order' ) . $eu2673_req->order_id;
                printf(
                    '<tr>
                        <td data-title="%s"><strong>#%d</strong></td>
                        <td data-title="%s"><a href="%s">#%d</a></td>
                        <td data-title="%s">%s</td>
                        <td data-title="%s"><span class="wb-myaccount-status wb-myaccount-status--%s">%s</span></td>
                    </tr>',
                    esc_attr__( 'Ref.',   'eu2673-withdrawal-button' ), absint( $eu2673_req->id ),
                    esc_attr__( 'Order',  'eu2673-withdrawal-button' ), esc_url( $eu2673_order_url ), absint( $eu2673_req->order_id ),
                    esc_attr__( 'Date',   'eu2673-withdrawal-button' ), esc_html( date_i18n( 'd/m/Y H:i', strtotime( $eu2673_req->created_at ) ) ),
                    esc_attr__( 'Status', 'eu2673-withdrawal-button' ), esc_attr( $eu2673_req->status ), esc_html( $eu2673_status_label )
                );
            }
            echo '</tbody></table>';
        }
        echo '</div>';
    }
}
