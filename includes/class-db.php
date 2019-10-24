<?php

namespace BM_Velobasar;

class DB {

    private $table;
    
    function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . BM_TABLENAME;
    }

    /**
     * Handle initial table creation for bm-velobasar
     */
    function create_table() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $queries = array();
        $queries[] = "CREATE TABLE IF NOT EXISTS $this->table (
                        accessid VARCHAR(32) PRIMARY KEY,
                        saletext TEXT,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
        dbDelta( $queries );
        $this->create_test_data();
    }

    /**
     * Get all sale status entries at once
     */
    function get_all() {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT access_id, saletext, updated_at FROM $this->table ORDER BY access_id" );
        return $wpdb->get_results( $sql, ARRAY_A );
    }

    function get_saletext( $accessid ) {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT saletext FROM $this->table where accessid = %s", $accessid );
        return $wpdb->get_col( $sql );
    }

    function insert_or_update( $accessid, $saletext) {
        global $wpdb;
        $wpdb->replace( $this->table, array(
            'accessid' => $accessid,
            'saletext' => $saletext,
            'updated_at' => current_time( 'mysql', 0 ) )
        );
    }

    function create_test_data() {
        $this->insert_or_update( 'braunaumobil.at', '<h4>Der Basar läuft gut!</h4><p>Test paragraph</p><ul><li>Ein Verkauf</li><li>Zwei Verkauf</li></ul>');
    }

    function reset() {
        global $wpdb;
        $wpdb->query('TRUNCATE TABLE $this->table');
        $this->create_test_data();
    }

    function delete( $accessid ) {
        $wpdb->delete( $this->table, array( 'accessid' => $accessid ) );
    }
}
