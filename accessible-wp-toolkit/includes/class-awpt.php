<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class AWPT {
    protected $loader;
    protected $i18n;
    protected $admin;
    protected $public;

    public function __construct() {
        $this->loader = new AWPT_Loader();
        $this->i18n   = new AWPT_i18n();
        $this->admin  = new AWPT_Admin();
        $this->public = new AWPT_Public();

        $this->define_hooks();
    }

    private function define_hooks() : void {
        // i18n
        $this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_plugin_textdomain' );

        // Admin
        $this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_assets' );
        $this->loader->add_action( 'admin_menu', $this->admin, 'add_menu' );

        // Public
        $this->loader->add_action( 'wp_enqueue_scripts', $this->public, 'enqueue_assets' );
    }

    public function run() : void {
        $this->loader->run();
    }
}
