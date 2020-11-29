<?php

//error_reporting(0);

// RPI-CAS LOGIN

// phpCAS file
include_once("res/phpCAS-1.3.6/CAS.php");
require_once "Mail.php";

phpCAS::setDebug(); 
phpCAS::setVerbose(true);
// create the client      authentication site, port (https), cas folder
phpCAS::client(CAS_VERSION_2_0, "cas-auth.rpi.edu", 443, "/cas/");
// set certifications
// phpCAS::setCasServerCACert("res/cacert.pem");
// line below keeps it from checking certifications
phpCAS::setNoCasServerValidation();


// DATABASE CREATION

include("initDB.php");


// function used that returns TRUE if date/time 1 is earlier than date/time 2
function checkDateTimeValid($entryDate, $exitDate, $entryTime, $exitTime) {
  
  $entry = strtotime("$entryDate $entryTime");
  $exit = strtotime("$exitDate $exitTime");
  if(strtotime("$exitDate $exitTime") - strtotime("$entryDate $entryTime") >= 0) {
    return TRUE;
  } else {
    return FALSE;
  }
}

// INPUT HANDLING

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // makes sure that the inputs in the sidebar were all filled in when submit was pressed
  if(isset($_POST['right']) && !isset($_POST['covid_sure']) && isset($_POST['entry_date']) && isset($_POST['exit_date']) && isset($_POST['entry_time']) && isset($_POST['exit_time']) && $_POST['entry_date'] != "" && $_POST['exit_date'] != "" && $_POST['entry_time'] != "" && $_POST['exit_time'] != "") {
    
    if(checkDateTimeValid($_POST['entry_date'], $_POST['exit_date'], $_POST['entry_time'], $_POST['exit_time'])) {
      
      // makes sure the user is logged in before trying to add anything to the table
      if (phpCAS::isAuthenticated()) {
        
        $user = strtolower(phpCAS::getUser());
        $locName = $_POST['loc_select'];
        $result = $dbconn->query("SELECT * FROM locations WHERE locationName='$locName'");
        $locID = $result->fetch();
        
        // string containing the information that the user is trying to add
        $loc_data = "(locationID, rcsID, entryDate, exitDate, entryTime, exitTime) VALUES (" . $locID['id'] . ", '$user', '" . $_POST['entry_date'] . "', '" . $_POST['exit_date'] . "', '" . $_POST['entry_time'] . "', '" . $_POST['exit_time'] . "')";
        
        // gets all rows that have the same values as the one trying to be added
        $sql = "SELECT * FROM locations_visited WHERE locationID=" . $locID['id'] . " AND rcsID='$user' AND entryDate='" . $_POST['entry_date'] . "' AND exitDate='" . $_POST['exit_date'] . "' AND entryTime='" . $_POST['entry_time'] . "' AND exitTime='" . $_POST['exit_time'] . "'";
        $result = $dbconn->query($sql);
        
        // if the result isn't in the table already, add it
        if(count($result->fetchAll()) == 0) {
          $sql = "INSERT INTO locations_visited $loc_data";
          $dbconn->query($sql);
          // echo "Logged in: added data<br>";
          echo "<script>alert('Added data');</script>";
        } else {
          // echo "Result already in the table<br>";
          // echo "<script>alert('Result already in the table');</script>";
        }
        
      } else {
        // the user wasn't logged in, so the data will not be added
        // echo "Not logged in: did not add data<br>";
        echo "<script>alert('Must be logged in to add data');</script>";
      }
    } else {
      // the date/time for exit was before the entry date/time
      echo "<script>alert('Entry cannot be after exit');</script>";
    }
    
  } else if (isset($_POST['right'])){
    // the inputs were not all filled
    //echo "Not all inputs set (:{()>";
    echo "<script>alert('Not all inputs set');</script>";
  }
  /*
  if(isset($_POST['right']))
    echo "loc set\n";
  else
    echo "loc not set\n";
  if(isset($_POST['covid_sure']))
    echo "covid set\n";
  else
    echo "covid not set\n";
  */
  // I have COVID-19 button handling
  if(!isset($_POST['right']) && isset($_POST['covid_sure'])) {
    
    // makes sure the user is logged in before trying to add anything to the table
    if (phpCAS::isAuthenticated()) {
      
      $user = strtolower(phpCAS::getUser());
      // email variables - recipients and headers set later
      $from = '<covidsleuth.alert@gmail.com>';
      $subject = 'Project Demo Test';
      $body = "This is a test email to see if our project's email sending is working. It does not relate to any of your COVID-19 tests.\n\nThis email can be ignored.\n\nHave a good day!\n\n- COVID Sleuth Dev Team";
      
      // SMTP for mailing - gmail used here
      $smtp = Mail::factory('smtp', array(
        'host' => 'ssl://smtp.gmail.com',
        'port' => '465',
        'auth' => true,
        'username' => 'covidsleuth.alert@gmail.com',
        'password' => 'covidsleuthadmin345'
      ));
      
          
      date_default_timezone_set("America/New_York");
      $currentTime = date("H:i");
      $yest = date(("yy-m-d"), time()-86400);
      
      // get all locations visited for everyone within last 24hrs
      $sql = "SELECT * FROM locations_visited WHERE (locations_visited.entryDate > \"$yest\" || (locations_visited.entryDate=\"$yest\" && locations_visited.entryTime >= \"$currentTime\"))";
      $rows = $dbconn->query($sql);
      
      // get all locations visited for current user within last 24hrs
      $sql = "SELECT locationID FROM locations_visited WHERE (locations_visited.rcsID=\"$user\" && (locations_visited.entryDate > \"$yest\" || (locations_visited.entryDate = \"$yest\" && locations_visited.entryTime >= \"$currentTime\")))";
      $results = $dbconn->query($sql);
      
      // puts results from above query in an array
      $locs_to_check = array();
      foreach($results as $ltc) {
        array_push($locs_to_check, $ltc['locationID']);
      }
      $locs_to_check = array_unique($locs_to_check);
      
      // add all infected locations to hotspots if not already hotspots
      foreach($locs_to_check as $ltc) {
        $sql = "INSERT INTO hotspots (locationID) VALUES(" . $ltc . ")";
        // will attempt to execute the query - won't insert if duplicate primary key
        try {
          $dbconn->exec($sql);
        } catch (\Throwable $th) {
          // just skip
        }
      }
      
      // determines which users need to be alerted
      $users_to_alert = array();
      foreach($rows as $row) {
        if(!in_array($row["rcsID"], $users_to_alert)) {
          if(in_array($row["locationID"], $locs_to_check)) {
            if(!($row["rcsID"] == $user)) {
              array_push($users_to_alert,$row["rcsID"]);
            }
          }
        }
      }
      
      // alerts users via email that they are at risk
      foreach($users_to_alert as $uta) {
        $to = "<$uta@rpi.edu>";
        $headers = array(
          'From' => $from,
          'To' => $to,
          'Subject' => $subject
        );
        
        $mail = $smtp->send($to, $headers, $body);
        /* mail error checking
        if (PEAR::isError($mail)) {
            echo('<p>' . $mail->getMessage() . '</p>');
        } else {
            echo("<p>Message successfully sent to litala@rpi.edu!</p>");
        }*/
      }
      
    } else {
      // the user wasn't logged in, so no emails will be sent, no locations will become hotspots
      // echo "Not logged in: did not add data<br>";
      echo "<script>alert('Must be logged in to add data');</script>";
    }
    
  }

}

?>

<html lang="en">
  <head>
    <title>COVID Sleuth</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="res/icon3.png">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="index.js" defer></script>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAMewe8_kbhPAxh7Lnexd6VrtGT3N_-R3Y&callback=initMap&libraries=&v=weekly"
      defer
    ></script>
  </head>
  
  <body>
    <!-- heading with the title and login button -->
    <div id="header">
    <a href="/"><img src="res/icon3.png"></img></a>
      COVID Sleuth
      <?php
      if (phpCAS::isAuthenticated()) {
        // echo "User:" . phpCAS::getUser();
        $user = strtolower(phpCAS::getUser());
        echo "<span id='username'>" . $user . "</span>";
        echo "<button id='login' onclick='logout()'>Log Out</button>";
      } else {
        echo "<button id='login' onclick='login()'>Log In</button>";
      }
      ?>
    </div>
    <section id="main">
      <aside id="left_sidebar">
        <button id="location_button" onclick="location_display()">Locations</button>
        <button id="hotspot_button" onclick="hotspot_display()">Hotspots</button>
        <button id="visited_button" onclick="visited_display()">Locations Visited</button>
        <!-- These locations will be filled from a database -->
        <ul id="loc_list"></ul>
      </aside>
      
      <!-- This will be filled with the map -->
      <div id="map"></div>
      
      <aside id="right_sidebar">
        <form id="data_form" method="post" action="index.php">
          <select id="loc_select" name="loc_select">
            <!-- loop through all locations and echo the option values -->
            <?php
              $rows = $dbconn->query("SELECT * FROM locations");
              $count = 0;
              foreach($rows as $row) {
                $loc = $row['locationName'];
                echo "<option value=\"$loc\">$loc</option>";
                $count++;
              }
            ?>
          </select>
          <input type="date" name="entry_date"></input>
          <input type="time" name="entry_time"></input>
          <input type="date" name="exit_date"></input>
          <input type="time" name="exit_time"></input>
          <button class="rightBtn" id="input_data_button" name="right" onclick="">Add Location Data</button>
        </form>
        
        <?php
          // will output the "I have COVID-19" button if the user is logged in
          if (phpCAS::isAuthenticated())
          {
            echo "<button id=\"covid_btn\">I have COVID-19</button>";
            echo "<form id=\"covid_form\" method=\"post\" action=\"index.php\">";
            echo "</form>";
          }
        ?>
        
        <section>
          <form>
            <p>Input location data via file upload</p>
            <input type="file" id="input_file_button"></input>
            <input class="rightBtn" type="submit"></input>
          </form>
        </section>
      </aside>
    </section>
    
    <div id="locs_from_db">
      <!-- Locations from locations table will be echoed here so they can be accessed by JS
           This element has display set to none so that it doesn't affect the layout -->
      <?php
        $rows = $dbconn->query("SELECT * FROM locations");
        $locs = "{ \"arr\": [ ";
        $count = 0;
        foreach($rows as $row) {
          if($count == 0)
            $locs = $locs . " [\"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'];
          else
            $locs = $locs . "], [" . " \"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'];
          $count++;
        }
        $locs = $locs . " ] ]}";
        echo $locs;
      ?>
    </div>
    
    <div id="hotspots_from_db">
      <!-- Locations from locations table will be echoed here so they can be accessed by JS
           This element has display set to none so that it doesn't affect the layout -->
      <?php
        $rows = $dbconn->query(
          "SELECT * FROM locations WHERE id IN (SELECT locationId FROM hotspots)"
        );
        $locs = "{ \"arr\": [ [";
        $count = 0;
        foreach($rows as $row) {
          if($count == 0)
            $locs = $locs . " \"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'];
          else
            $locs = $locs . "], [ \"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'];
          $count++;
        }
        $locs = $locs . " ] ]}";
        echo $locs;
      ?>
    </div>
    
    <div id="visited_from_db">
      <!-- Locatiosn from locations table will be echoed here so they can be accessed by JS
           This element has display set to none so that it doesn't affect the layout -->
      
      <?php
        $sql = "SELECT * FROM locations_visited JOIN locations ON locations_visited.locationId=locations.id WHERE rcsID='" . $user . "' ORDER BY `entryDate` DESC, `entryTime` DESC";
        $rows = $dbconn->query($sql);
        
        /*
            JSON format:
            { "arr": [ ["loc_name", "loc_lat", "loc_long", "loc_entr", "loc_exit"], ["loc_name", "loc_lat", "loc_long", "loc_entr", "loc_exit"] ] }
        */
        
        $locs = "{ \"arr\": [ ";
        $count = 0;
        foreach($rows as $row) {
          if($count != 0)
            $locs = $locs . ", ";
          $locs = $locs . " [\"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'] . ", \"" . $row['entryDate'] . ": " . $row['entryTime'] . "\", \"" . $row['exitDate'] . ": " . $row['exitTime'] . "\"]";
          $count++;
        }
        $locs = $locs . " ] }";
        
        echo $locs;
      ?>
    </div>
    
  </body>
</html>
