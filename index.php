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

date_default_timezone_set("America/New_York");

// INPUT HANDLING

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Input location data form handling
  // makes sure that the inputs in the sidebar were all filled in when submit was pressed
  if(isset($_POST['right']) && !isset($_POST['covid_sure']) && !isset($_POST['file_submit']) && isset($_POST['entry_date']) && isset($_POST['exit_date']) && isset($_POST['entry_time']) && isset($_POST['exit_time']) && $_POST['entry_date'] != "" && $_POST['exit_date'] != "" && $_POST['entry_time'] != "" && $_POST['exit_time'] != "") {
    
    if(checkDateTimeValid($_POST['entry_date'], $_POST['exit_date'], $_POST['entry_time'], $_POST['exit_time'])) {
      
      // makes sure the user is logged in before trying to add anything to the table
      if (phpCAS::isAuthenticated()) {
        
        $user = strtolower(phpCAS::getUser());
        $locName = $_POST['loc_select'];
        
        $sql_stmt = $dbconn->prepare("SELECT * FROM locations WHERE locationName=:locName ");
        $sql_stmt->bindValue(':locName', $locName, PDO::PARAM_STR);
        $sql_stmt->execute();
        $locID = $sql_stmt->fetch();
        
        // string containing the information that the user is trying to add
        $loc_data = "(locationID, rcsID, entryDate, exitDate, entryTime, exitTime) VALUES (" . $locID['id'] . ", '$user', '" . $_POST['entry_date'] . "', '" . $_POST['exit_date'] . "', '" . $_POST['entry_time'] . "', '" . $_POST['exit_time'] . "')";
        
        $sql_stmt = $dbconn->prepare("SELECT * FROM locations_visited WHERE locationID=:id AND rcsID=:user AND entryDate=:entry_date AND exitDate=:exit_date AND entryTime=:entry_time AND exitTime=:exit_time");
        $sql_stmt->bindValue(':id', $locID['id'], PDO::PARAM_INT);
        $sql_stmt->bindValue(':user', $user, PDO::PARAM_STR);
        $sql_stmt->bindValue(':entry_date', $_POST['entry_date'], PDO::PARAM_STR);
        $sql_stmt->bindValue(':exit_date', $_POST['exit_date'], PDO::PARAM_STR);
        $sql_stmt->bindValue(':entry_time', $_POST['entry_time'], PDO::PARAM_STR);
        $sql_stmt->bindValue(':exit_time', $_POST['exit_time'], PDO::PARAM_STR);
        $sql_stmt->execute();
        $result = $sql_stmt->fetchAll();
        
        
        
        // if the result isn't in the table already, add it
        if(count($result) == 0) {
          $sql_stmt = $dbconn->prepare("INSERT INTO locations_visited $loc_data");
          $sql_stmt->execute();
          echo "<div id=\"alert_msg\">Added data</div>";
        }
        
      } else {
        // the user wasn't logged in, so the data will not be added
        echo "<div id=\"alert_msg\">Must be logged in to add data</div>";
      }
    } else {
      // the date/time for exit was before the entry date/time
      echo "<div id=\"alert_msg\">Entry cannot be after exit</div>";
    }
    
  } else if (isset($_POST['right'])){
    // the inputs were not all filled
    echo "<div id=\"alert_msg\">Not all inputs set</div>";
  }
  
  // I have COVID-19 button handling
  if(!isset($_POST['right']) && isset($_POST['covid_sure']) && !isset($_POST['file_submit'])) {
    
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
      
      $currentTime = date("H:i");
      $yest = date(("yy-m-d"), time()-86400);
      
      // get all locations visited for everyone within last 24hrs
      $sql_stmt = $dbconn->prepare("SELECT * FROM locations_visited WHERE (locations_visited.entryDate > :yest || (locations_visited.entryDate=:yest && locations_visited.entryTime >= :currentTime))");
      $sql_stmt->bindParam(":yest", $yest, PDO::PARAM_STR);
      $sql_stmt->bindParam(":currentTime", $currentTime, PDO::PARAM_STR);
      $sql_stmt->execute();
      $rows = $sql_stmt->fetchAll();
      
      // get all locations visited for current user within last 24hrs
      $sql_stmt = $dbconn->prepare("SELECT locationID FROM locations_visited WHERE (locations_visited.rcsID=:user && (locations_visited.entryDate > :yest || (locations_visited.entryDate = :yest && locations_visited.entryTime >= :currentTime)))");
      $sql_stmt->bindParam(":user", $user, PDO::PARAM_STR);
      $sql_stmt->bindParam(":yest", $yest, PDO::PARAM_STR);
      $sql_stmt->bindParam(":currentTime", $currentTime, PDO::PARAM_STR);
      $sql_stmt->execute();
      $results = $sql_stmt->fetchAll();
      
      
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
      echo "<div id=\"alert_msg\">All people at risk are being notified</div>";
      
    } else {
      // the user wasn't logged in, so no emails will be sent, no locations will become hotspots
      echo "<div id=\"alert_msg\">Must be logged in to add data</div>";
    }
    
  }
  
  // File upload handling
  if(!isset($_POST['right']) && !isset($_POST['covid_sure']) && isset($_POST['file_upload'])) {
    if(phpCAS::isAuthenticated()) {
      $user = strtolower(phpCAS::getUser());
      
      $file = $_FILES['file'];
      $file_name = $file['name'];
      $file_ext = explode(".", $file_name);
      $file_ext = end($file_ext);
      
      if(strtolower($file_ext) == "json") {
        $file_content_string = file_get_contents($file['tmp_name']);
        $json = json_decode($file_content_string, true);
        
        $loc_data = $json['locations'];
        
        $sql = "SELECT * FROM locations";
        $db_locs = ($dbconn->query($sql))->fetchAll();
        
        $loc_list = array();
        
        // runs for each location in the file
        // if the location is close enough to one of the RPI locations,
        //    the location will be stored as being at that location
        //    if it is not close to any RPI locations, it is ignored
        for($i = 0; $i < count($loc_data); $i++) {
          $lat = $loc_data[$i]['latitudeE7']/10000000;
          $long = $loc_data[$i]['longitudeE7']/10000000;
          for($j = 0; $j < count($db_locs); $j++) {
            $d_lat = $db_locs[$j]['latitude'] - $lat;
            $d_long = $db_locs[$j]['longitude'] - $long;
            $dist = sqrt($d_lat*$d_lat + $d_long*$d_long);
            
            if($dist <= 0.00025) {
              array_push($loc_list, array());
              $loc_list[$i] = array($db_locs[$j], $loc_data[$i]['timestampMs']/1000);
              break;
            }
          }
        }
        
        // if locations at RPI were found in the location history
        if(count($loc_list) != 0) {
          
          // this section will fill an array with locationIDs and start and end times at the locations - it determines if two time periods overlap
          $final_loc_list = array();
          array_push($final_loc_list, array($loc_list[0][0]["id"], $loc_list[0][1]));
          
          $currentcounter = 0;
          for($i = 1; $i < count($loc_list); $i++) {
            if($loc_list[$i][0]["id"] != $loc_list[$i-1][0]["id"]) {
              array_push($final_loc_list[$currentcounter], $loc_list[$i-1][1]);
              $currentcounter++;
              array_push($final_loc_list, array($loc_list[$i][0]["id"], $loc_list[$i][1]));
            }
          }
          array_push($final_loc_list[count($final_loc_list)-1], $loc_list[count($loc_list)-1][1]);
          
          /* final_loc_list breakdown
          
          [i] array of 3 items:
            0 - location id                       use:    $final_loc_list[i][0]
            1 - start time                          use:    $final_loc_list[i][1]
            2 - end time                            use:    $final_loc_list[i][2]
          
          */
          
          // insert the locations into the locations_visited table
          for($i = 0; $i < count($final_loc_list); $i++) {
            
            $locationID = $final_loc_list[$i][0];
            $entryDate = date("yy-m-d", $final_loc_list[$i][1]);
            $entryTime = date("H:i", $final_loc_list[$i][1]);
            $exitDate = date("yy-m-d", $final_loc_list[$i][2]);
            $exitTime = date("H:i", $final_loc_list[$i][2]);
            
            $sql = "INSERT INTO locations_visited (`locationID`, `rcsID`, `entryDate`, `exitDate`, `entryTime`, `exitTime`) VALUES ('$locationID', '$user', '$entryDate', '$exitDate', '$entryTime', '$exitTime')";
            $dbconn->exec($sql);
          }
          echo "<div id=\"alert_msg\">Locations added to the database</div>";
        } else {
          echo "<div id=\"alert_msg\">No locations found in the JSON file</div>";
        }
        
      } else {
        echo "<div id=\"alert_msg\">Must be JSON file type</div>";
      }
    } else {
      echo "<div id=\"alert_msg\">Must be logged in to add data</div>";
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
        <?php
          if (phpCAS::isAuthenticated()) {
            echo "<button id=\"visited_button\" onclick=\"visited_display()\">Locations Visited</button>";
          }
        ?>
        
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
          <input type="date" name="entry_date" value="<?php
              if(isset($_POST['entry_date'])) echo $_POST['entry_date'];
              else echo date("yy-m-d");
            ?>"></input>
          <input type="time" name="entry_time" value="<?php
              if(isset($_POST['entry_time'])) echo $_POST['entry_time'];
              else echo date("H:i");
            ?>"></input>
          <input type="date" name="exit_date" value="<?php
              if(isset($_POST['exit_date'])) echo $_POST['exit_date'];
              else echo date("yy-m-d");
            ?>"></input>
          <input type="time" name="exit_time" value="<?php
              if(isset($_POST['exit_time'])) echo $_POST['exit_time'];
              else echo date("H:i");
            ?>"></input>
          <button class="rightBtn" id="input_data_button" name="right" onclick="">Add Location Data</button>
        </form>
        
        <?php
          // will output the "I have COVID-19" button if the user is logged in
          if (phpCAS::isAuthenticated())
          {
            echo "<button id=\"covid_btn\">I have COVID-19</button>";
            echo "<form id=\"covid_form\" method=\"post\" action=\"index.php\">";
            echo "<button name=\"covid_sure\" id=\"covid_sure\">Confirm</button>";
            echo "</form>";
          } else {
            echo "<button id=\"covid_btn\" style='visibility:hidden'>I have COVID-19</button>";
            echo "<form id=\"covid_form\" method=\"post\" action=\"index.php\">";
            echo "<button name=\"covid_sure\" id=\"covid_sure\">Confirm</button>";
            echo "</form>";
          }
        ?>
        
        <section>
          <form id="upload_form" method="post" action="index.php" enctype="multipart/form-data">
            <p>Input location data via file upload</p>
            <input type="file" name="file" id="input_file_button"></input>
            <input class="rightBtn" name="file_upload" type="submit"></input>
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
    
    <?php
      if (phpCAS::isAuthenticated()) {
        echo "<div id=\"visited_from_db\">";
        /* Locations from locations table will be echoed here so they can be accessed by JS
           This element has display set to none so that it doesn't affect the layout */
      
      
        $sql_stmt = $dbconn->prepare("SELECT * FROM locations_visited JOIN locations ON locations_visited.locationId=locations.id WHERE rcsID=:user ORDER BY `entryDate` DESC, `entryTime` DESC");
        $sql_stmt->bindValue(':user', $user, PDO::PARAM_STR);
        $sql_stmt->execute();
        $rows = $sql_stmt->fetchAll();
        
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
        echo "</div>";
      }
    ?>
    
    
    
  </body>
</html>
