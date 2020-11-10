<?php

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

?>

<html lang="en">
  <head>
    <title>COVID-19 Tracker</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="index.js" defer></script>
  </head>
  
  <body>
    <!-- heading with the title and login button -->
    <div>
      COVID-19 Tracker
      <?php
      if (phpCAS::isAuthenticated())
      {
        // echo "User:" . phpCAS::getUser();
        echo "<span id='username'>" . strtolower(phpCAS::getUser()) . "</span>";
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
        <button id="visited_button" onclick="visited_display()">Places You Visited</button>
        <!-- These locations will be filled from a database -->
        <ul id="loc_list">
          <li>Location 1</li>
          <li>Location 2</li>
          <li>Location 3</li>
          <li>Location 4</li>
          <li>Location 5</li>
        </ul>
      </aside>
      
      <!-- This will be filled with the map -->
      <aside id="map">
        <img width=100% height=100% src="res/map_placeholder_rpi.jpg" alt="Map Placeholder">
      </aside>
      
      <aside id="right_sidebar">
        <select id="loc_select">
          <option value="cyan">Cyan - default</option>
          <option value="red">Red</option>
          <option value="orange">Orange</option>
          <option value="yellow">Yellow</option>
          <option value="green">Green</option>
          <option value="blue">Blue</option>
          <option value="purple">Purple</option>
          <option value="pink">Pink</option>
          <option value="blackwhite">Black/White</option>
        </select>
        <input type="time" name="entry_time"></input>
        <input type="time" name="exit_time"></input>
        <button id="input_data_button" onclick="">Add Location Data</button>
        <section>
          <p>Input location data via file upload</p>
          <input type="file" id="input_data_button"></input>
          <input type="submit"></input>
        </section>
      </aside>
    </section>
    
    
  </body>
</html>
