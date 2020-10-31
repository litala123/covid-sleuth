function login() {
  alert("Login functionality hasn't been added yet! Try again later.");
}

function location_display() {
  alert("Locations will be shown below from this button.");
  
  document.getElementById("location_button").style.backgroundColor = "#ffaaaa";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
}

function hotspot_display() {
  alert("Hotspots will be shown below from this button.");
  
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#ffaaaa";
  document.getElementById("visited_button").style.backgroundColor = "#e7e7e7";
}

function visited_display() {
  alert("Your visited locations will be shown below from this button.");
  
  document.getElementById("location_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("hotspot_button").style.backgroundColor = "#e7e7e7";
  document.getElementById("visited_button").style.backgroundColor = "#ffaaaa";
}
