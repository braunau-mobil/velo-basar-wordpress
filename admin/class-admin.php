<?php

namespace BM_Velobasar;

class Admin {

    function __construct() {
        add_action( 'admin_init', array($this, 'admin_init') );
    }

    function admin_init() {
        // enqueue admin style?
        register_setting( 'general', BM_VELOBASAR_API_TOKEN_OPTION,
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );
        register_setting( 'general', 'bm_velobasar_date' );

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
    }

    function settings_section() {
    }

    function settings_field_token() {
        $bm_velobasar_api_token = get_option(BM_VELOBASAR_API_TOKEN_OPTION);
        ?>
        <input type="text" id="bm_velobasar_api_token" name="bm_velobasar_api_token" size="40" minlength="20" maxlength="60" value="<?php echo $bm_velobasar_api_token ?>" required />
        <p class="description"><?php _e('Das geheime Token für die API Authentifizierung', 'bm-velobasar') ?></p>
        <?php
    }

    /**
     * Textarea for editing action names
     */
    function settings_field_date() {
        $bm_velobasar_date = get_option('bm_velobasar_date');
        ?>
        <input type="date" id="bm_velobasar_date" name="bm_velobasar_date" value="<?php echo $bm_velobasar_date ?>" min="2018-01-01" required />
        <p class="description"><?php _e('An diesem Datum soll der Basar stattfinden', 'bm-velobasar') ?></p>
        <?php
    }
}
