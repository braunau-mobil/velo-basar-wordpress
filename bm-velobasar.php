<?php

/**
 * Plugin Name: Braunaumobil Velobasar Verkaufsstatus
 * Description: Wordpress Plugin mit Shortcode, DB Tabelle und REST API fuer die Verkaufsabfrage vom Braunaumobil.at Velobasar
 * Version:     0.1
 * Author:      Phil Mühlberger
 * Author URI:  https://braunaumobil.at
 * Text Domain: bm-velobasar
 * Domain Path: /languages
 * Network:     false
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


use BM_Velobasar\DB;
use BM_Velobasar\Api;
use BM_Velobasar\Shortcode;
use BM_Velobasar\Admin;

define( 'BM_VELOBASAR_VERSION', '0.1' );
define( 'BM_VELOBASAR_PLUGIN_DIR', __DIR__ );
define( 'BM_VELOBASAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BM_TABLENAME', 'bm_velobasar_salestatus' );
define( 'BM_VELOBASAR_API_TOKEN_OPTION', 'bm_velobasar_api_token' );
define( 'BM_VELOBASAR_API_TOKEN_MINLEN', 20 );
define( 'BM_VELOBASAR_API_TOKEN_MAXLEN', 60 );

class BM_Velobasar {

    private $db;

    function __construct() {
        // load dependencies
        require_once __DIR__ . '/includes/class-db.php';
        require_once __DIR__ . '/includes/class-api.php';
        require_once __DIR__ . '/includes/class-shortcode.php';
        if( is_admin() ) {
            require_once __DIR__ . '/admin/class-admin.php';
        }

        // if ( defined('WP_CLI') && WP_CLI ) {
        //    require_once __DIR__ . '/cli/class-cli.php';
        // }

        $this->db = new DB();
        $bm_velobasar_date = get_option('bm_velobasar_date');
        if( $bm_velobasar_date == date('Y-m-d')) {
            $api = new Api( $this->db );
        }
        $shortcode = new Shortcode( $this->db );
        if( is_admin() ) {
            $admin = new Admin( $this->db );
        }

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        register_uninstall_hook( __FILE__, 'uninstall' );
    }

    function activate() {
        $this->db->create_table();
    }

    function deactivate() {
    }
}

function uninstall() {
    global $wpdb;
    $table = $wpdb->prefix . BM_TABLENAME;
    $wpdb->query( "DROP TABLE IF EXISTS $table" );
}

new BM_Velobasar();
