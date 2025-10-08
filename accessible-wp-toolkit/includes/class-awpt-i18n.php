<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class AWPT_i18n {
    public function load_plugin_textdomain() : void {
        load_plugin_textdomain( 'accessible-wp-toolkit', false, dirname( plugin_basename( AWPT_PLUGIN_FILE ) ) . '/languages/' );
    }
}
