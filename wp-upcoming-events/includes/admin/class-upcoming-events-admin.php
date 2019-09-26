<?php

class upcoming_events_ADMIN {

    private $upcoming_events_settings;
    private $plugin_name;
    private $version;

    public function __construct( $options ) {

        $this->upcoming_events_settings = $options['settings'];
        $this->plugin_name = $options['plugin_name'];
        $this->version = $options['version'];
    }

    // ADMIN MENU ITEM
    public function upcoming_events_admin_menu() {

        add_submenu_page( 'options-general.php', 'Load API Data For Upcoming Events', 'Refresh Upcoming Events', 'manage_options', 'upcoming-events-settingspage', array( &$this, 'upcoming_events_print_settings_page' ) );
    }

    public function upcoming_events_print_settings_page() {

        require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings-page/settings-page.php' );
    }

    // STYLES
    public function upcoming_events_enqueue_styles($hook) {

        if ($hook !== 'settings_page_upcoming_events-settingspage') {
            return;
        }

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/upcoming-events-admin.css', array(), $this->version, 'all' );
    }

    // SCRIPTS
    public function upcoming_events_register_admin_script($hook) {

        if ($hook !== 'settings_page_upcoming_events-settingspage') {
            return;
        }

        $script_name = $this->plugin_name . '-admin-js';

        $localizedData = array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        );

        wp_register_script( $script_name, plugin_dir_url( __FILE__ ) . 'js/upcoming-events-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script(  $script_name, 'topPlayersAdminData', $localizedData );
        wp_enqueue_script( $script_name );
    }
}
?>