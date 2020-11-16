<?php

$sql = "INSERT INTO locations (locationName, longitude, latitude) VALUES ('loc1', 12.5, 8.4)";
$dbconn->exec($sql);

$sql = "INSERT INTO locations (locationName, longitude, latitude) VALUES ('loc2', 12.5, 8.4)";
$dbconn->exec($sql);

$sql = "INSERT INTO locations (locationName, longitude, latitude) VALUES ('loc3', 12.5, 8.4)";
$dbconn->exec($sql);

$sql = "INSERT INTO locations (locationName, longitude, latitude) VALUES ('loc4', 12.5, 8.4)";
$dbconn->exec($sql);

$sql = "INSERT INTO locations (locationName, longitude, latitude) VALUES ('loc5', 12.5, 8.4)";
$dbconn->exec($sql);

$sql = "INSERT INTO locations (locationName, longitude, latitude) VALUES ('loc6', 12.5, 8.4)";
$dbconn->exec($sql);

$sql = "INSERT INTO locations_visited (locationID, rcsID, entryDate, exitDate, entryTime, exitTime) VALUES (2, 'litala', '2020-11-12', '2020-11-12', '5:00', '5:05')";
$dbconn->exec($sql);

$sql = "INSERT INTO locations_visited (locationID, rcsID, entryDate, exitDate, entryTime, exitTime) VALUES (4, 'smithj2', '2020-11-14', '2020-11-14', '7:00', '8:30')";
$dbconn->exec($sql);

?>
