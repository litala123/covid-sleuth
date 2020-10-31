<!DOCTYPE html>
<html lang="en">
  <head>
    <title>COVID-19 Tracker</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="style.css">
  </head>
  
  <body>
    <!-- heading with the title and login button -->
    <header>
      COVID-19 Tracker
      <button id="login" onclick="login()">Log in</button>
    </header>
    <section>
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
    </section>
    
    <script defer src="index.js"></script>
  </body>
</html>
