<?php

$servername = 'final.websys';
$username = 'root';
$password = '';
$name = 'covid_db';

try {
  // Create SQL Database if it doesn't exist already
  
  $dbconn = new PDO("mysql:host=$servername", $username, $password);
  $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $sql = "CREATE DATABASE IF NOT EXISTS $name COLLATE 'utf8_general_ci'";
  $dbconn->exec($sql);
  
  
  // Create the tables if they don't exist
  
  $dbconn->exec("USE $name");
  
  $sql = "CREATE TABLE IF NOT EXISTS locations (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    locationName VARCHAR(100) NOT NULL,
    longitude DOUBLE NOT NULL,
    latitude DOUBLE NOT NULL
  )";
  $dbconn->exec($sql);
  
  $sql = "CREATE TABLE IF NOT EXISTS hotspots (
    locationID INT(6) UNSIGNED PRIMARY KEY
  )";
  $dbconn->exec($sql);
  
  $sql = "CREATE TABLE IF NOT EXISTS locations_visited (
    id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    locationID INT(6) UNSIGNED NOT NULL,
    rcsID VARCHAR(9) NOT NULL,
    entryTime VARCHAR(20) NOT NULL,
    exitTime VARCHAR(20) NOT NULL
  )";
  $dbconn->exec($sql);
  
  
  // Add the locations to the locations table if the table is empty
  $sql = "SELECT * FROM `locations`";
  $rows = $dbconn->query($sql);
  $row = $rows->fetch();
  if($row['id'] == NULL) {
    // echo "empty - will be filled<br>";
    $sql = "ALTER TABLE locations AUTO_INCREMENT = 1";
    $dbconn->exec($sql);
    include("fillLocations.php");
  }
  else {
    // echo "not empty - locationName = " . $row['locationName'] . "<br>";
  }
    
  
} catch (PDOException $e) {
  echo 'Database initialization error - Caught exception: ',  $e->getMessage(), "<br>";
}

?>