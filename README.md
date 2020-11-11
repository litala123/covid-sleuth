# COVID-19 Tracker
#### Websys Final Project - 5p00k
---
##### Initial commit - Andrew L'Italien
The homepage is created. There is a header that states in the top left, "COVID-19 Tracker." A login button is in the top right corner of the screen. Clicking this button alerts the user that it is not functional.

Below the header are the two primary elements: the location sidebar and the map element. The sidebar has three buttons: a button that will display all locations, one for all hotspot locations, and one for all places that the user has visited. Like the login button, clicking on them sends an alert to the user that they are not functional yet, although they do change to a light red to highlight which button has been pressed most-recently. There is a list under the buttons that has some placeholder location elements. The 3 buttons in the sidebar, once functional, will update this list.

The map element is planned to take advantage of the Google Maps API, but currently has only a placeholder image of the RPI campus.

##### RPI-CAS Login - Andrew L'Italien
The login button has been linked to a page that will allow users to log into the site with their RCS-ID. This is done with PHP and the library phpCAS. Upon logging in, the login button will become the logout button, which will log out the user from the site. The RCS-ID of the user will be displayed in the header to the left of the logout button.
Note: setting server certificates only seems to work when using absolute filepaths. The filepaths need to be relative to work for everyone, so the site currently has certificate validation turned off. This should be fixed.


##### Right sidebar and proportional sizes - Andrew L'Italien
The right sidebar is added. This sidebar will be dedicated to inputting data to the database. It is 20% the width of the viewport, just as the life sidebar. The top of the new sidebar has a few different input options: a select option element for selecting a location (location options will be added once the database is implemented), two time input boxes (one for time entering the location and one for leaving), and a button to add the data to the database. The button currently does nothing.

Much below the location input is an area dedicated to uploading location data with a file. There is a paragraph element that tells the user "Input location data via file upload." There is then a file upload button and a submit button for that file upload. Submitting currently does nothing.

Not only for the right sidebar but the entire site, all style information in the stylesheet with pixel measurements have been changed to use vh and vw units, which are proportional to the size of the viewport. This allows the window to be resized without ruining the structure of the site. This also fixes a minor bug that caused the site to not fit perfectly on the screen and require slight scrolling.

##### Created the database, left sidebar updates with location data - Andrew L'Italien
A file called "initDB.php" was created that is included in the PHP script at the top of the homepage. This file creates a database called "covid_db" if there is not already a database with that name. It will then create 3 tables (if they don't already exist) in that database: "locations" (will store all locations on RPI), "hotspots" (will store the IDs of locations that are hotspots), and "locations_visited" (will store the locations that each user has visited).

The "initDB.php" file also checks if the "locations" table is empty. If it is empty, it will fill it with locations at RPI. The SQL querying is done in a file ("fillLocations.php") that is included in the "initDB.php" file. The locations currently in the "fillLocations.php" file are placeholders. There are also temporary INSERTs in this file for the "locations_visited" table for testing.

The homepage has an element with #locs_from_db whose text is filled with the locations stored in the "locations" table. These are taken from the database with an SQL query and put into a string formatted as a JSON object with an array of the location which will get echoed into the HTML element. The element has "display" set to "none" to keep the homepage layout uninterrupted. Clicking on the "Locations" button on the left sidebar will clear the list of locations in the left sidebar and fill it with the locations taken from the database. This is done by parsing the JSON-formatted string to get an array of locations and appending &lt;li&gt;s with those locations into the list.
Similar elements have been added for the "hotspots" and "locations_visited tables" (with IDs #hotspots_from_db and #visited_from_db, respectively). The element for hotspots pulls locations from the "locations" table based on what locationIDs are stored in the "hotspots" table. The locations-visited element pulls all rows from the "locations_visited" table that correspond to the user that is currently logged into the website. The locations and the times the user was at those locations will be displayed in this list. Not being logged-in results in an empty list for locations visited.
Upon first loading the page, all locations will be listed.

In the right sidebar, the input for selecting a location now pulls its options from the database. All locations are pulled from the locations table, and each option is created by echoing an &lt;option&gt; tag for each location.

Minor changes:
- The "Places You Visited" button now says, "Locations Visited" instead.
- The "time" inputs have been changed to "datetime-local" inputs so that both date and time can be inputted instead of just the time.
- The location list no longer uses bullet points.
