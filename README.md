# COVID-19 Tracker
#### Websys Final Project - 5p00k
---
##### Initial commit - Andrew L'Italien
The homepage is created. There is a header that states in the top left, "COVID-19 Tracker." A login button is in the top right corner of the screen. Clicking this button alerts the user that it is not functional.

Below the header are the two primary elements: the location sidebar and the map element. The sidebar has three buttons: a button that will display all locations, one for all hotspot locations, and one for all places that the user has visited. Like the login button, clicking on them sends an alert to the user that they are not functional yet, although they do change to a light red to highlight which button has been pressed most-recently. There is a list under the buttons that has some placeholder location elements. The 3 buttons in the sidebar, once functional, will update this list.

The map element is planned to take advantage of the Google Maps API, but currently has only a placeholder image of the RPI campus.

##### Right sidebar and proportional sizes - Andrew L'Italien
The right sidebar is added. This sidebar will be dedicated to inputting data to the database. It is 20% the width of the viewport, just as the life sidebar. The top of the new sidebar has a few different input options: a select option element for selecting a location (location options will be added once the database is implemented), two time input boxes (one for time entering the location and one for leaving), and a button to add the data to the database. The button currently does nothing.

Much below the location input is an area dedicated to uploading location data with a file. There is a paragraph element that tells the user "Input location data via file upload." There is then a file upload button and a submit button for that file upload. Submitting currently does nothing.

Not only for the right sidebar but the entire site, all style information in the stylesheet with pixel measurements have been changed to use vh and vw units, which are proportional to the size of the viewport. This allows the window to be resized without ruining the structure of the site. This also fixes a minor bug that caused the site to not fit perfectly on the screen and require slight scrolling.
