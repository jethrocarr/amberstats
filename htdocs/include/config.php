<?php
/*
	MASTER CONFIGURATION FILE

	This file contains key application configuration options and values for
	developers rather than users/admins.

	DO NOT MAKE ANY CHANGES TO THIS FILE, INSTEAD PLEASE MAKE ANY ADJUSTMENTS
	TO "config-settings.php" TO ENSURE CORRECT APPLICATION OPERATION.

	If config-settings.php does not exist, then you need to copy sample_config.php
	into it's place.
*/



$GLOBALS["config"] = array();



/*
	Define Application Name & Versions
*/

// define the application details
$GLOBALS["config"]["app_name"]			= "AmberStats";
$GLOBALS["config"]["app_version"]		= "0.0.1";

// define the schema version required
$GLOBALS["config"]["schema_version"]		= "20130817";





/*
	Session Management
*/

// Initate session variables
if (!empty($_SERVER['SERVER_NAME']))
{
	// proper session variables
	session_name("amberstats");
	session_start();
}
else
{
	// trick to make logging and error system work correctly for scripts.
	$GLOBALS["_SESSION"]	= array();
	$_SESSION["mode"]	= "cli";
	$_SESSION["user"]["id"] = 0;
}


/*
	Inherit User Configuration
*/
include("config-settings.php");



/*
	Silence warnings to avoid unexpected errors appearing on newer PHP versions
	than what the developers tested with, unless running in dev mode (in which
	case we want to see all the configured errors).
*/
if (empty($_SESSION["user"]["debug"]))
{
	ini_set("display_errors", 0);
}



/*
	Connect to Databases
*/
include("database.php");

?>
