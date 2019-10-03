<!DOCTYPE HTML>
<html lang="en">

<head>
  <p><strong>Refresh this page to load new event data into the options table.</strong></p>
  <style>
    .divTable.upcomingEventsListNormalCaseSettings {
      border-bottom: 5px solid #ce113f;
    }

    .divTableBody {
      display: table-row-group;
    }

    *,
    ::before,
    ::after {
      box-sizing: inherit;
    }

    element {
      color:
        #ffffff;
    }

    .site-container {
      word-wrap: break-word;
    }

    body {
      color: #333;
      font-family: "Source Sans Pro", sans-serif;
      font-size: 16px;
      font-weight: 400;
      line-height: 1.625;
    }

    html {
      -moz-osx-font-smoothing: grayscale;
    }

    .divTable .divTableRow:nth-child(even) {
      background-color: #f2f2f2
    }

    .divTable {
      display: table;
      width: 100%;
    }

    .divTableRow {
      display: table-row;
    }

    .divTableHeading {
      background-color: #EEE;
      display: table-header-group;
    }

    .divTableCell,
    .divTableHead {
      border: 1px solid #999999;
      display: table-cell;
      padding: 3px 10px;
    }

    .divTableHeading {
      background-color: #EEE;
      display: table-header-group;
      font-weight: bold;
    }

    .divTableFoot {
      background-color: #EEE;
      display: table-footer-group;
      font-weight: bold;
    }

    .divTableBody {
      display: table-row-group;
    }
  </style>

</html>

<?php

//TODO: Make this into a cron job!

//filters the data from the API and sorts it ascending based on end date.
//private - should only be used in add_filteredEvents_to_wp_options()

function get_data_from_API()
{
  //call the API function to get data
  $apikey = "9rCPJKFYcpWLM0XxhhBkGQ";
  $url = "https://www.golfgenius.com/api_v2/" . $apikey . "/events";
  //Save the contents of the GET request
  $response = wp_remote_get($url);

  try {
    //dump the results of the api call into an array
    $array = json_decode(wp_remote_retrieve_body($response), true);
    $tz_object = new DateTimeZone('America/New_York');
    $dateTime = new DateTime();
    $dateTime->setTimezone($tz_object);
    $now = $dateTime->format('Y-m-d H:i:s');
    echo "Data has been pulled from the API at " . $now;
    return $array;
  } catch (Exception $ex) {
    return null;
  }
}

function get_data_filter_and_sort()
{
  $array = get_data_from_api();
  //create a new array for only the data we want
  $filteredEvents = array();

  if ($array != null) {
    //loop through the array with all event data. 
    foreach ($array as $events) {
      //Pull the data we want. 
      $eName = $events['event']['name'];
      $eDate = $events['event']['end_date'];
      $eDateTime = strtotime($eDate);
      $sDate = $events['event']['start_date'];
      $eLink = $events['event']['website'];
      $city = $events['event']['location']['city'];
      $state = $events['event']['location']['state'];
      $location = "";

      if ($city == null || $state == null) {
        $location = "TBD";
      } else {
        $location = $city . ", " . $state;
      }

      //Save it into an array called $singleEvent
      $singleEvent = array(
        'name' => $eName,
        'start_date' => $sDate,
        'end_date' => $eDate,
        'event_link' => $eLink,
        'location' =>  $location
      );

      //checks to see if the $singleEvent is relevant or not (older than today's date)
      $tz_object = new DateTimeZone('America/New_York');
      $datetime = new DateTime();
      $datetime->setTimezone($tz_object);
      $date_now = strtotime($datetime->format('Y/m/d'));

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
  } else {
    $filteredEvents = null;
    return $filteredEvents;
  }
}

//adds $filteredEvents item to wp_options
//returns false if it was not able to
function add_filteredEvents_to_wp_options()
{

  $filteredEvents = get_data_filter_and_sort();

  if ($filteredEvents != null) {
    //clears out the data from the options table in order to add fresh data
    delete_option('upcoming_events');

    //Here we handle the options settings
    $option = "upcoming_events";
    $value = $filteredEvents;
    //deprecated but WP complains if it's not here as an argument
    $deprecated = "";
    $autoload = "yes";

    add_option($option, null, $deprecated, $autoload);
    delete_option($option);
    add_option($option, $value, $deprecated, $autoload);
  } else {
    return false;
  }
}

//returns the first 10 amount of rows in the upcoming_events row in wp_options array. 
function get_first_10_event_rows()
{
  $my_events_data = get_option('upcoming_events', $default = false);
  $list = array();
  if ($my_events_data != false) {
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
  } else {
    $list = null;
    return $list;
  }
}

//Begin actions

$status = add_filteredEvents_to_wp_options();
$query = get_first_10_event_rows();

ob_start();
echo '<div class="upcomingEventsWrapper">';   // Both featured event and list to be printed in this wrapper 
$firstEventName = $query[0]['name'];
$firstEventStartDate = $query[0]['start_date'];
$fDate = date('M d, Y', strtotime($firstEventStartDate));
$firstEventEndDate = date($query[0]['end_date']);
$eDate = date('M d, Y', strtotime($firstEventEndDate));

//if the start date and end date are the same, only print the start date.
//else, print the full date.
if (strcmp($fDate, $eDate) == 0) 
{
  $fullDate = $fDate;
} else 
{
  $fullDate = $fDate . ' - ' . $eDate;
}

$firstEventURL = $query[0]['event_link'];
$location = $query[0]['location'];
$eventImg = "/wp-content/uploads/2015/06/arizona.jpeg";
echo
  '<div class="featuredEventWrapper one-third first">',
  '<div class="featured-event-image"><img src="' . $eventImg . '" alt=""></div>',
  '<div class="featured-event-name"><h3>' . $firstEventName . '</h3></div>',
  '<div class="featured-event-location"><h4>' .  $location . '</h4></div>',
  '<div class="featured-event-date"><p>' . $fullDate . '</p></div>',
  '<div class="featured-event-information-url"><a href="https://"' . $firstEventURL . '" class="button" >Event Information <span class="dashicons dashicons-external"></span></a></div>',
  '</div>',
  '<div class="listEventsWrapper two-thirds">',
  '<div class="divTable upcomingEventsListNormalCaseSettings">',
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
    '<a href="' . "https://" . $nextEventURL . '" class="eventDetails divTableRow" target="_blank" rel="noopener noreferrer">',
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
$myvariable = ob_get_contents();
ob_end_clean();
echo $myvariable;

?>