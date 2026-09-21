<?php defined( 'ABSPATH' ) || exit;
// Variables passed from EU2673_Admin::page_requests() via include.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wrap wb-admin-wrap">
    <h1><?php esc_html_e( 'Solicitudes de Desistimiento', 'eu2673-withdrawal-button' ); ?></h1>

    <div style="background:linear-gradient(135deg,#1a1a2e 0%,#2c3e7a 100%);border-radius:10px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;box-shadow:0 4px 12px rgba(44,62,122,0.25);">
        <div style="flex:1;">
            <p style="margin:0 0 6px;font-size:15px;font-weight:700;color:#fff;letter-spacing:0.2px;">
                🔒 <?php esc_html_e( 'Estás usando Withdrawal Button Lite', 'eu2673-withdrawal-button' ); ?>
            </p>
            <p style="margin:0 0 10px;font-size:13px;color:rgba(255,255,255,0.8);line-height:1.6;">
                <?php esc_html_e( 'Esta versión cubre el cumplimiento básico. Sin embargo, en caso de disputa o reclamación te faltará documentación probatoria:', 'eu2673-withdrawal-button' ); ?>
            </p>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ PDF legal oficial</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Hash SHA-256</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Formulario Anexo B</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Trazabilidad legal</span>
                <span style="background:rgba(255,255,255,0.12);color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">✗ Antifraude</span>
            </div>
        </div>
        <a href="https://adaptatuweb.com/withdrawal-button-es/" target="_blank"
           style="background:#f59e0b;color:#1a1a2e;padding:12px 22px;border-radius:8px;font-size:14px;font-weight:800;text-decoration:none;white-space:nowrap;flex-shrink:0;box-shadow:0 2px 8px rgba(245,158,11,0.4);letter-spacing:0.2px;">
            ⭐ <?php esc_html_e( 'Actualizar a Pro — €39', 'eu2673-withdrawal-button' ); ?> →
        </a>
    </div>
    <?php
    $stat_map = [];
    foreach ( $stats as $s ) $stat_map[ $s->status ] = $s->count;
    $labels = [
        'pending'   => [ __( 'Pendientes',  'eu2673-withdrawal-button' ), '#f39c12' ],
        'approved'  => [ __( 'Aprobadas',   'eu2673-withdrawal-button' ), '#27ae60' ],
        'rejected'  => [ __( 'Rechazadas',  'eu2673-withdrawal-button' ), '#e74c3c' ],
        'completed' => [ __( 'Completadas', 'eu2673-withdrawal-button' ), '#2980b9' ],
    ];
    ?>
    <div class="wb-stats-row">
        <?php foreach ( $labels as $key => [ $label, $color ] ) : ?>
        <div class="wb-stat-card" style="border-top: 4px solid <?php echo esc_attr( $color ); ?>">
            <span class="wb-stat-number"><?php echo esc_html( $stat_map[ $key ] ?? 0 ); ?></span>
            <span class="wb-stat-label"><?php echo esc_html( $label ); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="wb-filters">
        <form method="get">
            <input type="hidden" name="page" value="eu2673-withdrawal-button">
            <select name="status_filter">
                <option value=""><?php esc_html_e( 'Todos los estados', 'eu2673-withdrawal-button' ); ?></option>
                <?php foreach ( $labels as $key => [ $label ] ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status_filter, $key ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button"><?php esc_html_e( 'Filtrar', 'eu2673-withdrawal-button' ); ?></button>
        </form>
        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'export_csv' => '1', 'status_filter' => $status_filter ], admin_url( 'admin.php?page=withdrawal-button' ) ), 'eu2673_export_csv' ) ); ?>" class="button button-secondary" style="margin-left:8px;">
            &#8595; <?php esc_html_e( 'Exportar CSV', 'eu2673-withdrawal-button' ); ?>
        </a>
    </div>
    <?php if ( empty( $items ) ) : ?>
        <div class="wb-empty"><p><?php esc_html_e( 'No hay solicitudes todavía.', 'eu2673-withdrawal-button' ); ?></p></div>
    <?php else : ?>
    <table class="wp-list-table widefat fixed striped wb-requests-table">
        <thead><tr>
            <th width="60"><?php esc_html_e( 'ID',      'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Pedido',   'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Cliente',  'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Email',    'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Motivo',   'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Fecha',    'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Estado',   'eu2673-withdrawal-button' ); ?></th>
            <th><?php esc_html_e( 'Acciones', 'eu2673-withdrawal-button' ); ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ( $items as $item ) :
            $order     = wc_get_order( $item->order_id );
            $order_url = $order ? admin_url( 'post.php?post=' . $item->order_id . '&action=edit' ) : '#';
        ?>
            <tr id="wb-row-<?php echo esc_attr( $item->id ); ?>">
                <td><strong>#<?php echo esc_html( $item->id ); ?></strong></td>
                <td><?php if ( $order ) : ?><a href="<?php echo esc_url( $order_url ); ?>">#<?php echo esc_html( $item->order_id ); ?></a><?php else : ?>#<?php echo esc_html( $item->order_id ); ?><?php endif; ?></td>
                <td><?php echo esc_html( $item->customer_name ); ?></td>
                <td><a href="mailto:<?php echo esc_attr( $item->customer_email ); ?>"><?php echo esc_html( $item->customer_email ); ?></a></td>
                <td><?php echo esc_html( $item->reason ?: '—' ); ?></td>
                <td><?php echo esc_html( date_i18n( 'j M Y H:i', strtotime( $item->created_at ) ) ); ?></td>
                <td><span class="wb-status wb-status-<?php echo esc_attr( $item->status ); ?>"><?php echo esc_html( $labels[ $item->status ][0] ?? $item->status ); ?></span></td>
                <td>
                    <select class="wb-status-select" data-id="<?php echo esc_attr( $item->id ); ?>">
                        <?php foreach ( $labels as $key => [ $label ] ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item->status, $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $total_pages = ceil( $total / $per_page );
    if ( $total_pages > 1 ) echo wp_kses_post( paginate_links( [ 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => $page, 'total' => $total_pages ] ) );
    ?>
    <?php endif; ?>
</div>
