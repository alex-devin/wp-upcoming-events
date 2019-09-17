<?php

class upcoming_events_CRON {

    private $upcoming_events_settings;
    private $plugin_name;
    private $version;

    public function __construct( $options ) {

        $this->upcoming_events_settings = $options['settings'];
        $this->plugin_name = $options['plugin_name'];
        $this->version = $options['version'];
    }


    // SHORTCODES - edit here
    public function upcoming_events_cron_get_all_players() {

        error_log("My Cron running to fetch all player data...");
        
        

    }
}
