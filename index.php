<?php

error_reporting(0);

// RPI-CAS LOGIN

// phpCAS file
include_once("res/phpCAS-1.3.6/CAS.php");

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
  if(isset($_POST['entry_date']) && isset($_POST['exit_date']) && isset($_POST['entry_time']) && isset($_POST['exit_time']) && $_POST['entry_date'] != "" && $_POST['exit_date'] != "" && $_POST['entry_time'] != "" && $_POST['exit_time'] != "") {
    
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
    
  } else {
    // the inputs were not all filled
    //echo "Not all inputs set (:{()>";
    echo "<script>alert('Not all inputs set');</script>";
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
      <img src="res/icon3.png"></img>
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
            <!-- Add PHP here to loop through all locations and echo the option values -->
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
          <button class="rightBtn" id="input_data_button" onclick="">Add Location Data</button>
        </form>
        
        <?php
          if (phpCAS::isAuthenticated())
          {
            echo "<button id=\"covid_btn\">I have COVID-19</button>";
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
      <!-- Location from locations table will be echoed here so they can be accessed by JS
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
      <!-- Location from locations table will be echoed here so they can be accessed by JS
           This element has display set to none so that it doesn't affect the layout -->
      <?php
        $rows = $dbconn->query(
          "SELECT * FROM locations WHERE id IN (SELECT locationId FROM hotspots)"
        );
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
    
    <div id="visited_from_db">
      <!-- Location from locations table will be echoed here so they can be accessed by JS
           This element has display set to none so that it doesn't affect the layout -->
      <?php
        $sql = "SELECT * FROM locations WHERE id IN (
          SELECT locationID FROM locations_visited WHERE rcsID='$user'
        )";
        $rows = $dbconn->query($sql);
        
        $sql = "SELECT * FROM locations_visited WHERE rcsID='$user'";
        $times = $dbconn->query($sql);
        
        $locs = "{ \"t_arr\": [ ";
        $count = 0;
        foreach($times as $time) {
          if($count == 0)
            $locs = $locs . " [\"" . $time['entryDate'] . ": " . $time['entryTime'] . "\", \"" . $time['exitDate'] . ": " . $time['exitTime'] . "\"]";
          else
            $locs = $locs . ", " . " [\"" . $time['entryDate'] . ": " . $time['entryTime'] . "\", \"" . $time['exitDate'] . ": " . $time['exitTime'] . "\"]";
          $count++;
        }
        $locs = $locs . " ], ";
        echo $locs;
        
        $locs = "\"l_arr\": [ ";
        $count = 0;
        foreach($rows as $row) {
          if($count == 0)
            $locs = $locs . " [\"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'];
          else
            $locs = $locs . "], " . " [\"" . $row['locationName'] . "\", " . $row['latitude'] . ", " . $row['longitude'];
          $count++;
        }
        $locs = $locs . "] ] }";
        echo $locs;
      ?>
    </div>
    
  </body>
</html>
