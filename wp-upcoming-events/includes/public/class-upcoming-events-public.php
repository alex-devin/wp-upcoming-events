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
        function getDatetimeNow()
        {
            $tz_object = new DateTimeZone('America/New_York');
            $datetime = new DateTime();
            $datetime->setTimezone($tz_object);
            $datetime->format("M d, Y");
            return $datetime;
        }

        //retrieves the data from the API and stores it into an array, which is returned
        function get_data_from_API()
        {

            //call the API
            $apikey = "9rCPJKFYcpWLM0XxhhBkGQ";
            $url = "https://www.golfgenius.com/api_v2/" . $apikey . "/events";

            //Save the contents of the GET request
            $content = file_get_contents($url);

            //dump the results of the api call into an array
            $array = json_decode($content, true);
            return $array;
        }

        //filters the data from the API and sorts it ascending based on end date.
        //Adds the data to the wp_options
        function update_data_filter_and_sort()
        {

            $array = get_data_from_API();
            //create a new array for only the data we want
            $filteredEvents = array();

            //loop through the array with all event data. 
            foreach ($array as $events) {
                //Pull the data we want. 
                $eName = $events['event']['name'];
                $eDate = $events['event']['end_date'];
                $endDate = date("M-d-Y", strtotime($eDate));
                $sDate = $events['event']['start_date'];
                $startDate = date("M-d-Y", strtotime($sDate));
                $eLink = "placeholder.com";

                //Save it into an array called $singleEvent
                $singleEvent = array(
                    'name' => $eName,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'event_link' => $eLink
                );

                //checks to see if the $singleEvent is relevant or not (older than today's date)
                $date_now = strtotime(getDatetimeNow());

                //if today's date is less than the stored date, add it
                //(i.e. the event hasn't happened yet, it is an upcoming event)
                if ($eDate > $date_now && $eDate != null) {
                    array_push($filteredEvents, $singleEvent);
                } else {
                    //do nothing
                }
            }

            //sort the array ascending by dates (most recent event first)
            foreach ($filteredEvents as $key => $value) {
                $sort_data[$key] = $value['end_date'];
            }
            array_multisort($sort_data, SORT_ASC, $filteredEvents);

            return $filteredEvents;
        }

        //adds $filteredEvents item to wp_options
        function add_filteredEvents_to_wp_options()
        {
            //clears out the data from the options table in order to add fresh data
            delete_option('upcoming_events');
            $filteredEvents = update_data_filter_and_sort();

            //Here we handle the options settings
            $option = "upcoming_events";
            $value = $filteredEvents;
            $deprecated = "";
            $autoload = "yes";

            //just for the first instance - if the row in the options table doesn't exist, create it.
            if (!get_option($option, $default = false)) {
                add_option($option, $value, $deprecated, $autoload);
                $optionValue = get_option('upcoming_events');
                //if once we've added the option and it still does not exist
                //something went wrong and we were unable to create the row.
                if (!get_option($option, $default = false)) {
                    return false;
                }
            }
        }

        //returns the first $nbr amount of rows in an array. 
        //if $count = 'all', returns all.
        function get_first_10_event_rows()
        {
            $nbr = 10;
            $my_events_data = get_option('upcoming_Events', $default = false);

            if ($nbr == 'all') {
                //echo count($my_events_data);
                return $my_events_data;
            } else if ($nbr < count($my_events_data)) {
                $list = array();
                for ($count = 0; $count < 10; $count++) {

                    $event_name = $my_events_data[$count]['name'];
                    $event_start_date = $my_events_data[$count]['start_date'];
                    $event_end_date = $my_events_data[$count]['end_date'];
                    $event_url = $my_events_data[$count]['event_link'];

                    $aSingleEvent = array(
                        'name' => $event_name,
                        'start_date' => $event_start_date,
                        'end_date' => $event_end_date,
                        'event_link' => $event_url
                    );
                    array_push($list, $aSingleEvent);
                }
            }
            return $list;
        }

        $query = get_first_10_event_rows();
        //echo "test test";
        //echo ($query);
        ob_start();

        if (!empty($query)) {
            echo '<div class="upcomingEventsWrapper">';   // Both featured event and list to be printed in this wrapper 
            $firstEventName = $query[0]['name'];
            $firstEventStartDate = $query[0]['start_date'];
            $fDate = strtotime($firstEventStartDate);
            $fDate = date('M d, Y', $fDate);
            $firstEventEndDate = date($query[0]['end_date']);
            $eDate = strtotime($firstEventEndDate);
            $eDate = date('M d, Y', $eDate);
            $fullDate = $fDate . " - " . $eDate;
            $firstEventURL = $query[0]['event_link'];;
            $location = "Orlando, FL";
            $eventImg = "";
            echo
                // Loop 1 - event with closest start date or tagged as featured
                '<div class="featuredEventWrapper one-third first">',
                '<div class="featured-event-image"><img src="/wp-content/uploads/2015/06/arizona.jpeg" alt=""></div>',
                '<div class="featured-event-name">
                <h3>' . $firstEventName . '</h3></div>',
                '<div class="featured-event-location">' .  $location . '</h4></div>',
                '<div class="featured-event-date">' . $fullDate . '</div>',
                '<div class="featured-event-information-url"><a class="button" href="">Event Information <span class="dashicons dashicons-external"></span></a></div>',
                '</div>',
                '<div class="listEventsWrapper two-thirds">',
                '<div class="divTable upcomingEventsList">',

                // divTable heading 	
                '<div class="divTableBody">',
                '<div class="divTableRow">',
                '<div class="divTableCell">Name</div>',
                '<div class="divTableCell">City</div>',
                '<div class="divTableCell">Event Dates</div>',
                '</div>';

            for ($count = 1; $count < 10; $count++) {
                $nextEventName = $query[$count]['name'];
                $nextEventStartDate = $query[$count]['start_date'];
                $sDate = strtotime($nextEventStartDate);
                $sDate = date('M d, Y', $sDate);
                $nextEventEndDate = $query[$count]['end_date'];
                $eDate = strtotime($nextEventEndDate);
                $eDate = date('M d, Y', $eDate);
                $nextEventURL = $query[$count]['event_link'];
                $nextEventCity = 'ORLANDO, FL';
                echo
                    // Begin Loop 10 up to event rows with closest dates
                    '<div class="eventDetails divTableRow">',
                    '<div class="event_name divTableCell">' . $nextEventName . '</div>',
                    '<div class="event_city divTableCell">' . $nextEventCity . '</div>',
                    '<div class="event_date divTableCell">' . $sDate . ' to ' . $eDate . '</div>',
                    '</div>';
                // End eventDetails divTableRow loop row
            }
            echo
                '</div>',
                '</div>', // End divTable upcomingEventsList	
                '</div>';    // End listEventsWrapper two-thirds

            //wp_reset_postdata();
            echo '</div>'; // End upcomingEventsWrapper
            $myvariable = ob_get_contents();
            ob_end_clean();
            return $myvariable;
        }
    }
}
