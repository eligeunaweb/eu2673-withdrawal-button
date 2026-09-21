<?php
/**
 * Plugin Name: EU2673 Withdrawal Button
 * Plugin URI:  https://adaptatuweb.com/en-gb/withdrawal-button/
 * Description: Añade un botón de desistimiento legal a tu tienda WooCommerce. Cumple con la Directiva UE 2023/2673 (obligatoria desde el 19 de junio de 2026). El cliente puede ejercer su derecho de desistimiento en 1 clic, con acuse de recibo automático por email.
 * Version:     1.1.2
 * Author:      Álvaro Martínez - AdaptaTuWeb.com
 * Author URI:  https://adaptatuweb.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: eu2673-withdrawal-button
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 */

defined( 'ABSPATH' ) || exit;

define( 'EU2673_VERSION',     '1.1.2' );
define( 'EU2673_PLUGIN_FILE', __FILE__ );
define( 'EU2673_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'EU2673_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'EU2673_PLUGIN_SLUG', 'eu2673-withdrawal-button' );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$eu2673_classes = [
    'class-eu2673-activator',
    'class-eu2673-core',
    'class-eu2673-frontend',
    'class-eu2673-ajax',
    'class-eu2673-emails',
    'class-eu2673-admin',
    'class-eu2673-checkout',
    'class-eu2673-product',
    'class-eu2673-guest',
];
foreach ( $eu2673_classes as $eu2673_class ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    require_once EU2673_PLUGIN_DIR . 'includes/' . $eu2673_class . '.php';
}

register_activation_hook( __FILE__,   [ 'EU2673_Activator', 'activate'   ] );
register_deactivation_hook( __FILE__, [ 'EU2673_Activator', 'deactivate' ] );

// Declarar compatibilidad con HPOS (High-Performance Order Storage) de WooCommerce
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

add_action( 'plugins_loaded', [ 'EU2673_Core', 'init' ] );
