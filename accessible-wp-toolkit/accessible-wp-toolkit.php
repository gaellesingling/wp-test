<?php
/**
 * Plugin Name:       Accessible WP Toolkit
 * Plugin URI:        https://example.com/accessible-wp-toolkit
 * Description:       Outils d’accessibilité pour WordPress : contraste, audit clavier, repères ARIA et médias.
 * Version:           0.2.0
 * Author:            Accessible WP Toolkit Team
 * Author URI:        https://example.com
 * Text Domain:       accessible-wp-toolkit
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Chemins utiles
define( 'AWPT_VERSION', '0.2.0' );
define( 'AWPT_PLUGIN_FILE', __FILE__ );
define( 'AWPT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AWPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'AWPT_MIN_WP', '6.0' );

// Charge nos classes
require_once AWPT_PLUGIN_DIR . 'includes/class-awpt-loader.php';
require_once AWPT_PLUGIN_DIR . 'includes/class-awpt-i18n.php';
require_once AWPT_PLUGIN_DIR . 'includes/class-awpt.php';
require_once AWPT_PLUGIN_DIR . 'admin/class-awpt-admin.php';
require_once AWPT_PLUGIN_DIR . 'public/class-awpt-public.php';
require_once AWPT_PLUGIN_DIR . 'includes/class-awpt-accessibility-api.php';
require_once AWPT_PLUGIN_DIR . 'includes/helpers.php';

/**
 * Activation / Désactivation
 */
function awpt_activate() {
    // Exemple: vérifier version WP, créer options par défaut
    update_option( 'awpt_version', AWPT_VERSION );
}
function awpt_deactivate() {
    // Exemple: nettoyer des transient, cron, etc.
}
register_activation_hook( __FILE__, 'awpt_activate' );
register_deactivation_hook( __FILE__, 'awpt_deactivate' );

/**
 * Lancer le plugin
 */
function awpt_run() {
    $plugin = new AWPT();
    $plugin->run();
}
awpt_run();
