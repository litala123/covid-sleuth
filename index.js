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
  document.getElementById("location_button").style.backgroundColor = "#ff6666";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
  
  $("#loc_list").empty();
  
  var locs = JSON.parse($("#locs_from_db").text());
  for(var i = 0; i < locs.arr.length; i++) {
    $("#loc_list").append("<li id=" + locs.arr[i][1].toString() + locs.arr[i][2].toString() + ">" + locs.arr[i][0] + "</li>");
  }
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
}

function hotspot_display() {
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#ff6666";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
  
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
}

function visited_display() {
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#ff6666";
  
  $("#loc_list").empty();
  
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
  $("li").click( function() {
    coords = $(this).attr('id').split("-");
    changeMap(parseFloat(coords[0]), -1 * parseFloat(coords[1]), 20);
  });
}

$("#covid_btn").click( function() {
  $("#covid_form").append("<button name=\"covid_sure\" id=\"covid_sure\">Yes</button>")
  $("#covid_sure").click( function() {
    alert("All people at risk are being notified.");
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
