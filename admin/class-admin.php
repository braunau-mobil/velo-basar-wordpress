<?php

namespace BM_Velobasar;

class Admin {

    private $db;

    function __construct( $db ) {
        $this->db = $db;
        add_action( 'admin_init', array($this, 'admin_init') );
    }

    function admin_init() {
        // enqueue admin style and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'update_option_bm_velobasar_reset', array( $this, 'bm_velobasar_reset' ), 10, 2 );
        register_setting( 'general', BM_VELOBASAR_API_TOKEN_OPTION,
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_token' ),
            )
        );
        register_setting( 'general', 'bm_velobasar_date' );
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

    function settings_field_reset() {
        ?>
        <input type="checkbox" id="bm_velobasar_reset" name="bm_velobasar_reset" value="1" onchange="bm_reset_confirm('bm_velobasar_reset')" <?php checked(1, get_option('bm_velobasar_reset'), false); ?> />
        <p class="description"><?php _e('Vorsicht! Plugin und DB Reset - Löscht alles', 'bm-velobasar') ?></p>
        <?php
    }

    function sanitize_token($token) {
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
}