<?php

class upcoming_events_CRON
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

    private function getDatetimeNow()
    {
        $tz_object = new DateTimeZone('America/New_York');
        $datetime = new DateTime();
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y/m/d');
    }

    //retrieves the data from the API and stores it into an array, which is returned
    private function get_data_from_API()
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
    private function update_data_filter_and_sort()
    {

        $array = get_data_from_API();
        //create a new array for only the data we want
        $filteredEvents = array();

        //loop through the array with all event data. 
        foreach ($array as $events) {
            //Pull the data we want. 
            $eName = $events['event']['name'];
            $eDate = $events['event']['end_date'];
            $eDate = str_replace('-', '/', $eDate);
            //grab the event's end date and save it as a datetime object for comparison
            $eDateTime = strtotime($eDate);
            $sDate = $events['event']['start_date'];
            $sDate = str_replace('-', '/', $sDate);
            $eLink = "placeholder.com";

            //Save it into an array called $singleEvent
            $singleEvent = array(
                'name' => $eName,
                'start_date' => $sDate,
                'end_date' => $eDate,
                'event_link' => $eLink
            );


            //checks to see if the $singleEvent is relevant or not (older than today's date)
            $date_now = getDatetimeNow();

            $date_now = strtotime($date_now);

            //if today's date is less than the stored date, add it
            //(i.e. the event hasn't happened yet, it is an upcoming event)
            if ($eDateTime > $date_now && $eDateTime != null) {
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
    private function add_filteredEvents_to_wp_options()
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

    public function upcoming_events_update_data_cron()
    {
        error_log("My Cron running to fetch all player data...");
        add_filteredEvents_to_wp_options();
    }
}
