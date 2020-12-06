// calls this on first load
location_display();

// pulsing animation for the alert area, if it exists
$("#alert_msg").animate({borderWidth: "5px", left: "-=10px", top: "-=10px", padding: "+=5px"}, 200);
$("#alert_msg").animate({borderWidth: "0px", left: "+=10px", top: "+=10px", padding: "-=5px"}, 5);
$("#alert_msg").animate({borderWidth: "5px", left: "-=10px", top: "-=10px", padding: "+=5px"}, 300);
$("#alert_msg").animate({borderWidth: "0px", left: "+=10px", top: "+=10px", padding: "-=5px"}, 0);

// go to the login page
function login() {
  location.href = "login.php";
}

// go to the logout page
function logout() {
  location.href = "logout.php";
}

// show list of locations and highlight "Locations" button
function location_display() {
  // makes "Locations" button the only one highlighted in the left sidebar
  $("#location_button").css("background-color", "ff6666");
  $("#hotspot_button").css("background-color", "e7e7e7");
  $("#visited_button").css("background-color", "e7e7e7");
  
  // clear the list in the left sidebar
  $("#loc_list").empty();
  
  // create the list of locations
  var locs = JSON.parse($("#locs_from_db").text());
  for(var i = 0; i < locs.arr.length; i++) {
    $("#loc_list").append("<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "</li>");
  }
  
  // go to location on map when location in left sidebar is clicked
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
}

// show list of hotspots and highlight "Hotspots" button
function hotspot_display() {
  // makes "Hotspots" button the only one highlighted in the left sidebar
  $("#location_button").css("background-color", "e7e7e7");
  $("#hotspot_button").css("background-color", "ff6666");
  $("#visited_button").css("background-color", "e7e7e7");
  
  // clear the list in the left sidebar
  $("#loc_list").empty();
  
  // create the list of hotspots
  var locs = JSON.parse($("#hotspots_from_db").text());
  for(var i = 0; i < locs.arr.length; i++) {
    $("#loc_list").append("<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "</li>");
  }
  
  // go to location on map when location in left sidebar is clicked
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
}

// show list of visited locations and highlight "Locations Visited" button
function visited_display() {
  // makes "Locations Visited" button the only one highlighted in the left sidebar
  $("#location_button").css("background-color", "e7e7e7");
  $("#hotspot_button").css("background-color", "e7e7e7");
  $("#visited_button").css("background-color", "ff6666");
  
  // clear the list in the left sidebar
  $("#loc_list").empty();
  
  // create the list of locations visited
  var locs = JSON.parse($("#visited_from_db").text());
  for(var i = 0; i < locs.arr.length; i++) {
    // ex. turn 2020-11-14 into 2020/11/14
    // does this for entry and exit dates
    locs.arr[i][3] = locs.arr[i][3].replace("-", "/");
    locs.arr[i][3] = locs.arr[i][3].replace("-", "/");
    locs.arr[i][4] = locs.arr[i][4].replace("-", "/");
    locs.arr[i][4] = locs.arr[i][4].replace("-", "/");
    
    if(locs.arr[i][3].substring(0, 10) == locs.arr[i][4].substring(0, 10))
    {
      locstr = "<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "<br><div class=loc_time>("  + locs.arr[i][3].substring(5) + "–" + locs.arr[i][4].substring(12) + ")</div></li>"
    }
    else
    {
      locstr = "<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "<br><div class=loc_time>("  + locs.arr[i][3].substring(5) + " – " + locs.arr[i][4].substring(5) + ")</div></li>"
    }
    $("#loc_list").append(locstr);
  }
  
  // go to location on map when location in left sidebar is clicked
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
}

// make the "Confirm" button appear when the "I have COVID-19" button is clicked
$("#covid_btn").click( function() {
  $("#covid_sure").css("visibility", "visible");
});

// create a popup to show users how to use Google Takeout to get their location history
$("#takeout_info").click( function() {
  $("#takeout_popup_outer").css("display", "block");
  $("#takeout_popup").css("display", "block");
  $("#popup_x").css("display", "block");
  $("#popup_x").click( function() {
    $("#takeout_popup_outer").css("display", "none");
    $("#takeout_popup").css("display", "none");
    $("#popup_x").css("display", "none");
  });
});

// Initialize and add the map
function initMap() {
  // The location of RPI
  const rpi = { lat: 42.73, lng: -73.6775 };
  // The map, centered at RPi
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: rpi,
  });
  // The marker, positioned at RPI
  const marker = new google.maps.Marker({
    position: rpi,
    map: map,
  });
}

// Initialize and add the map
function changeMap(la, lo, z) {
  // The location of RPI
  const loc = { lat: la, lng: lo };
  // The map, centered at RPi
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: z,
    center: loc,
  });
  // The marker, positioned at RPI
  const marker = new google.maps.Marker({
    position: loc,
    map: map,
  });
}
