// calls this on first load
location_display();

function login()
{
  location.href = "login.php";
}

function logout()
{
  location.href = "logout.php";
}

function location_display() {
  // alert("Locations will be shown below from this button.");
  
  $("#loc_list").empty();
  
  var locs = JSON.parse($("#locs_from_db").text());
  // console.log("L: " + locs.arr);
  for(var i = 0; i < locs.arr.length; i++) {
    $("#loc_list").append("<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "</li>");
  }
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
  
  document.getElementById("location_button").style.backgroundColor = "#ff6666";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
}

function hotspot_display() {
  // alert("Hotspots will be shown below from this button.");
  
  $("#loc_list").empty();
  
  var locs = JSON.parse($("#hotspots_from_db").text());
  // console.log("H: " + locs.arr);
  for(var i = 0; i < locs.arr.length; i++) {
    $("#loc_list").append("<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "</li>");
  }
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#ff6666";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
}

function visited_display() {
  // alert("Your visited locations will be shown below from this button.");
  
  $("#loc_list").empty();
  
  var locs = JSON.parse($("#visited_from_db").text());
  // console.log("V: " + locs.l_arr);
  // console.log("V: " + locs.t_arr);
  for(var i = 0; i < locs.l_arr.length; i++) {
    // ex. turn 2020-11-14 into 2020/11/14
    // does this for entry an exit dates
    locs.t_arr[i][0] = locs.t_arr[i][0].replace("-", "/");
    locs.t_arr[i][0] = locs.t_arr[i][0].replace("-", "/");
    locs.t_arr[i][1] = locs.t_arr[i][1].replace("-", "/");
    locs.t_arr[i][1] = locs.t_arr[i][1].replace("-", "/");
    
    if(locs.t_arr[i][0].substring(0, 10) == locs.t_arr[i][1].substring(0, 10))
    {
      locstr = "<li id=" + locs.l_arr[i][1].toString() + locs.l_arr[i][2].toString() + ">" + locs.l_arr[i][0] + "<br><div class=loc_time>("  + locs.t_arr[i][0].substring(5) + "–" + locs.t_arr[i][1].substring(12) + ")</div></li>"
      //alert(locstr);
    }
    else
    {
      locstr = "<li id=" + locs.l_arr[i][1].toString() + locs.l_arr[i][2].toString() + ">" + locs.l_arr[i][0] + "<br><div class=loc_time>("  + locs.t_arr[i][0].substring(5) + " – " + locs.t_arr[i][1].substring(5) + ")</div></li>"
      //alert(locstr);
    }
    $("#loc_list").append(locstr);
  }
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
  
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#ff6666";
  
  
}

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
