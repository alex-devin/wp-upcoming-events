<?php
/**
 * Plugin Name: top-players-settingspage
 * Plugin URI:  tbd
 * Description: Allows the editor to search for players after pulling in data from the API
 * Version:     1.0.0
 * Author:      Alex DeVincenzo <alex.devincenzo@golfchannel.com>
 * Author URI:  tbd
 * Text Domain: wporg
 * Domain Path: /languages
 * License:     tbd
 */
        echo "test"."<br>";
        echo $_POST['input_bar']."<br>";
        print_r($_POST)."<br>";
        echo "<br>";
        echo "end test";

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


        var_dump($array);  

        $userInput = 'aa';

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
        echo "Testing 123"."<br>";
        var_dump($matches);
        
        
        ?>