<?php

namespace BM_Velobasar;

use WP_Query;

class Shortcode {

    private $db;
    
    function __construct( $db ) {
        $this->db = $db;
        add_action( 'init', array( $this, 'init' ) );
        add_shortcode( 'bmvelobasar', array( $this, 'shortcode' ) );
    }

    function init() {
        global $post;
        wp_register_style( 'bm-velobasar-style', BM_VELOBASAR_PLUGIN_URL . 'css/main.css', array(), BM_VELOBASAR_VERSION );
    }

    /**
     * Shortcode : show sale status form to user
     */
    function shortcode( $atts ) {
        wp_enqueue_style( 'bm-velobasar-style' );

        $nosale_message = '<p>' . __('Für die eingegebene Kunden-ID sind bisher keine Verkäufe vorhanden.', 'bm-velobasar') . '</p>';

        $atts = shortcode_atts(array(
            'titletag'    => 'h4',
            'title'       => ''
        ), $atts);

        $bm_velobasar_date = get_option('bm_velobasar_date');
        if( $bm_velobasar_date != date('Y-m-d')) {
            return;
        }

        ob_start();

        if ( isset( $atts['title'] ) ) {
            echo '<' . $atts['titletag'] . '>' . $atts['title'] . '</' . $atts['titletag'] . '>'; 
        }
        ?>
        <form id="bm-velobasar-search" method="post" name="bm-velobasar-salestatus" action="<?php echo get_permalink(); ?>" >
            <input id="accessid" name="accessid" type="text" value="" placeholder="<?php _e( 'Ihre Abfrage-ID', 'bm-velobasar' ) ?>" />
        <button type="submit" class="button"><span class="icon-search"></span><?php _e( 'Status abrufen', 'bm-velobasar' ) ?></button>
        </form>

        <?php
        // TODO: add captcha
        $accessid = false;
        if( isset($_POST['accessid']) ) {
            $accessid = sanitize_text_field( $_POST['accessid'] );
        } else {
            if( get_query_var(BM_VELOBASAR_GET_ID_PARAM) ) {
                $accessid = sanitize_text_field( get_query_var(BM_VELOBASAR_GET_ID_PARAM) );
            }
        }
        if( $accessid ) {
            $saletext = $this->db->get_saletext( $accessid );
            if( ! $saletext or sizeof( $saletext ) < 1 ) {
                echo $nosale_message;
            } else {
                echo '<div class="bm-velobasar-saletext">';
                // echo esc_html( $saletext[0] );
                echo $saletext[0]; // allow HTML tags
                echo '</div>';
            }
        }
        return ob_get_clean();
    }
}
