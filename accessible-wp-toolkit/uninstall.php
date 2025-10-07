<?php
// Sécurité
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

// Nettoyage d’options, etc. (exemples)
delete_option( 'awpt_version' );
// delete_option( 'awpt_settings' );
