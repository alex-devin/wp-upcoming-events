<?php

class upcoming_events_PUBLIC {

    private $upcoming_events_settings;
    private $plugin_name;
    private $version;

    public function __construct( $options ) {

        $this->upcoming_events_settings = $options['settings'];
        $this->plugin_name = $options['plugin_name'];
        $this->version = $options['version'];
    }

    // SCRIPTS
    public function upcoming_events_register_public_script() {

        $script_name = $this->plugin_name . '-public-js';

        wp_register_script( $script_name, plugin_dir_url( __FILE__ ) . 'js/upcoming-events-public.js', array( 'jquery' ), $this->version, true );
    }

    public function upcoming_events_register_public_style() {

        $stylesheet_name = $this->plugin_name . '-public-css';

        wp_register_style( $stylesheet_name, plugin_dir_url( __FILE__ ) . 'css/upcoming-events-public.css', array(), $this->version, 'all' );
    }

    // SHORTCODES - edit here
    public function upcoming_events_do_shortcode( $atts ) {

        $test = '';

        if ( $atts && isset( $atts['test'] ) ) {

            $test = $atts['test'];
        }

        $apikey = "9rCPJKFYcpWLM0XxhhBkGQ";
        $url = "https://www.golfgenius.com/api_v2/".$apikey."/master_roster";
        $content=file_get_contents($url);
        
        //dump the results of the api call into an array
        $array = json_decode($content, true);
        $args = array(
                'headers' => array(
                'Content-Type' => 'application/json'
                )
            );
        $userInput = $atts;

        $matches = array();

        //loop through all players
        foreach ($array as $playerArray)
        {
            if (empty( $playerArray )) {
                return;
            }
            $fname = $playerArray['member']['first_name'];
            $lname = $playerArray['member']['last_name'];

            $lastNameMatches = preg_match('/'.$userInput.'/i', $fname);
            $firstNameMatches = preg_match('/'.$userInput.'/i', $lname);
            
            if ($firstNameMatches=== 1 || $lastNameMatches === 1 )
            {
                array_push( $matches, $playerArray );
            }

        }
              
        return '<div>My Shortcode test:' . $matches . '</div>';
    }

}
