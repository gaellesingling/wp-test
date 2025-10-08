<?php
/**
 * Plugin Name: FACILITI-like Side Panel (Starter)
 * Description: Adds a simple, accessible slide-in side panel with empty placeholders. Inspired by FACIL'iti-style UI. Pure JS/CSS, no dependencies.
 * Version: 0.1.0
 * Author: ChatGPT
 * License: GPL-2.0+
 * Text Domain: faciliti-side-panel
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'FSP_VERSION', '0.1.0' );
define( 'FSP_URL', plugin_dir_url( __FILE__ ) );
define( 'FSP_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue assets
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'fsp-panel', FSP_URL . 'assets/css/panel.css', [], FSP_VERSION );
    wp_enqueue_script( 'fsp-panel', FSP_URL . 'assets/js/panel.js', [], FSP_VERSION, true );
    wp_localize_script( 'fsp-panel', 'FSP', [
        'openLabel'  => __( 'Open accessibility panel', 'faciliti-side-panel' ),
        'closeLabel' => __( 'Close panel', 'faciliti-side-panel' ),
    ]);
});

/**
 * Render panel in footer by default
 */
add_action( 'wp_footer', function() {
    include FSP_PATH . 'templates/panel.php';
});

/**
 * Optional shortcode: [faciliti_panel]
 */
add_shortcode( 'faciliti_panel', function() {
    ob_start();
    include FSP_PATH . 'templates/panel.php';
    return ob_get_clean();
});
