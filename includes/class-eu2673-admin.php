<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Admin {

    const OPTION_INSTALL_DATE  = 'eu2673_install_date';
    const OPTION_NOTICE_CLOSED = 'eu2673_notice_closed';
    const PRO_URL              = 'https://adaptatuweb.com/withdrawal-button-es/';
    const REVIEW_URL           = 'https://wordpress.org/support/plugin/eu2673-withdrawal-button/reviews/#new-post';

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_notices',         [ $this, 'show_admin_notice' ] );
        add_action( 'wp_ajax_eu2673_dismiss_notice', [ $this, 'dismiss_notice' ] );
        add_filter( 'plugin_row_meta',       [ $this, 'plugin_row_meta' ], 10, 2 );

        // Guardar fecha de instalación la primera vez
        if ( ! get_option( self::OPTION_INSTALL_DATE ) ) {
            update_option( self::OPTION_INSTALL_DATE, time() );
        }
    }

    // ── MENÚ ─────────────────────────────────────────────────────────────────

    public function register_menu(): void {
        add_menu_page(
            __( 'Withdrawal Button', 'eu2673-withdrawal-button' ),
            __( 'Withdrawal', 'eu2673-withdrawal-button' ),
            'manage_woocommerce',
            'eu2673-withdrawal',
            [ $this, 'page_requests' ],
            'dashicons-undo',
            58
        );
        add_submenu_page(
            'eu2673-withdrawal',
            __( 'Withdrawal requests', 'eu2673-withdrawal-button' ),
            __( 'Requests', 'eu2673-withdrawal-button' ),
            'manage_woocommerce',
            'eu2673-withdrawal',
            [ $this, 'page_requests' ]
        );
        add_submenu_page(
            'eu2673-withdrawal',
            __( 'Settings', 'eu2673-withdrawal-button' ),
            __( 'Settings', 'eu2673-withdrawal-button' ),
            'manage_options',
            'eu2673-settings',
            [ $this, 'page_settings' ]
        );
        add_submenu_page(
            'eu2673-withdrawal',
            __( 'Help', 'eu2673-withdrawal-button' ),
            __( 'Help', 'eu2673-withdrawal-button' ),
            'manage_options',
            'eu2673-help',
            [ $this, 'page_help' ]
        );
    }

    public function enqueue_assets( string $hook ): void {
        if ( strpos( $hook, 'eu2673' ) === false ) return;
        wp_enqueue_style( 'eu2673-admin', plugin_dir_url( EU2673_PLUGIN_FILE ) . 'admin/css/admin.css', [], EU2673_VERSION );
        wp_enqueue_script( 'eu2673-admin', plugin_dir_url( EU2673_PLUGIN_FILE ) . 'admin/js/admin.js', [ 'jquery' ], EU2673_VERSION, true );
        wp_localize_script( 'eu2673-admin', 'eu2673Admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'eu2673_admin_nonce' ),
        ] );
    }

    // ── ADMIN NOTICE ─────────────────────────────────────────────────────────

    public function show_admin_notice(): void {
        // No mostrar si ya fue cerrado
        if ( get_option( self::OPTION_NOTICE_CLOSED ) ) return;

        // No mostrar en las primeras 24 horas
        $install_date = (int) get_option( self::OPTION_INSTALL_DATE, time() );
        $days_active  = (int) floor( ( time() - $install_date ) / DAY_IN_SECONDS );
        if ( $days_active < 1 ) return;

        // Solo en páginas relevantes
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->base, [ 'dashboard', 'plugins', 'toplevel_page_eu2673-withdrawal' ] ) ) return;

        $is_long_user = $days_active >= 30;
        ?>
        <div class="notice eu2673-admin-notice" id="eu2673-notice" style="border-left:4px solid #2c3e7a;padding:16px 20px;display:flex;align-items:flex-start;gap:16px;background:#fff;">
            <div style="font-size:28px;line-height:1;">&#8617;</div>
            <div style="flex:1;">
                <?php if ( $is_long_user ) : ?>
                    <p style="margin:0 0 6px;font-weight:700;font-size:14px;color:#1a1a2e;">
                        <?php esc_html_e( 'Unlock the full power of Withdrawal Button', 'eu2673-withdrawal-button' ); ?>
                    </p>
                    <p style="margin:0 0 10px;font-size:13px;color:#555;">
                        <?php esc_html_e( 'You have been using the free version for over a month. The Pro version adds:', 'eu2673-withdrawal-button' ); ?>
                        <strong><?php esc_html_e( 'official PDF with SHA-256 hash, Annex B form, legal email traceability and anti-fraud system.', 'eu2673-withdrawal-button' ); ?></strong>
                        <?php esc_html_e( 'One-time payment of €39.', 'eu2673-withdrawal-button' ); ?>
                    </p>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank" class="button button-primary" style="background:#2c3e7a;border-color:#2c3e7a;">
                            🚀 <?php esc_html_e( 'Upgrade to Pro — €39', 'eu2673-withdrawal-button' ); ?>
                        </a>
                        <a href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" class="button button-secondary">
                            ⭐ <?php esc_html_e( 'Leave a review', 'eu2673-withdrawal-button' ); ?>
                        </a>
                        <button type="button" id="eu2673-dismiss" class="button button-link" style="color:#999;">
                            <?php esc_html_e( 'Dismiss', 'eu2673-withdrawal-button' ); ?>
                        </button>
                    </div>
                <?php else : ?>
                    <p style="margin:0 0 6px;font-weight:700;font-size:14px;color:#1a1a2e;">
                        <?php esc_html_e( 'Thank you for using EU2673 Withdrawal Button!', 'eu2673-withdrawal-button' ); ?>
                    </p>
                    <p style="margin:0 0 10px;font-size:13px;color:#555;">
                        <?php
                        printf(
                            esc_html__( 'You have been using the plugin for %d day(s). If it is helping your store comply with EU Directive 2023/2673, we would love a quick review — it helps other merchants find us!', 'eu2673-withdrawal-button' ),
                            $days_active
                        );
                        ?>
                    </p>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" class="button button-primary" style="background:#2c3e7a;border-color:#2c3e7a;">
                            ⭐ <?php esc_html_e( 'Leave a review on WordPress.org', 'eu2673-withdrawal-button' ); ?>
                        </a>
                        <a href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank" class="button button-secondary">
                            🚀 <?php esc_html_e( 'Discover Pro features', 'eu2673-withdrawal-button' ); ?>
                        </a>
                        <button type="button" id="eu2673-dismiss" class="button button-link" style="color:#999;">
                            <?php esc_html_e( 'Dismiss', 'eu2673-withdrawal-button' ); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <script>
        jQuery(function($){
            $('#eu2673-dismiss').on('click', function(){
                $('#eu2673-notice').fadeOut();
                $.post(eu2673Admin.ajax_url, {
                    action: 'eu2673_dismiss_notice',
                    nonce:  eu2673Admin.nonce
                });
            });
        });
        </script>
        <?php
    }

    public function dismiss_notice(): void {
        check_ajax_referer( 'eu2673_admin_nonce', 'nonce' );
        update_option( self::OPTION_NOTICE_CLOSED, 1 );
        wp_send_json_success();
    }

    // ── PLUGIN ROW META ───────────────────────────────────────────────────────

    public function plugin_row_meta( array $links, string $file ): array {
        if ( plugin_basename( EU2673_PLUGIN_FILE ) !== $file ) return $links;
        $links[] = '<a href="' . esc_url( self::REVIEW_URL ) . '" target="_blank">⭐ ' . esc_html__( 'Rate', 'eu2673-withdrawal-button' ) . '</a>';
        $links[] = '<a href="' . esc_url( self::PRO_URL ) . '" target="_blank" style="color:#2c3e7a;font-weight:600;">🚀 ' . esc_html__( 'Upgrade to Pro', 'eu2673-withdrawal-button' ) . '</a>';
        $links[] = '<a href="mailto:proyectos@adaptatuweb.com">📧 ' . esc_html__( 'Support', 'eu2673-withdrawal-button' ) . '</a>';
        return $links;
    }

    // ── PÁGINAS ADMIN ─────────────────────────────────────────────────────────

    public function page_requests(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'eu2673_withdrawal_requests';
        $status_filter = sanitize_text_field( $_GET['status'] ?? '' );
        $where = $status_filter ? $wpdb->prepare( 'WHERE status = %s', $status_filter ) : '';
        $requests = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT 100", ARRAY_A );
        $counts = $wpdb->get_results( "SELECT status, COUNT(*) as total FROM {$table} GROUP BY status", ARRAY_A );
        $by_status = [];
        foreach ( $counts as $c ) $by_status[ $c['status'] ] = $c['total'];

        $statuses = [
            ''          => __( 'All', 'eu2673-withdrawal-button' ),
            'pending'   => __( 'Pending', 'eu2673-withdrawal-button' ),
            'approved'  => __( 'Approved', 'eu2673-withdrawal-button' ),
            'rejected'  => __( 'Rejected', 'eu2673-withdrawal-button' ),
            'completed' => __( 'Completed', 'eu2673-withdrawal-button' ),
        ];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Withdrawal requests', 'eu2673-withdrawal-button' ); ?></h1>

            <?php /* Pro upsell banner */ ?>
            <div style="background:linear-gradient(135deg,#1a1a2e,#2c3e7a);border-radius:8px;padding:16px 20px;margin:16px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="color:#fff;">
                    <strong style="font-size:14px;">🚀 <?php esc_html_e( 'Upgrade to Pro', 'eu2673-withdrawal-button' ); ?></strong>
                    <span style="font-size:13px;opacity:.85;margin-left:12px;">
                        <?php esc_html_e( 'PDF acknowledgement with SHA-256 · Official Annex B · Email traceability · Anti-fraud system', 'eu2673-withdrawal-button' ); ?>
                    </span>
                </div>
                <a href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank"
                   style="background:#FFD24D;color:#1a1a2e;padding:8px 18px;border-radius:6px;text-decoration:none;font-weight:700;font-size:13px;white-space:nowrap;">
                    <?php esc_html_e( 'Get Pro — €39 one-time', 'eu2673-withdrawal-button' ); ?>
                </a>
            </div>

            <?php /* Filtros */ ?>
            <ul class="subsubsub" style="margin-bottom:12px;">
                <?php foreach ( $statuses as $key => $label ) : ?>
                <li>
                    <a href="<?php echo esc_url( add_query_arg( 'status', $key ) ); ?>"
                       <?php echo ( $status_filter === $key ) ? 'class="current" style="font-weight:700;"' : ''; ?>>
                        <?php echo esc_html( $label ); ?>
                        <?php if ( $key && isset( $by_status[ $key ] ) ) echo '<span class="count">(' . (int) $by_status[ $key ] . ')</span>'; ?>
                    </a> |
                </li>
                <?php endforeach; ?>
            </ul>

            <?php /* Tabla */ ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Order', 'eu2673-withdrawal-button' ); ?></th>
                        <th><?php esc_html_e( 'Customer', 'eu2673-withdrawal-button' ); ?></th>
                        <th><?php esc_html_e( 'Reason', 'eu2673-withdrawal-button' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'eu2673-withdrawal-button' ); ?></th>
                        <th><?php esc_html_e( 'Date', 'eu2673-withdrawal-button' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'eu2673-withdrawal-button' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $requests ) ) : ?>
                    <tr><td colspan="6" style="text-align:center;color:#999;padding:24px;"><?php esc_html_e( 'No requests found.', 'eu2673-withdrawal-button' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $requests as $req ) : ?>
                    <tr>
                        <td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $req['id_order'] . '&action=edit' ) ); ?>">#<?php echo (int) $req['id_order']; ?></a></td>
                        <td><?php echo esc_html( $req['customer_email'] ?? '—' ); ?></td>
                        <td><?php echo esc_html( mb_substr( $req['reason'] ?? '—', 0, 60 ) ); ?></td>
                        <td><span class="eu2673-status eu2673-status--<?php echo esc_attr( $req['status'] ); ?>"><?php echo esc_html( $req['status'] ); ?></span></td>
                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', strtotime( $req['created_at'] ) ) ); ?></td>
                        <td>
                            <select class="eu2673-status-select" data-id="<?php echo (int) $req['id']; ?>" style="font-size:12px;">
                                <?php foreach ( [ 'pending', 'approved', 'rejected', 'completed' ] as $s ) : ?>
                                <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $req['status'], $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <p style="margin-top:16px;font-size:12px;color:#999;">
                <?php esc_html_e( 'Showing last 100 requests. Export and advanced filters available in Pro.', 'eu2673-withdrawal-button' ); ?>
                <a href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank"><?php esc_html_e( 'Upgrade to Pro →', 'eu2673-withdrawal-button' ); ?></a>
            </p>
        </div>
        <?php
    }

    public function page_settings(): void {
        if ( isset( $_POST['eu2673_save'] ) && check_admin_referer( 'eu2673_settings' ) ) {
            update_option( 'eu2673_days',                (int) ( $_POST['eu2673_days'] ?? 14 ) );
            update_option( 'eu2673_button_text',         sanitize_text_field( $_POST['eu2673_button_text'] ?? '' ) );
            update_option( 'eu2673_send_customer_email', (int) ( $_POST['eu2673_send_customer_email'] ?? 1 ) );
            update_option( 'eu2673_send_admin_email',    (int) ( $_POST['eu2673_send_admin_email'] ?? 1 ) );
            update_option( 'eu2673_admin_email',         sanitize_email( $_POST['eu2673_admin_email'] ?? '' ) );
            update_option( 'eu2673_change_status',       (int) ( $_POST['eu2673_change_status'] ?? 0 ) );
            update_option( 'eu2673_new_status',          sanitize_text_field( $_POST['eu2673_new_status'] ?? '' ) );
            update_option( 'eu2673_require_reason',      (int) ( $_POST['eu2673_require_reason'] ?? 0 ) );
            update_option( 'eu2673_show_on_confirm',     (int) ( $_POST['eu2673_show_on_confirm'] ?? 1 ) );
            update_option( 'eu2673_start_from',          sanitize_text_field( $_POST['eu2673_start_from'] ?? 'order_date' ) );
            update_option( 'eu2673_grace_days',          (int) ( $_POST['eu2673_grace_days'] ?? 0 ) );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'eu2673-withdrawal-button' ) . '</p></div>';
        }

        $order_statuses = wc_get_order_statuses();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Withdrawal Button — Settings', 'eu2673-withdrawal-button' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'eu2673_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Withdrawal period (days)', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="number" name="eu2673_days" value="<?php echo (int) get_option( 'eu2673_days', 14 ); ?>" min="14" class="small-text">
                        <p class="description"><?php esc_html_e( 'Legal minimum is 14 calendar days.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Grace days (optional)', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="number" name="eu2673_grace_days" value="<?php echo (int) get_option( 'eu2673_grace_days', 0 ); ?>" min="0" class="small-text">
                        <p class="description"><?php esc_html_e( 'Extra days on top of the legal deadline.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Start deadline from', 'eu2673-withdrawal-button' ); ?></th>
                        <td><select name="eu2673_start_from">
                            <option value="order_date" <?php selected( get_option( 'eu2673_start_from', 'order_date' ), 'order_date' ); ?>><?php esc_html_e( 'Order date', 'eu2673-withdrawal-button' ); ?></option>
                            <option value="delivery_date" <?php selected( get_option( 'eu2673_start_from' ), 'delivery_date' ); ?>><?php esc_html_e( 'Delivery date (when order is completed)', 'eu2673-withdrawal-button' ); ?></option>
                        </select></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Button text', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="text" name="eu2673_button_text" value="<?php echo esc_attr( get_option( 'eu2673_button_text', '' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Leave empty to use the default text.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Send email to customer', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="checkbox" name="eu2673_send_customer_email" value="1" <?php checked( get_option( 'eu2673_send_customer_email', 1 ) ); ?>></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Send notification to admin', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="checkbox" name="eu2673_send_admin_email" value="1" <?php checked( get_option( 'eu2673_send_admin_email', 1 ) ); ?>></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Admin email', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="email" name="eu2673_admin_email" value="<?php echo esc_attr( get_option( 'eu2673_admin_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Require reason', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="checkbox" name="eu2673_require_reason" value="1" <?php checked( get_option( 'eu2673_require_reason', 0 ) ); ?>>
                        <p class="description"><?php esc_html_e( 'Note: the law does not require consumers to provide a reason.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Show on order confirmation page', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="checkbox" name="eu2673_show_on_confirm" value="1" <?php checked( get_option( 'eu2673_show_on_confirm', 1 ) ); ?>></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Change order status automatically', 'eu2673-withdrawal-button' ); ?></th>
                        <td><input type="checkbox" name="eu2673_change_status" value="1" <?php checked( get_option( 'eu2673_change_status', 0 ) ); ?>></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'New order status', 'eu2673-withdrawal-button' ); ?></th>
                        <td><select name="eu2673_new_status">
                            <?php foreach ( $order_statuses as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( get_option( 'eu2673_new_status' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select></td>
                    </tr>
                </table>

                <?php submit_button( __( 'Save settings', 'eu2673-withdrawal-button' ), 'primary', 'eu2673_save' ); ?>
            </form>

            <?php /* Pro upsell footer */ ?>
            <div style="margin-top:32px;background:#f8f9ff;border:1px solid #e0e0e0;border-radius:8px;padding:20px 24px;">
                <h3 style="margin:0 0 12px;color:#1a1a2e;">🚀 <?php esc_html_e( 'Need more? Upgrade to Pro', 'eu2673-withdrawal-button' ); ?></h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:16px;">
                    <?php
                    $features = [
                        '📄 ' . __( 'Official PDF acknowledgement attached to customer email', 'eu2673-withdrawal-button' ),
                        '🔐 ' . __( 'SHA-256 hash for legal proof in case of disputes', 'eu2673-withdrawal-button' ),
                        '📋 ' . __( 'Official Annex B form pre-filled in PDF', 'eu2673-withdrawal-button' ),
                        '🛡️ ' . __( 'Anti-fraud system with risk alerts', 'eu2673-withdrawal-button' ),
                        '🔄 ' . __( 'Reverse automation with order status sync', 'eu2673-withdrawal-button' ),
                        '📊 ' . __( 'Legal email traceability with exact timestamps', 'eu2673-withdrawal-button' ),
                    ];
                    foreach ( $features as $f ) :
                    ?>
                    <div style="font-size:13px;color:#333;padding:8px 12px;background:#fff;border-radius:6px;border:1px solid #e0e0e0;">
                        <?php echo esc_html( $f ); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank" class="button button-primary" style="background:#2c3e7a;border-color:#2c3e7a;font-size:14px;padding:6px 20px;height:auto;">
                    <?php esc_html_e( 'Get Pro — €39 one-time payment', 'eu2673-withdrawal-button' ); ?>
                </a>
                <span style="margin-left:12px;font-size:12px;color:#888;"><?php esc_html_e( 'Compatible with WooCommerce and PrestaShop.', 'eu2673-withdrawal-button' ); ?></span>
            </div>
        </div>
        <?php
    }

    public function page_help(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Withdrawal Button — Help', 'eu2673-withdrawal-button' ); ?></h1>
            <?php require_once dirname( __FILE__, 2 ) . '/admin/partials/page-help.php'; ?>
        </div>
        <?php
    }
}
