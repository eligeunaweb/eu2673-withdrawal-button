<?php
defined( 'ABSPATH' ) || exit;

class EU2673_Admin {

    const OPTION_INSTALL_DATE  = 'eu2673_install_date';
    const OPTION_NOTICE_CLOSED = 'eu2673_notice_closed';
    const PRO_URL              = 'https://adaptatuweb.com/withdrawal-button-es/';
    const REVIEW_URL           = 'https://wordpress.org/support/plugin/eu2673-withdrawal-button/reviews/#new-post';
    const PER_PAGE             = 20;
    const STATUSES             = [ 'pending', 'approved', 'rejected', 'completed' ];

    public function __construct() {
        add_action( 'admin_menu',                        [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts',             [ $this, 'enqueue_assets' ] );
        add_action( 'admin_notices',                     [ $this, 'notice_withdrawal_page' ] );
        add_action( 'admin_notices',                     [ $this, 'show_admin_notice' ] );
        add_action( 'wp_ajax_eu2673_dismiss_notice',     [ $this, 'dismiss_notice' ] );
        add_action( 'admin_post_eu2673_update_status',   [ $this, 'handle_update_status' ] );
        add_action( 'admin_post_eu2673_save_settings',   [ $this, 'handle_save_settings' ] );
        add_action( 'admin_post_eu2673_create_page',     [ $this, 'handle_create_page' ] );
        add_filter( 'plugin_row_meta',                   [ $this, 'plugin_row_meta' ], 10, 2 );

        if ( ! get_option( self::OPTION_INSTALL_DATE ) ) {
            update_option( self::OPTION_INSTALL_DATE, time() );
        }
    }

    /* ── Menú y recursos ────────────────────────────────────────────────── */

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
        add_submenu_page( 'eu2673-withdrawal', __( 'Withdrawal requests', 'eu2673-withdrawal-button' ), __( 'Requests', 'eu2673-withdrawal-button' ), 'manage_woocommerce', 'eu2673-withdrawal', [ $this, 'page_requests' ] );
        add_submenu_page( 'eu2673-withdrawal', __( 'Settings', 'eu2673-withdrawal-button' ), __( 'Settings', 'eu2673-withdrawal-button' ), 'manage_woocommerce', 'eu2673-settings', [ $this, 'page_settings' ] );
        add_submenu_page( 'eu2673-withdrawal', __( 'Help', 'eu2673-withdrawal-button' ), __( 'Help', 'eu2673-withdrawal-button' ), 'manage_woocommerce', 'eu2673-help', [ $this, 'page_help' ] );
    }

    public function enqueue_assets( $hook ): void {
        if ( strpos( (string) $hook, 'eu2673' ) === false ) return;
        wp_enqueue_style( 'eu2673-admin', EU2673_PLUGIN_URL . 'admin/admin.css', [], EU2673_VERSION );
    }

    /* ── Avisos ─────────────────────────────────────────────────────────── */

    /** Sin página pública, quien compra sin cuenta no puede desistir durante todo el plazo. */
    public function notice_withdrawal_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) || EU2673_Core::withdrawal_page_id() ) return;
        ?>
        <div class="notice notice-warning">
            <p><strong><?php esc_html_e( 'Withdrawal Button: there is no public withdrawal page.', 'eu2673-withdrawal-button' ); ?></strong>
            <?php esc_html_e( 'Customers who bought without an account need a page where they can withdraw at any time during the withdrawal period (Art. 11a(1) of Directive 2011/83/EU).', 'eu2673-withdrawal-button' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="eu2673_create_page">
                <?php wp_nonce_field( 'eu2673_create_page' ); ?>
                <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Create the withdrawal page', 'eu2673-withdrawal-button' ); ?></button></p>
            </form>
        </div>
        <?php
    }

    public function handle_create_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html__( 'You do not have permission to do this.', 'eu2673-withdrawal-button' ) );
        check_admin_referer( 'eu2673_create_page' );
        $id = EU2673_Core::create_withdrawal_page();
        wp_safe_redirect( add_query_arg( [ 'page' => 'eu2673-settings', 'eu2673_msg' => $id ? 'page_created' : 'page_error' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    public function show_admin_notice(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) || get_option( self::OPTION_NOTICE_CLOSED ) ) return;
        $days_active = (int) floor( ( time() - (int) get_option( self::OPTION_INSTALL_DATE, time() ) ) / DAY_IN_SECONDS );
        if ( $days_active < 14 ) return;
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->id, [ 'dashboard', 'plugins', 'toplevel_page_eu2673-withdrawal' ], true ) ) return;
        ?>
        <div class="notice notice-info is-dismissible eu2673-review-notice">
            <p><?php esc_html_e( 'Is Withdrawal Button helping your store? A short review on WordPress.org helps other merchants find it.', 'eu2673-withdrawal-button' ); ?></p>
            <p><a href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener" class="button button-primary"><?php esc_html_e( 'Leave a review on WordPress.org', 'eu2673-withdrawal-button' ); ?></a></p>
        </div>
        <script>
        document.addEventListener( 'click', function ( e ) {
            if ( ! e.target.closest( '.eu2673-review-notice .notice-dismiss' ) ) return;
            var fd = new FormData();
            fd.append( 'action', 'eu2673_dismiss_notice' );
            fd.append( 'nonce', <?php echo wp_json_encode( wp_create_nonce( 'eu2673_admin_nonce' ) ); ?> );
            fetch( ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' } );
        } );
        </script>
        <?php
    }

    public function dismiss_notice(): void {
        check_ajax_referer( 'eu2673_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error();
        update_option( self::OPTION_NOTICE_CLOSED, 1 );
        wp_send_json_success();
    }

    public function plugin_row_meta( array $links, string $file ): array {
        if ( plugin_basename( EU2673_PLUGIN_FILE ) !== $file ) return $links;
        $links[] = '<a href="' . esc_url( self::REVIEW_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Rate', 'eu2673-withdrawal-button' ) . '</a>';
        $links[] = '<a href="' . esc_url( self::PRO_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'Upgrade to Pro', 'eu2673-withdrawal-button' ) . '</a>';
        return $links;
    }

    /** Bloque de la versión Pro: una sola descripción, coherente con lo que hace la Lite. */
    private function pro_box(): void {
        ?>
        <div class="eu2673-pro-box">
            <p><strong><?php esc_html_e( 'Withdrawal Button Pro', 'eu2673-withdrawal-button' ); ?></strong> —
            <?php esc_html_e( 'PDF acknowledgement attached to the customer email, SHA-256 hash of each request, email log with exact timestamps, anti-fraud alerts and order status sync.', 'eu2673-withdrawal-button' ); ?></p>
            <p><a href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank" rel="noopener" class="button"><?php esc_html_e( 'Get Pro — €39 one-time payment', 'eu2673-withdrawal-button' ); ?></a></p>
        </div>
        <?php
    }

    /* ── Solicitudes ────────────────────────────────────────────────────── */

    public function page_requests(): void {
        global $wpdb;
        $table  = $wpdb->prefix . 'withdrawal_requests';
        $labels = EU2673_Frontend::status_labels();
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- filtros de lectura
        $filter = sanitize_key( wp_unslash( $_GET['status'] ?? '' ) );
        $filter = in_array( $filter, self::STATUSES, true ) ? $filter : '';
        $paged  = max( 1, absint( $_GET['paged'] ?? 1 ) );
        $msg    = sanitize_key( wp_unslash( $_GET['eu2673_msg'] ?? '' ) );
        $msg_id = absint( $_GET['request'] ?? 0 );
        // phpcs:enable

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $where  = $filter ? $wpdb->prepare( 'WHERE status = %s', $filter ) : '';
        $total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
        $rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d", self::PER_PAGE, ( $paged - 1 ) * self::PER_PAGE ) );
        $counts = wp_list_pluck( $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status" ), 'total', 'status' );
        // phpcs:enable
        $base_url = admin_url( 'admin.php?page=eu2673-withdrawal' );
        ?>
        <div class="wrap eu2673-wrap">
            <h1><?php esc_html_e( 'Withdrawal requests', 'eu2673-withdrawal-button' ); ?></h1>

            <?php if ( $msg ) : ?>
            <div class="notice notice-<?php echo 'error' === $msg ? 'error' : 'success'; ?>" role="status"><p>
                <?php
                if ( 'error' === $msg ) {
                    esc_html_e( 'The request could not be updated.', 'eu2673-withdrawal-button' );
                } else {
                    /* translators: %d: request ID */
                    printf( esc_html__( 'Request #%d updated.', 'eu2673-withdrawal-button' ), (int) $msg_id );
                    if ( 'updated_sent' === $msg ) {
                        echo ' ' . esc_html__( 'The customer has been notified by email.', 'eu2673-withdrawal-button' );
                    } elseif ( 'updated_failed' === $msg ) {
                        echo ' ' . esc_html__( 'The email to the customer could not be sent. Check your mail settings.', 'eu2673-withdrawal-button' );
                    }
                }
                ?>
            </p></div>
            <?php endif; ?>

            <ul class="subsubsub">
                <li><a href="<?php echo esc_url( $base_url ); ?>" <?php echo '' === $filter ? 'class="current" aria-current="page"' : ''; ?>><?php esc_html_e( 'All', 'eu2673-withdrawal-button' ); ?> <span class="count">(<?php echo (int) array_sum( $counts ); ?>)</span></a> |</li>
                <?php foreach ( $labels as $key => $label ) : ?>
                <li><a href="<?php echo esc_url( add_query_arg( 'status', $key, $base_url ) ); ?>" <?php echo $filter === $key ? 'class="current" aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?> <span class="count">(<?php echo (int) ( $counts[ $key ] ?? 0 ); ?>)</span></a><?php echo 'completed' === $key ? '' : ' |'; ?></li>
                <?php endforeach; ?>
            </ul>

            <table class="wp-list-table widefat fixed striped eu2673-requests">
                <thead><tr>
                    <th scope="col" class="eu2673-col-ref"><?php esc_html_e( 'Ref.', 'eu2673-withdrawal-button' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Order', 'eu2673-withdrawal-button' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Customer', 'eu2673-withdrawal-button' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Reason', 'eu2673-withdrawal-button' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Date', 'eu2673-withdrawal-button' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Status', 'eu2673-withdrawal-button' ); ?></th>
                    <th scope="col" class="eu2673-col-action"><?php esc_html_e( 'Change status', 'eu2673-withdrawal-button' ); ?></th>
                </tr></thead>
                <tbody>
                <?php if ( ! $rows ) : ?>
                    <tr><td colspan="7"><?php esc_html_e( 'No requests found.', 'eu2673-withdrawal-button' ); ?></td></tr>
                <?php endif; ?>
                <?php foreach ( $rows as $req ) :
                    $order = wc_get_order( $req->order_id );
                    $sel   = 'eu2673-status-' . (int) $req->id;
                    $note  = 'eu2673-note-' . (int) $req->id;
                    ?>
                    <tr>
                        <td>#<?php echo (int) $req->id; ?></td>
                        <td><?php if ( $order ) : ?><a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a><?php else : ?>#<?php echo (int) $req->order_id; ?><?php endif; ?></td>
                        <td><?php echo esc_html( $req->customer_name ); ?><br><a href="mailto:<?php echo esc_attr( $req->customer_email ); ?>"><?php echo esc_html( $req->customer_email ); ?></a></td>
                        <td><?php echo $req->reason ? esc_html( $req->reason ) : '—'; ?></td>
                        <td><?php echo esc_html( EU2673_Core::format_db_datetime( $req->created_at ) ); ?></td>
                        <td><span class="eu2673-status eu2673-status--<?php echo esc_attr( $req->status ); ?>"><?php echo esc_html( $labels[ $req->status ] ?? $req->status ); ?></span></td>
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="eu2673-status-form">
                                <input type="hidden" name="action" value="eu2673_update_status">
                                <input type="hidden" name="request_id" value="<?php echo (int) $req->id; ?>">
                                <?php wp_nonce_field( 'eu2673_update_status_' . (int) $req->id ); ?>
                                <label class="screen-reader-text" for="<?php echo esc_attr( $sel ); ?>"><?php /* translators: %d: request ID */ printf( esc_html__( 'New status for request #%d', 'eu2673-withdrawal-button' ), (int) $req->id ); ?></label>
                                <select id="<?php echo esc_attr( $sel ); ?>" name="status">
                                    <?php foreach ( $labels as $key => $label ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $req->status, $key ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label class="screen-reader-text" for="<?php echo esc_attr( $note ); ?>"><?php esc_html_e( 'Note for the customer (optional)', 'eu2673-withdrawal-button' ); ?></label>
                                <input type="text" id="<?php echo esc_attr( $note ); ?>" name="admin_note" maxlength="500" placeholder="<?php esc_attr_e( 'Note for the customer (optional)', 'eu2673-withdrawal-button' ); ?>" value="<?php echo esc_attr( (string) $req->admin_notes ); ?>">
                                <button type="submit" class="button"><?php esc_html_e( 'Update', 'eu2673-withdrawal-button' ); ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $pages = (int) ceil( $total / self::PER_PAGE );
            if ( $pages > 1 ) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post( paginate_links( [
                    'base'    => add_query_arg( 'paged', '%#%', $filter ? add_query_arg( 'status', $filter, $base_url ) : $base_url ),
                    'format'  => '',
                    'current' => $paged,
                    'total'   => $pages,
                ] ) ) . '</div></div>';
            }
            if ( '1' === (string) get_option( 'eu2673_send_status_email', '1' ) ) {
                echo '<p class="description">' . esc_html__( 'When you change the status to Approved, Rejected or Completed, the customer receives an email with the new status and your note.', 'eu2673-withdrawal-button' ) . '</p>';
            }
            $this->pro_box();
            ?>
        </div>
        <?php
    }

    public function handle_update_status(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html__( 'You do not have permission to do this.', 'eu2673-withdrawal-button' ) );
        $id = absint( $_POST['request_id'] ?? 0 );
        check_admin_referer( 'eu2673_update_status_' . $id );
        $status = sanitize_key( wp_unslash( $_POST['status'] ?? '' ) );
        $note   = mb_substr( sanitize_textarea_field( wp_unslash( $_POST['admin_note'] ?? '' ) ), 0, 500 );
        $back   = wp_get_referer() ?: admin_url( 'admin.php?page=eu2673-withdrawal' );
        $back   = remove_query_arg( [ 'eu2673_msg', 'request' ], $back );

        global $wpdb;
        $table = $wpdb->prefix . 'withdrawal_requests';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $request || ! in_array( $status, self::STATUSES, true ) ) {
            wp_safe_redirect( add_query_arg( 'eu2673_msg', 'error', $back ) );
            exit;
        }

        $changed = $request->status !== $status;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $table,
            [ 'status' => $status, 'admin_notes' => $note, 'processed_at' => current_time( 'mysql' ) ],
            [ 'id' => $id ],
            [ '%s', '%s', '%s' ],
            [ '%d' ]
        );

        $msg = 'updated';
        if ( $changed ) {
            $labels = EU2673_Frontend::status_labels();
            $order  = wc_get_order( $request->order_id );
            if ( $order ) {
                /* translators: 1: request ID, 2: status name */
                $order->add_order_note( sprintf( __( 'Withdrawal request #%1$d marked as %2$s.', 'eu2673-withdrawal-button' ), $id, $labels[ $status ] ) );
            }
            if ( 'pending' !== $status && '1' === (string) get_option( 'eu2673_send_status_email', '1' ) ) {
                $sent = ( new EU2673_Emails() )->send_status_update_email( $id, $status );
                $msg  = $sent ? 'updated_sent' : 'updated_failed';
            }
        }
        wp_safe_redirect( add_query_arg( [ 'eu2673_msg' => $msg, 'request' => $id ], $back ) );
        exit;
    }

    /* ── Ajustes ────────────────────────────────────────────────────────── */

    private function checkbox_options(): array {
        return [ 'eu2673_show_on_guest_orders', 'eu2673_require_reason', 'eu2673_footer_link', 'eu2673_email_link', 'eu2673_change_order_status', 'eu2673_send_admin_email', 'eu2673_send_status_email' ];
    }

    public function handle_save_settings(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html__( 'You do not have permission to do this.', 'eu2673-withdrawal-button' ) );
        check_admin_referer( 'eu2673_settings' );
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanea campo a campo
        $post = wp_unslash( $_POST );
        // phpcs:enable

        update_option( 'eu2673_withdrawal_days', max( 14, min( 365, absint( $post['eu2673_withdrawal_days'] ?? 14 ) ) ) );
        update_option( 'eu2673_grace_days', max( 0, min( 365, absint( $post['eu2673_grace_days'] ?? 0 ) ) ) );
        update_option( 'eu2673_start_from', 'order_date' === ( $post['eu2673_start_from'] ?? '' ) ? 'order_date' : 'delivery_date' );
        update_option( 'eu2673_button_text', sanitize_text_field( $post['eu2673_button_text'] ?? '' ) );
        update_option( 'eu2673_email_subject_customer', sanitize_text_field( $post['eu2673_email_subject_customer'] ?? '' ) );
        update_option( 'eu2673_email_subject_admin', sanitize_text_field( $post['eu2673_email_subject_admin'] ?? '' ) );
        $admin_email = sanitize_email( $post['eu2673_admin_email'] ?? '' );
        update_option( 'eu2673_admin_email', is_email( $admin_email ) ? $admin_email : get_option( 'admin_email' ) );
        foreach ( $this->checkbox_options() as $key ) {
            update_option( $key, empty( $post[ $key ] ) ? '0' : '1' );
        }

        $statuses   = array_keys( wc_get_order_statuses() );
        $new_status = sanitize_text_field( $post['eu2673_new_order_status'] ?? 'wc-on-hold' );
        update_option( 'eu2673_new_order_status', in_array( $new_status, $statuses, true ) && 'wc-refunded' !== $new_status ? $new_status : 'wc-on-hold' );
        $excluded = array_map( 'sanitize_text_field', (array) ( $post['eu2673_exclude_statuses'] ?? [] ) );
        update_option( 'eu2673_exclude_statuses', array_values( array_intersect( $excluded, $statuses ) ) );

        $page_id = absint( $post['eu2673_withdrawal_page_id'] ?? 0 );
        if ( $page_id && has_shortcode( (string) get_post_field( 'post_content', $page_id ), EU2673_Core::SHORTCODE ) ) {
            update_option( 'eu2673_withdrawal_page_id', $page_id );
        }

        wp_safe_redirect( add_query_arg( [ 'page' => 'eu2673-settings', 'eu2673_msg' => 'saved' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    private function field_checkbox( $key, $label, $default = '1', $description = '' ): void {
        $id = str_replace( '_', '-', $key );
        ?>
        <label for="<?php echo esc_attr( $id ); ?>"><input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( '1', (string) get_option( $key, $default ) ); ?>> <?php echo esc_html( $label ); ?></label>
        <?php if ( $description ) : ?><p class="description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
        <?php
    }

    public function page_settings(): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $msg            = sanitize_key( wp_unslash( $_GET['eu2673_msg'] ?? '' ) );
        $order_statuses = wc_get_order_statuses();
        $excluded       = (array) get_option( 'eu2673_exclude_statuses', [ 'wc-refunded', 'wc-cancelled', 'wc-failed' ] );
        $page_id        = EU2673_Core::withdrawal_page_id();
        ?>
        <div class="wrap eu2673-wrap">
            <h1><?php esc_html_e( 'Withdrawal Button — Settings', 'eu2673-withdrawal-button' ); ?></h1>
            <?php if ( 'saved' === $msg ) : ?>
                <div class="notice notice-success" role="status"><p><?php esc_html_e( 'Settings saved.', 'eu2673-withdrawal-button' ); ?></p></div>
            <?php elseif ( 'page_created' === $msg ) : ?>
                <div class="notice notice-success" role="status"><p><?php esc_html_e( 'The withdrawal page has been created.', 'eu2673-withdrawal-button' ); ?>
                <?php if ( $page_id ) : ?><a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>"><?php esc_html_e( 'View page', 'eu2673-withdrawal-button' ); ?></a><?php endif; ?></p></div>
            <?php elseif ( 'page_error' === $msg ) : ?>
                <div class="notice notice-error" role="alert"><p><?php esc_html_e( 'The withdrawal page could not be created.', 'eu2673-withdrawal-button' ); ?></p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="eu2673_save_settings">
                <?php wp_nonce_field( 'eu2673_settings' ); ?>

                <h2><?php esc_html_e( 'Withdrawal period', 'eu2673-withdrawal-button' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="eu2673-withdrawal-days"><?php esc_html_e( 'Withdrawal period (days)', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><input type="number" id="eu2673-withdrawal-days" name="eu2673_withdrawal_days" value="<?php echo (int) get_option( 'eu2673_withdrawal_days', 14 ); ?>" min="14" max="365" class="small-text" aria-describedby="eu2673-withdrawal-days-desc">
                        <p class="description" id="eu2673-withdrawal-days-desc"><?php esc_html_e( 'Legal minimum is 14 calendar days. You can offer more.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eu2673-grace-days"><?php esc_html_e( 'Grace days (optional)', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><input type="number" id="eu2673-grace-days" name="eu2673_grace_days" value="<?php echo (int) get_option( 'eu2673_grace_days', 0 ); ?>" min="0" max="365" class="small-text" aria-describedby="eu2673-grace-days-desc">
                        <p class="description" id="eu2673-grace-days-desc"><?php esc_html_e( 'Extra days on top of the legal deadline.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eu2673-start-from"><?php esc_html_e( 'Start deadline from', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><select id="eu2673-start-from" name="eu2673_start_from" aria-describedby="eu2673-start-from-desc">
                            <option value="delivery_date" <?php selected( get_option( 'eu2673_start_from', 'order_date' ), 'delivery_date' ); ?>><?php esc_html_e( 'Delivery date (when order is completed)', 'eu2673-withdrawal-button' ); ?></option>
                            <option value="order_date" <?php selected( get_option( 'eu2673_start_from', 'order_date' ), 'order_date' ); ?>><?php esc_html_e( 'Order date', 'eu2673-withdrawal-button' ); ?></option>
                        </select>
                        <p class="description" id="eu2673-start-from-desc"><?php esc_html_e( 'For goods, the period starts when the customer receives them (Art. 9(2)(b) of Directive 2011/83/EU): mark the order as Completed when it is delivered. Until then the button stays available. Use the order date only if you sell services or digital content.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Do not show the button for orders with these statuses', 'eu2673-withdrawal-button' ); ?></th>
                        <td><fieldset><legend class="screen-reader-text"><?php esc_html_e( 'Do not show the button for orders with these statuses', 'eu2673-withdrawal-button' ); ?></legend>
                        <?php foreach ( $order_statuses as $slug => $label ) :
                            if ( in_array( $slug, [ 'wc-pending', 'wc-checkout-draft' ], true ) ) continue; ?>
                            <label style="display:inline-block;margin:0 16px 6px 0;"><input type="checkbox" name="eu2673_exclude_statuses[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $excluded, true ) ); ?>> <?php echo esc_html( $label ); ?></label>
                        <?php endforeach; ?>
                        <p class="description"><?php esc_html_e( 'Do not exclude Completed: customers usually withdraw after receiving the order.', 'eu2673-withdrawal-button' ); ?></p></fieldset></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Where the button appears', 'eu2673-withdrawal-button' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="eu2673-button-text"><?php esc_html_e( 'Button text', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><input type="text" id="eu2673-button-text" name="eu2673_button_text" value="<?php echo esc_attr( get_option( 'eu2673_button_text', '' ) ); ?>" class="regular-text" placeholder="<?php echo esc_attr( __( 'Desistir del contrato aquí', 'eu2673-withdrawal-button' ) ); ?>" aria-describedby="eu2673-button-text-desc">
                        <p class="description" id="eu2673-button-text-desc"><?php esc_html_e( 'Leave empty to use the wording of the Directive ("withdraw from contract here"), translated into the store language.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eu2673-withdrawal-page-id"><?php esc_html_e( 'Public withdrawal page', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td>
                            <?php
                            wp_dropdown_pages( [
                                'name'              => 'eu2673_withdrawal_page_id',
                                'id'                => 'eu2673-withdrawal-page-id',
                                'selected'          => $page_id,
                                'show_option_none'  => __( '— None —', 'eu2673-withdrawal-button' ),
                                'option_none_value' => '0',
                            ] );
                            ?>
                            <p class="description"><?php esc_html_e( 'Page with the [eu2673_withdrawal_form] shortcode. Customers who bought without an account withdraw here with their order number and email.', 'eu2673-withdrawal-button' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Links to the withdrawal page', 'eu2673-withdrawal-button' ); ?></th>
                        <td>
                            <?php $this->field_checkbox( 'eu2673_footer_link', __( 'Show a link at the bottom of every page', 'eu2673-withdrawal-button' ) ); ?><br>
                            <?php $this->field_checkbox( 'eu2673_email_link', __( 'Add a link to the WooCommerce order emails sent to the customer', 'eu2673-withdrawal-button' ) ); ?>
                            <p class="description"><?php esc_html_e( 'The withdrawal function must be prominently displayed and available throughout the withdrawal period (Art. 11a(1)).', 'eu2673-withdrawal-button' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Order received page', 'eu2673-withdrawal-button' ); ?></th>
                        <td><?php $this->field_checkbox( 'eu2673_show_on_guest_orders', __( 'Show the button on the order received page', 'eu2673-withdrawal-button' ) ); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Reason', 'eu2673-withdrawal-button' ); ?></th>
                        <td><?php $this->field_checkbox( 'eu2673_require_reason', __( 'Show an optional reason field', 'eu2673-withdrawal-button' ), '0', __( 'The customer never has to give a reason; the field is always optional.', 'eu2673-withdrawal-button' ) ); ?></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Emails', 'eu2673-withdrawal-button' ); ?></h2>
                <p><?php esc_html_e( 'The acknowledgement of receipt is always sent to the customer, as required by Art. 11a(4).', 'eu2673-withdrawal-button' ); ?></p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="eu2673-email-subject-customer"><?php esc_html_e( 'Customer email subject', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><input type="text" id="eu2673-email-subject-customer" name="eu2673_email_subject_customer" value="<?php echo esc_attr( get_option( 'eu2673_email_subject_customer', '' ) ); ?>" class="regular-text" aria-describedby="eu2673-subject-desc">
                        <p class="description" id="eu2673-subject-desc"><?php esc_html_e( 'Leave empty for the default text in the store language. You can use {order_id} and {customer_name}.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Status updates', 'eu2673-withdrawal-button' ); ?></th>
                        <td><?php $this->field_checkbox( 'eu2673_send_status_email', __( 'Email the customer when you change the status of a request', 'eu2673-withdrawal-button' ) ); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Store notification', 'eu2673-withdrawal-button' ); ?></th>
                        <td><?php $this->field_checkbox( 'eu2673_send_admin_email', __( 'Email the store when a customer withdraws', 'eu2673-withdrawal-button' ) ); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eu2673-admin-email"><?php esc_html_e( 'Admin email', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><input type="email" id="eu2673-admin-email" name="eu2673_admin_email" value="<?php echo esc_attr( get_option( 'eu2673_admin_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eu2673-email-subject-admin"><?php esc_html_e( 'Store email subject', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><input type="text" id="eu2673-email-subject-admin" name="eu2673_email_subject_admin" value="<?php echo esc_attr( get_option( 'eu2673_email_subject_admin', '' ) ); ?>" class="regular-text" aria-describedby="eu2673-subject-desc"></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Order status', 'eu2673-withdrawal-button' ); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Change order status automatically', 'eu2673-withdrawal-button' ); ?></th>
                        <td><?php $this->field_checkbox( 'eu2673_change_order_status', __( 'Change the order status when the customer withdraws', 'eu2673-withdrawal-button' ), '0' ); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eu2673-new-order-status"><?php esc_html_e( 'New order status', 'eu2673-withdrawal-button' ); ?></label></th>
                        <td><select id="eu2673-new-order-status" name="eu2673_new_order_status" aria-describedby="eu2673-new-status-desc">
                            <?php foreach ( $order_statuses as $slug => $label ) :
                                if ( in_array( $slug, [ 'wc-refunded', 'wc-checkout-draft' ], true ) ) continue; ?>
                            <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( get_option( 'eu2673_new_order_status', 'wc-on-hold' ), $slug ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description" id="eu2673-new-status-desc"><?php esc_html_e( 'Refunds are never made automatically: refund the order in WooCommerce once you have checked it.', 'eu2673-withdrawal-button' ); ?></p></td>
                    </tr>
                </table>

                <?php submit_button( __( 'Save settings', 'eu2673-withdrawal-button' ) ); ?>
            </form>
            <?php $this->pro_box(); ?>
        </div>
        <?php
    }

    public function page_help(): void {
        require EU2673_PLUGIN_DIR . 'admin/page-help.php';
    }
}
