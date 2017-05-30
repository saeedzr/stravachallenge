<link type="text/css" rel="stylesheet" href="style.css">
<html>
  <head>
    <title>Strava 5K/year</title>
    </head>
</html>

<?php 
include 'vendor/autoload.php';

//use Pest;
use Strava\API\Client;
use Strava\API\Exception;
use Strava\API\Service\REST;

//$token[0] = ""; // Saeed

$athlete_row = array();

if (in_array($_GET['token'], $token)) {
    $kmgoal = 5000;
    if ($_GET['kmgoal']) $kmgoal = $_GET['kmgoal'];
    $fiveK_pace = $kmgoal/366;
    
    echo '<table id="ytd" class="pure-table" style="width:100%">
        <thead><tr>
        <th width="5%">Rank</th>
        <th width="10%">Name</th>
        <th width="10%">Year-to-date distance (KM)</th>
        <th width="10%">Pace difference to ' . round($fiveK_pace * 366) . 'KM goal (KM)</th>
        <th width="10%">KM left</th>
        <th width="32%">Last 30 days rides</th>
        <th width="32%">Last ride</th>
        </tr></thead>';

    foreach ($token as $key => $token_value) {
        try { 
            $adapter = new Pest('https://www.strava.com/api/v3');
            $service = new REST($token_value, $adapter);  // Define your user token here..
            $client = new Client($service);

            $athlete = $client->getAthlete();
    //    print_r($athlete);

            $activities = $client->getAthleteActivities(null, null, null, null);
//            print_r($activities[0]['type']);
            
            $start_date = "N/A";
            $distance = "N/A";
            $elevation = "N/A";
            $sufferScore = "";
            
            // Last ride

            foreach ($activities as $num => $details) {
                  if ($details['type'] == 'Ride') {
                      $start_date = substr($details['start_date'], 0, 10);
                      $distance = round($details['distance']/1000);
                      $elevation = round($details['total_elevation_gain']);
                      $sufferScore = $details['suffer_score'];
                      break;
                  }
            }

            $sufferScore_print = "";
            if ($sufferScore) {
                $sufferScore_print = ", <span class=\"orange\">Suffer score: " . $sufferScore . "</span>";
            }
            

            $stats = $client->getAthleteStats($athlete['id']);
    //        print_r($stats);
            
            $ytd_dist = round($stats['ytd_ride_totals']['distance']/1000);

            // Adding extra KMs for 2016
            if (date('Y') == 2016) {
                if ($athlete['firstname'] == "Maciej") {
                    $ytd_dist = $ytd_dist+2500;
                    $athlete['lastname'] .= "**";
                }

                if ($athlete['firstname'] == "Ole Petter") {
                    $ytd_dist = $ytd_dist+1000;
                    $athlete['lastname'] .= "***";
                }
            }

            $day_num = date('z') + 1;
            $year_pace = $day_num * $fiveK_pace;
            $pace_diff = round(-($year_pace - $ytd_dist));
            $pace_diff_styled;

            if ($pace_diff > 0) {
                $pace_diff_styled = '<font size="5" color="green">+' . $pace_diff . '</font>';
            } else if ($pace_diff == 0) {
                $pace_diff_styled = '<font size="5">' . abs($pace_diff) . '</font>';
            } else {
                $pace_diff_styled = '<font size="5" color="red">' . $pace_diff . '</font>';
            }
            
            $km_left = $kmgoal - $ytd_dist;
            $km_diff_styled = "";
            if ($km_left >= 0) {
                $km_diff_styled = '<font size="3" color="red">' . $km_left . '</font>';
            } else {
                $km_diff_styled = '-';
            }

            $athlete_row[$key] = array('key' => $key, 'stats' => '<td width="5%">' . $athlete['firstname'] . ' ' .$athlete['lastname'] . '</td>'
            . '<td width="10%">' . $ytd_dist . '</td>'
            . '<td width="10%">' . $pace_diff_styled . '</td>'
            . '<td width="10%">' . $km_diff_styled . '</td>'
            . '<td width="32%">Rides count: ' . $stats['recent_ride_totals']['count'] . ", Distance: " 
            . round($stats['recent_ride_totals']['distance']/1000) . " KM, Elevation: "
            . round($stats['recent_ride_totals']['elevation_gain']) . " M" . '</td>'
            . '<td width="32%">Date: ' . $start_date . ", Distance: " . $distance .
            " KM, Elevation: " . $elevation . ' M' . $sufferScore_print . '</td>', 'ytd' => $ytd_dist);
        } catch(Exception $e) {
            print $e->getMessage();
        }
    }

    /* echo("<!-- "); */
    /* print_r($athlete_row); */
    /* echo("-->"); */

    usort($athlete_row, "sortByOrder");
    $i = 1;

    foreach ($athlete_row as $key => $stats) {
        if ($i % 2 == 0) {
            if (($stats['ytd'] >= round($fiveK_pace * 366)) && ($stats['ytd'] >= 5000)) {
                echo '<tr class="pure-table-goal-odd">';
            } else {
                echo '<tr class="pure-table">';
            }
        }
        else {
            if (($stats['ytd'] >= round($fiveK_pace * 366)) && ($stats['ytd'] >= 5000)) {
                echo '<tr class="pure-table-goal">';
            } else {
                echo '<tr class="pure-table-odd">';
            }
        }


        echo '<td width="5%">' . $i . '</td>';
        print_r($athlete_row[$key]['stats']);
        $i++;
    }

    echo '</table>';
    echo '<br><font size="2">* You can pass in your own goal by specifying it in the URL: http://saeed.opoint.com/?token=tokenValue&kmgoal=4000</font>';
    if (date('Y') == 2016) {
        echo '<br><font size="2">** Maciej has gotten an extra 2500KM since he started mid-year</font>';
        echo '<br><font size="2">*** Ole Petter has gotten an extra 1000KM since he started later</font>';
    }
} else echo 'Error: Token is missing or invalid token, enter your token like: http://saeed.opoint.com/?token=tokenValue';

function sortByOrder($a, $b) {
    return $b['ytd'] - $a['ytd'];
}