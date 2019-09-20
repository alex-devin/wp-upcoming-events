<!DOCTYPE HTML>
<html lang="en">

<head>
  <p>Refresh this page to load new event data into the options table.</p>

<style>	
.divTable .divTableRow:nth-child(even) {
background-color: #f2f2f2
}

.divTable{
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
.divTableCell, .divTableHead {
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
    $endDate = strtotime($eDate);
    $sDate = $events['event']['start_date'];
    $startDate = strtotime($sDate);
    $eLink = "placeholder.com";

    //Save it into an array called $singleEvent
    $singleEvent = array(
      'name' => $eName,
      'start_date' => $sDate,
      'end_date' => $eDate,
      'event_link' => $eLink
    );

    //checks to see if the $singleEvent is relevant or not (older than today's date)
    $date_now = strtotime(getDatetimeNow());

    //if today's date is less than the stored date, add it
    //(i.e. the event hasn't happened yet, it is an upcoming event)
    if ($endDate > $date_now && $endDate != null) {
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
$date = getDatetimeNow();
$result = $date->format("M d, Y");

if ($result) {
  echo $result;
} else { // format failed
  echo "Unknown Time";
}


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
  $firstEventURL = $query[0]['event_link'];
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
  print ($myvariable);
}
?>