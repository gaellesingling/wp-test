<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class AWPT_Public {
    public function enqueue_assets() : void {
        wp_enqueue_style( 'awpt-public', AWPT_PLUGIN_URL . 'public/css/public.css', array(), AWPT_VERSION );
        wp_enqueue_script( 'awpt-public', AWPT_PLUGIN_URL . 'public/js/public.js', array(), AWPT_VERSION, true );
    }
}
