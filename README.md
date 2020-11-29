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

##### Input form adds location info to database - Andrew L'Italien
The input boxes in the top of the right sidebar can now be used to add location data to the database. In order to upload data, a user must be logged in, all boxes must be filled in, and the entry date and time cannot be later than the exit date and time. If any of these requirements are not done, the user will receive an alert telling them. If the data is successfully added, the user will receive an alert saying so. Hitting the "Add Location" button posts the selected location and inputted dates and times and will add them to the locations_visited table with the user's RCS-ID. No repeat entries are allowed (info will not be added to the database if there is already a row with the same user, location, dates, and times).

The inputs for dates and times used to be 2 inputs of type="datetime-local" for entry and exit information, but that type of input is not supported in Firefox on the desktop. Both of those inputs were split into 2 separate input elements for date and time for browser compatibility.

##### UI mini overhaul, RPI locations, extra map usage - Andrew L'Italien
The header is now red. Buttons have rounded corners, and the red/pink highlights on clicked buttons are darker. The RCS-ID now has a black border and has a gray background to keep it readable against the red. The website title and the header text now say "COVID Sleuth" instead of the generic "COVID-19 Tracker" that it had before. A logo has been placed before the header text; that logo is also used as the website icon, seen in the tab at the top of the screen.
There is a big "I have COVID-19" button in the right sidebar that currently does nothing. It is only visible when a user is logged in.

The list of locations has been updated to reflect locations actually at RPI. The list in the left sidebar is scrollable so the entrees do not extend beyond the page. Each location has a higher margin on the bottom to space them out better.
Clicking on one of these locations will bring you to that location on the map.

Minor changes:
- The longitude and latitude column orders have been switched in the locations table for convenience. Longitude was displayed first, but this changed because latitude is typically displayed before longitude, so this change makes it easier to read in the table.
- Changed mouse icon to pointer when hovering over listed locations on the left
- Changed font family to "serif"

##### Locations visited list fixed - Andrew L'Italien
The locations visited list pulled data from the database in a way that made it so no location could appear more than once in the list. The SQL query has been rewritten to fix this issue. Also, the visits are now listed by entry date/time from most recent to longest ago.
Also, the code was pretty inefficient before, making multiple SQL queries, creating two different arrays for data, and then using both of those in the JS file. The code has now been shortened to include on 1 SQL query, 1 array, and is now much more readable.

##### Users can claim they have COVID-19 - Andrew L'Italien
Upon clicking the "I have COVID-19" button, a new "Yes" button will appear below it. Upon clicking the "Yes" button, the site will email all users that have been to any location with the past 24 hours that the current user has been to within the last 24 hours. Also, all of the locations the current user has been to within the last 24 hours will be marked as hotspots.

The email is done with PHP with a Mail package installed with PEAR. The SMTP used is 'smtp.gmail.com', and 'covidsleuth.alert@gmail.com' is the email address that sends out emails to users. Using 'gmail.com' instead of 'rpi.edu' may result in some users' spam filters to mark the alerts as spam, but that is user dependent. My email filter is able to receive emails from 'gmail.com' addresses, but they don't always send right away, so I had to whitelist the accounts to prevent emails from being put on hold.

Also added more INSERT queries in "fillLocations.php" for testing.
