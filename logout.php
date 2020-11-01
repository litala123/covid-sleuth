<?php

// phpCAS file
include_once("res/phpCAS-1.3.6/CAS.php");

phpCAS::setDebug(); 
phpCAS::setVerbose(true);
// create the client      authentication site, port (https), cas folder
phpCAS::client(CAS_VERSION_2_0, "cas-auth.rpi.edu", 443, "/cas/");
// set certifications
// phpCAS::setCasServerCACert("res/cacert.pem");
// line below keeps it from checking certifications
phpCAS::setNoCasServerValidation();

if(phpCAS::isAuthenticated())
  phpCAS::logout();
else
  header("location: index.php");

?>