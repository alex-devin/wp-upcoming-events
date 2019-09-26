<!DOCTYPE HTML>
<html lang="en">

<head>
  <p>Refresh this page to load new event data into the options table.</p>
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
function getDatetimeNow()
{
  $tz_object = new DateTimeZone('America/New_York');
  $datetime = new DateTime();
  $datetime->setTimezone($tz_object);
  return $datetime->format('Y/m/d');
}

//retrieves the data from the API and stores it into an array, which is returned.
//returns false if not able to get data from the API.
function get_data_from_API()
{

  //call the API
  $apikey = "9rCPJKFYcpWLM0XxhhBkGQ";
  $url = "https://www.golfgenius.com/api_v2/" . $apikey . "/events";
  $response = wp_remote_get($url);

  //Save the contents of the GET request
  try {
    //dump the results of the api call into an array 
    $array = json_decode(wp_remote_retrieve_body($response), true);
    return $array;
  } catch (Exception $ex) {
    return false;
  } // end try/catch
}

//filters the data from the API and sorts it ascending based on end date.
//Adds the data to the wp_options
function update_data_filter_and_sort($data)
{
  //$array = get_data_from_API();
  //create a new array for only the data we want
  $filteredEvents = array();

  //loop through the array with all event data. 
  foreach ($data as $events) {
    //Pull the data we want. 
    $eName = $events['event']['name'];
    $eDate = $events['event']['end_date'];
    $eDate = str_replace('-', '/', $eDate);
    $eDateTime = strtotime($eDate);
    $sDate = $events['event']['start_date'];
    $sDate = str_replace('-', '/', $sDate);
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
    $date_now = strtotime(getDatetimeNow());

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
//returns false if it was not able to
function add_filteredEvents_to_wp_options($filteredEvents)
{
  //clears out the data from the options table in order to add fresh data
  delete_option('upcoming_events');
  //$filteredEvents = update_data_filter_and_sort();

  //Here we handle the options settings
  $option = "upcoming_events";
  $value = $filteredEvents;
  $deprecated = "";
  $autoload = "yes";

  //just for the first instance - if the row in the options table doesn't exist, create it.
  //also checks that the fiteredEvents array is not null.
  if (!get_option($option, $default = false) && $filteredEvents != null) {
    add_option($option, $value, $deprecated, $autoload);
    $optionValue = get_option('upcoming_events');
    //if once we've added the option and it still does not exist
    //something went wrong and we were unable to create the row.
    if (!get_option($option, $default = false)) {
      return false;
    }
  }
}

//returns the first 10 amount of rows in the upcoming_events row in wp_options array. 
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

//should already be done but just in case..
$data = null;
delete_option('upcoming_events');
//make a new call to the API
$data = get_data_from_API();
$timeNow = date_default_timezone_set('America/New_York');
$timeNow = date('M d, Y h:i:s a', time());
if ($data != false) {
  echo "successfully pulled data from API at " . $timeNow . " <br><br>";
}
//if we succeeded in getting data, update filter and sort, add to wp_options.
try {
  add_filteredEvents_to_wp_options(update_data_filter_and_sort($data));
  $query = get_first_10_event_rows();
} catch (Exception $ex) {
  $query = null;
}

if ($query != null) {
  ob_start();
  echo '<div class="upcomingEventsWrapper">';   // Both featured event and list to be printed in this wrapper 
  $firstEventName = $query[0]['name'];
  $firstEventStartDate = $query[0]['start_date'];
  $fDate = date('M d, Y', strtotime($firstEventStartDate));
  $firstEventEndDate = date($query[0]['end_date']);
  $eDate = date('M d, Y', strtotime($firstEventEndDate));

  if (strcmp($fDate, $eDate) == 0) {
    $fullDate = $fDate;
  } else {
    $fullDate = $fDate . ' - ' . $eDate;
  }
  $firstEventURL = $query[0]['event_link'];
  $location = $query[0]['location'];
  $eventImg = "";
  echo
    '<div class="featuredEventWrapper one-third first">',
    '<div class="featured-event-image"><img src="/wp-content/uploads/2015/06/arizona.jpeg" alt=""></div>',
    '<div class="featured-event-name"><h3>' . $firstEventName . '</h3></div>',
    '<div class="featured-event-location"><h4>' .  $location . '</h4></div>',
    '<div class="featured-event-date"><p>' . $fullDate . '</p></div>',
    '<div class="featured-event-information-url"><a href="www.' . "https://" . $firstEventURL . '" class="button" >Event Information <span class="dashicons dashicons-external"></span></a></div>',
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
} else if ($data == false || $query == null) {
  echo "Data could not be fetched from the API";
}

?>