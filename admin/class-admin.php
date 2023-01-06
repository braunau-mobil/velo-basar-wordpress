<?php

namespace BM_Velobasar;

use BM_Velobasar\API;

class Admin {

    private $db;
    private $pretty_permalinks;

    function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_init', array($this, 'admin_init') );
    }

    function admin_init() {
        // enqueue admin style and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'update_option_bm_velobasar_reset', array( $this, 'bm_velobasar_reset' ), 10, 2 );
        $this->pretty_permalinks = get_option( 'permalink_structure' );
        register_setting( 'general', BM_VELOBASAR_API_TOKEN_OPTION,
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_token' ),
            )
        );
        register_setting( 'general', 'bm_velobasar_date' );
        register_setting( 'general',
                          'bm_velobasar_formurl',
                          array(
                              'type'              => 'string',
                              'sanitize_callback' => array( $this, 'sanitize_formurl' ),
                          )
        );
        register_setting( 'general', 'bm_velobasar_resturl' );
        register_setting( 'general', 'bm_velobasar_reset' );

        add_settings_section(
            'bm-velobasar',
            __('BM Velobasar', 'bm-velobasar'),
            array($this, 'settings_section'),
            'general'
        );

        add_settings_field(
            BM_VELOBASAR_API_TOKEN_OPTION,
            __( 'Geheimes API Token', 'bm-velobasar' ),
            array($this, 'settings_field_token'),
            'general',
            'bm-velobasar'
        );

        add_settings_field(
            'bm_velobasar_date',
            __( 'Basar Datum', 'bm-velobasar' ),
            array($this, 'settings_field_date'),
            'general',
            'bm-velobasar'
        );

        add_settings_field(
            'bm_velobasar_formurl',
            __( 'Ziel-URL des Abfrageformulars (optional)', 'bm-velobasar' ),
            array($this, 'settings_field_formurl'),
            'general',
            'bm-velobasar'
        );

        add_settings_field(
            'bm_velobasar_resturl',
            __( 'URL des REST API Endpunkt', 'bm-velobasar' ),
            array($this, 'settings_field_resturl'),
            'general',
            'bm-velobasar'
        );

        add_settings_field(
            'bm_velobasar_reset',
            __( 'Plugin und Daten zurücksetzen', 'bm-velobasar' ),
            array($this, 'settings_field_reset'),
            'general',
            'bm-velobasar'
        );
    }

    function settings_section() {
    }

    function settings_field_token() {
        $bm_velobasar_api_token = get_option(BM_VELOBASAR_API_TOKEN_OPTION);
        ?>
        <input type="text" id="bm_velobasar_api_token" name="bm_velobasar_api_token" size="40"
            minlength="<?php echo BM_VELOBASAR_API_TOKEN_MINLEN ?>"
            maxlength="<?php echo BM_VELOBASAR_API_TOKEN_MAXLEN ?>"
            value="<?php echo $bm_velobasar_api_token ?>" required />
        <p class="description"><?php echo sprintf( __('Das geheime Token für die API Authentifizierung (%d-%d Zeichen)', 'bm-velobasar'),
            BM_VELOBASAR_API_TOKEN_MINLEN, BM_VELOBASAR_API_TOKEN_MAXLEN) ?></p>
        <?php
    }

    /**
     * Textarea for editing action names
     */
    function settings_field_date() {
        $bm_velobasar_date = get_option( 'bm_velobasar_date' );
        ?>
        <input type="date" id="bm_velobasar_date" name="bm_velobasar_date" value="<?php echo $bm_velobasar_date ?>" min="2018-01-01" required />
        <p class="description"><?php _e('An diesem Datum soll der Basar stattfinden', 'bm-velobasar') ?></p>
        <?php
    }

    function settings_field_formurl() {
        $bm_velobasar_formurl = get_option( 'bm_velobasar_formurl' );
        ?>
        <input type="text" id="bm_velobasar_formurl" name="bm_velobasar_formurl" size="80"
            minlength="0"
            maxlength="80"
            value="<?php echo $bm_velobasar_formurl ?>" required />
                 <p class="description"><?php echo sprintf( __('Fixe URL f&uuml;r Form Submit (0-80 Zeichen) [optional]. Default: Aktuell angezeigte Url. Auf der Zielseite mu&szlig; der Shortcode installiert sein.', 'bm-velobasar')) ?></p>
        <?php
    }

    /**
     * Readonly textline for REST API URL
     */
    function settings_field_resturl() {
        ?>
        <input type="text" id="bm_velobasar_resturl" name="bm_velobasar_resturl" size="40" value="<?php echo $this->get_rest_url() ?>" readonly />
        <p class="description"><?php _e('Die zu verwendende URL für den REST API Endpunkt', 'bm-velobasar') ?></p>
        <?php
    }

    function settings_field_reset() {
        ?>
        <input type="checkbox" id="bm_velobasar_reset" name="bm_velobasar_reset" value="1" onchange="bm_reset_confirm('bm_velobasar_reset')" <?php checked(1, get_option('bm_velobasar_reset'), false); ?> />
        <p class="description"><?php _e('Vorsicht! Plugin und DB Reset - Löscht alles', 'bm-velobasar') ?></p>
        <?php
    }

    function sanitize_token( $token ) {
        $token = sanitize_text_field( $token );
        if( strlen( $token ) < BM_VELOBASAR_API_TOKEN_MINLEN ) {
            add_settings_error(
                BM_VELOBASAR_API_TOKEN_OPTION,
                'bm-velobasar-short-token',
                sprintf( __('Fehler: Das BM-Velobasar Token ist zu kurz. Minimum %d Zeichen!'), BM_VELOBASAR_API_TOKEN_MINLEN ),
                'error'
            );
            # return sprintf( __('Fehler: Token kürzer als %d Zeichen!'), BM_VELOBASAR_API_TOKEN_MINLEN );
            return;
        }
        if( strlen( $token ) > BM_VELOBASAR_API_TOKEN_MAXLEN ) {
            add_settings_error(
                BM_VELOBASAR_API_TOKEN_OPTION,
                'bm-velobasar-long-token',
                sprintf( __('Fehler: Das BM-Velobasar Token ist länger als %d Zeichen'), BM_VELOBASAR_API_TOKEN_MAXLEN ),
                'error'
            );
            # return sprintf( __('Fehler: Token ist länger als %d Zeichen!'), BM_VELOBASAR_API_TOKEN_MAXLEN );
            return;
        }
        return $token;
    }

    function sanitize_formurl( $formurl ) {
        # First case: clear form url parameter when left empty
        if( strlen( $formurl ) == 0 ) {
            return '';
        }
        $formurl = sanitize_url( $formurl, array('https', 'http') );
        if( ! $formurl ) {
            add_settings_error(
                'bm_velobasar_formurl',
                'bm-velobasar-formurl-format-error',
                sprintf( __('Fehler: Das Format der Form Url ist falsch. Erlaubt ist nur http oder https.') ),
                'error'
            );
            return;
        }
        if( strlen( $formurl ) > 80 ) {
            add_settings_error(
                'bm_velobasar_formurl',
                'bm-velobasar-long-formurl',
                sprintf( __('Fehler: Die Submit URL des Abfrageformulars ist zu lang. Maximum 80 Zeichen!') ),
                'error'
            );
            return;
        }
        return $formurl;
    }

    function bm_velobasar_reset( $old_value, $new_value ) {
        if( $new_value ) {
            delete_option( 'bm_velobasar_reset' );
            if( $new_value != $old_value ) {
                $this->db->reset();
                delete_option( BM_VELOBASAR_API_TOKEN_OPTION );
                delete_option( 'bm_velobasar_date' );
            }
        }
    }

    function admin_enqueue_scripts() {
        wp_register_style( 'bm-velobasar-style-admin', BM_VELOBASAR_PLUGIN_URL . 'css/admin.css' );
        wp_enqueue_style( 'bm-velobasar-style-admin' );
        wp_register_script( 'bm-velobasar-script-admin', BM_VELOBASAR_PLUGIN_URL . 'js/admin.js' );
        wp_enqueue_script( 'bm-velobasar-script-admin' );
    }

    function get_rest_url() {
        global $wp;
        if( empty( $this->pretty_permalinks ) ) {
            return home_url( $wp->request ) . '/?rest_route=' . API::get_endpoint_url();
        } else {
            return home_url( $wp->request ) . '/wp-json' . API::get_endpoint_url();
        }
    }
}
