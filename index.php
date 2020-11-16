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

?>

<html lang="en">
  <head>
    <title>COVID-19 Tracker</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="style.css">
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
      COVID-19 Tracker
      <?php
      if (phpCAS::isAuthenticated())
      {
        // echo "User:" . phpCAS::getUser();
        $user = strtolower(phpCAS::getUser());
        echo "<span id='username'>" . $user . "</span>";
        echo "<button id='login' onclick='logout()'>Log Out</button>";
      }
      else
      {
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
        <select id="loc_select">
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
        <input type="datetime-local" name="entry_time"></input>
        <input type="datetime-local" name="exit_time"></input>
        <button id="input_data_button" onclick="">Add Location Data</button>
        <section>
          <p>Input location data via file upload</p>
          <input type="file" id="input_data_button"></input>
          <input type="submit"></input>
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
            $locs = $locs . " \"" . $row['locationName'];
          else
            $locs = $locs . "\", " . " \"" . $row['locationName'];
          $count++;
        }
        $locs = $locs . "\" ] }";
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
            $locs = $locs . " \"" . $row['locationName'];
          else
            $locs = $locs . "\", " . " \"" . $row['locationName'];
          $count++;
        }
        $locs = $locs . "\" ] }";
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
            $locs = $locs . " [\"" . $time['entryTime'] . "\", \"" . $time['exitTime'] . "\"]";
          else
            $locs = $locs . ", " . " [\"" . $time['entryTime'] . "\", \"" . $time['exitTime'] . "\"]";
          $count++;
        }
        $locs = $locs . " ], ";
        echo $locs;
        
        $locs = "\"l_arr\": [ ";
        $count = 0;
        foreach($rows as $row) {
          if($count == 0)
            $locs = $locs . " \"" . $row['locationName'];
          else
            $locs = $locs . "\", " . " \"" . $row['locationName'];
          $count++;
        }
        $locs = $locs . "\" ] }";
        echo $locs;
      ?>
    </div>
    
  </body>
</html>
