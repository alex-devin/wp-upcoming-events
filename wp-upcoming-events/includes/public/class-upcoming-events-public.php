<?php

class upcoming_events_PUBLIC
{

    private $upcoming_events_settings;
    private $plugin_name;
    private $version;

    public function __construct($options)
    {

        $this->upcoming_events_settings = $options['settings'];
        $this->plugin_name = $options['plugin_name'];
        $this->version = $options['version'];
    }

    // SCRIPTS
    public function upcoming_events_register_public_script()
    {

        $script_name = $this->plugin_name . '-public-js';

        wp_register_script($script_name, plugin_dir_url(__FILE__) . 'js/upcoming-events-public.js', array('jquery'), $this->version, true);
    }

    public function upcoming_events_register_public_style()
    {

        $stylesheet_name = $this->plugin_name . '-public-css';

        wp_register_style($stylesheet_name, plugin_dir_url(__FILE__) . 'css/upcoming-events-public.css', array(), $this->version, 'all');
    }

    // SHORTCODES - edit here

    //the first function (add_filteredEvents_to_wp_options) grabs data from the API, filters and sorts it, and adds it to wp_options. this should be made a cron job
    //the second function (return get_events_data) returns the data within the wp_options row where we've saved the filtered events data
    public function UPCOMING_EVENTS_do_shortcode($atts)
    {
        //returns the first $nbr amount of rows in the wp_options table where the data is stored. 
        function get_first_10_event_rows()
        {
            $my_events_data = get_option('upcoming_Events', $default = false);
            $list = array();
            for ($count = 0; $count < 10; $count++) {

                $event_name = $my_events_data[$count]['name'];
                $event_start_date = $my_events_data[$count]['start_date'];
                $event_end_date = $my_events_data[$count]['end_date'];
                $event_url = $my_events_data[$count]['event_link'];
                $event_location = $my_events_data[$count]['location'];

                $aSingleEvent = array(
                    'name' => $event_name,
                    'start_date' => $event_start_date,
                    'end_date' => $event_end_date,
                    'event_link' => $event_url,
                    'location' =>  $event_location
                );
                array_push($list, $aSingleEvent);
            }
            return $list;
        }
        //just pull data from the wp_options table 
        //to refresh the data, go into the settings page for upcoming_events in the admin menu.
        $query = get_first_10_event_rows();

        if (!empty($query)) {
            ob_start();
            echo '<div class="upcomingEventsWrapper">';   // Both featured event and list to be printed in this wrapper 
            $firstEventName = $query[0]['name'];
            $firstEventStartDate = $query[0]['start_date'];
            $fDate = date('M d, Y', strtotime($firstEventStartDate));
            $firstEventEndDate = date($query[0]['end_date']);
            $eDate = date('M d, Y',strtotime($firstEventEndDate));
          
            if (strcmp($fDate, $eDate) == 0) {
              $fullDate = $fDate;
            } else {
              $fullDate = $fDate . ' - ' . $eDate;
            }
            $firstEventURL = $query[0]['event_link'];
            $location = $query[0]['location'];
            $eventImg = "";
            echo
            '<div class="clearfix">',
              '<div class="featuredEventWrapper one-third first">',
              //sorry Nathan, Mike doesn't like the upper case
              '<style>',
              '.divTable.upcomingEventsListNormalCase {
                border-bottom: 5px solid 
                #ce113f;
            }',
            '</style>',
              '<div class="featured-event-image"><img src="/wp-content/uploads/2015/06/arizona.jpeg" alt=""></div>',
              '<div class="featured-event-name"><h3>' . $firstEventName . '</h3></div>',
              '<div class="featured-event-location"><h4>' .  $location . '</h4></div>',
              '<div class="featured-event-date"><p>' . $fullDate . '</p></div>',
              '<div class="featured-event-information-url"><a href="www.' . "https://".$firstEventURL . '" class="button" >Event Information <span class="dashicons dashicons-external"></span></a></div>',
              '</div>',
              '<div class="listEventsWrapper two-thirds">',
              '<div class="divTable upcomingEventsListNormalCase">',
              // divTable heading 	
              '<div class="divTableBody">',
              '<div class="eventHeading divTableRow">',
              '<div class="divTableCell">Name</div>',
              '<div class="divTableCell">City</div>',
              '<div class="divTableCell">Event Dates</div>',
              '</div>';
            for ($count = 1; $count < 10; $count++) {
              $nextEventName = $query[$count]['name'];
              $nextEventStartDate = $query[$count]['start_date'];
              $fDate = date('M d, Y', strtotime($nextEventStartDate));
              $nextEventEndDate = $query[$count]['end_date'];
              $eDate = date('M d, Y', strtotime($nextEventEndDate));
              $nextEventURL = $query[$count]['event_link'];
              $nextEventCity = $query[$count]['location'];
              $fullDate = "";
              if (strcmp($fDate, $eDate) == 0) {
                $fullDate = $fDate;
              } else {
                $fullDate = $fDate . ' to ' . $eDate;
              }
              echo
                // Begin Loop 10 up to event rows with closest dates
                '<a href="' . "https://".$nextEventURL . '" class="eventDetails divTableRow" target="_blank" rel="noopener noreferrer">',
                '<div class="event_name divTableCell">' . $nextEventName . '</div>',
                '<div class="event_city divTableCell">' . $nextEventCity . '</div>',
                '<div class="event_date divTableCell">' . $fullDate . '</div>',
                '</a>';
              // End eventDetails divTableRow loop row
            }
            echo
              '</div>',
              '</div>', // End divTable upcomingEventsList	
              '</div>', //End ListEventsWrapper two-thirds
              '</div>';   // End upcomingEventsWrapper
            echo '</div>'; // End clearfix
            $myvariable = ob_get_contents();
            ob_end_clean();
            return $myvariable;
        }
    }
}
