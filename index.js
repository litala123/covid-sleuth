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
    $("#loc_list").append("<li>" + locs.arr[i] + "</li>");
  }
  
  document.getElementById("location_button").style.backgroundColor = "#ffaaaa";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
}

function hotspot_display() {
  // alert("Hotspots will be shown below from this button.");
  
  $("#loc_list").empty();
  
  var locs = JSON.parse($("#hotspots_from_db").text());
  // console.log("H: " + locs.arr);
  for(var i = 0; i < locs.arr.length; i++) {
    $("#loc_list").append("<li>" + locs.arr[i] + "</li>");
  }
  
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#ffaaaa";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
}

function visited_display() {
  // alert("Your visited locations will be shown below from this button.");
  
  $("#loc_list").empty();
  
  var locs = JSON.parse($("#visited_from_db").text());
  // console.log("V: " + locs.l_arr);
  // console.log("V: " + locs.t_arr);
  for(var i = 0; i < locs.l_arr.length; i++) {
    $("#loc_list").append("<li>" + locs.l_arr[i] + " ("  + locs.t_arr[i][0] + "-" + locs.t_arr[i][1] + ")</li>");
  }
  
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#ffaaaa";
}

// Initialize and add the map
function initMap() {
  // The location of Uluru
  const rpi = { lat: 42.73, lng: -73.6775 };
  // The map, centered at Uluru
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: rpi,
  });
  // The marker, positioned at Uluru
  const marker = new google.maps.Marker({
    position: rpi,
    map: map,
  });
}
