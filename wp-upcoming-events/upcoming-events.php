<?php

/**
 * Plugin Name: GN Upcoming Events
 * Plugin URI: https://github.com/alex-devin/wp-upcoming-events 
 * Description: A WordPress plugin that shows the 4 most recent upcoming events.
 * Version: 1.1.0
 * Author: Alex DeVincenzo <alex.devincenzo@golfchannel.com>
 * License: Private
 * GitHub Plugin URI: https://github.com/alex-devin/wp-upcoming-events 
*/

// die if this file is called directly
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'UPCOMING_EVENTS_VERSION', '1.1.0' );

require plugin_dir_path( __FILE__ ) . 'includes/class-upcoming-events.php';

function run_UPCOMING_EVENTS() {

    $defaults = array();

    $plugin = new UPCOMING_EVENTS( array(
        'plugin_name' => 'gn_top_player',
        'settings' => get_option( 'UPCOMING_EVENTS_settings', $defaults )
    ));
    $plugin->run();
}

// run it!
run_UPCOMING_EVENTS();