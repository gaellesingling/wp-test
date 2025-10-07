<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class AWPT_Admin {
    public function enqueue_assets( $hook ) : void {
        // Charger uniquement sur notre page
        if ( isset( $_GET['page'] ) && 'awpt' === $_GET['page'] ) {
            wp_enqueue_style( 'awpt-admin', AWPT_PLUGIN_URL . 'admin/css/admin.css', array(), AWPT_VERSION );
            wp_enqueue_script( 'awpt-admin', AWPT_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), AWPT_VERSION, true );
        }
    }

    public function add_menu() : void {
        add_menu_page(
            __( 'Accessibilité', 'accessible-wp-toolkit' ),
            __( 'Accessibilité', 'accessible-wp-toolkit' ),
            awpt_capability(),
            'awpt',
            array( $this, 'render_page' ),
            'dashicons-universal-access-alt',
            58
        );
    }

    public function render_page() : void {
        if ( ! current_user_can( awpt_capability() ) ) { return; }
        $awpt_state = awpt_admin_state();
        include_once AWPT_PLUGIN_DIR . 'admin/partials/awpt-admin-display.php';
    }
}
