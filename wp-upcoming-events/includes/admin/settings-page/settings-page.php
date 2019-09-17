<!DOCTYPE HTML>
<html lang = "en">
<head>
<p>Refresh this page to load new event data into the options table.</p>
</html>

<?php
//call the API
$apikey = "9rCPJKFYcpWLM0XxhhBkGQ";
$url = "https://www.golfgenius.com/api_v2/".$apikey."/events";

//Save the contents of the GET request
$content=file_get_contents($url);
        
//dump the results of the api call into an array
$array = json_decode($content, true);

//create a new array for only the data we want
$filteredEvents = array();

//loop through the array with all event data. 
foreach ($array as $events)
{
  //Pull the data we want. 
  $eName = $events['event']['name'];
  $eDate = $events['event']['end_date'];
  $sDate = $events['event']['start_date'];
  $eLink = "placeholder.com";

  //Save it into an array called $singleEvent
  $singleEvent = array (
    'name' => $eName, 
    'start_date' => $sDate, 
    'end_date' => $eDate,
    'event_link' => $eLink
    );
    //Push the single event into our main array, $filteretEvents, 
    //which contains only the data we want from every event.
    array_push( $filteredEvents, $singleEvent );
}
//clears out the data from the options table in order to add fresh data
delete_option('upcoming_events');

//Here we handle the options settings
$option = "upcoming_events";
$value = $filteredEvents; 
$deprecated= ""; 
$autoload = "yes";

//just for the first instance - if the row in the options table doesn't exist, create it.
if(!get_option($option, $default = false ))
{
  add_option( $option, $value, $deprecated, $autoload );
  echo "Option was added and data has been loaded."."<br><br>";
  //if once we've added the option and it still does not exist
  //something went wrong and we were unable to create the row.
  if(!get_option($option, $default = false )){
    echo "Option could not be created."."<br><br>";
  }
}

//checks to see that the row within table exists
if(get_option($option, $default = false ))
{
  echo "Row in options exists"."<br><br>";
  //retrieves the data in the table
  $optionValue = get_option('upcoming_events'); 
  echo "Current values within the ".$option." row: "."<br><br>";
  //print it out for troubleshooting purposes.
  var_dump($optionValue);
  echo "<br><br>";
}
?>