<?php
/*
	admin/config-process.php
	
	Access: admin only

	Updates the system configuration.
*/


// includes
include_once("../include/config.php");
include_once("../include/amberphplib/main.php");


if (user_permissions_get("admin"))
{
	/*
		Fetch Configuration Data
	*/
	
	$data					= array();

	$data["QUEUE_DELETE_PROCESSED"]		= security_form_input_predefined("checkbox", "QUEUE_DELETE_PROCESSED", 0, "");
	$data["QUEUE_PURGE_COLLECTED"]		= security_form_input_predefined("checkbox", "QUEUE_PURGE_COLLECTED", 0, "");
	$data["QUEUE_DELETE_INVALID"]		= security_form_input_predefined("checkbox", "QUEUE_DELETE_INVALID", 0, "");
	
	$data["STATS_GEOIP_LOOKUP"]		= security_form_input_predefined("checkbox", "STATS_GEOIP_LOOKUP", 0, "");
	$data["STATS_GEOIP_COUNTRYDB_V4"]	= security_form_input_predefined("any", "STATS_GEOIP_COUNTRYDB_V4", 0, "");
	$data["STATS_GEOIP_COUNTRYDB_V6"]	= security_form_input_predefined("any", "STATS_GEOIP_COUNTRYDB_V6", 0, "");

	$data["DATEFORMAT"]			= security_form_input_predefined("any", "DATEFORMAT", 1, "");
	$data["TIMEZONE_DEFAULT"]		= security_form_input_predefined("any", "TIMEZONE_DEFAULT", 1, "");
	
	$data["PHONE_HOME"]			= security_form_input_predefined("checkbox", "PHONE_HOME", 0, "");


	if ($data["STATS_GEOIP_LOOKUP"])
	{
		if (empty($data["STATS_GEOIP_COUNTRYDB_V4"]))
		{
			log_write("error", "process", "You need to set the path to a copy of the GeoIP database");
			error_flag_field("STATS_GEOIP_COUNTRYDB_V4");
		}
		else
		{
			if (!file_exists($data["STATS_GEOIP_COUNTRYDB_V4"]))
			{
				log_write("error", "process", "Provided GeoIP file does not exist or it not readable by this process");
				error_flag_field("STATS_GEOIP_COUNTRYDB_V4");
			}
		}

		if (empty($data["STATS_GEOIP_COUNTRYDB_V6"]))
		{
			log_write("error", "process", "You need to set the path to a copy of the GeoIP database");
			error_flag_field("STATS_GEOIP_COUNTRYDB_V6");
		}
		else
		{
			if (!file_exists($data["STATS_GEOIP_COUNTRYDB_V6"]))
			{
				log_write("error", "process", "Provided GeoIP file does not exist or it not readable by this process");
				error_flag_field("STATS_GEOIP_COUNTRYDB_V6");
			}
		}

	}



	/*
		Process Data
	*/
	if ($_SESSION["error"]["message"])
	{
		$_SESSION["error"]["form"]["config"] = "failed";
		header("Location: ../index.php?page=admin/config.php");
		exit(0);
	}
	else
	{
		$_SESSION["error"] = array();

		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();

	
		/*
			Update all the config fields

			We have already loaded the data for all the fields, so simply need to go and set all the values
			based on the naming of the $data array.
		*/

		foreach (array_keys($data) as $data_key)
		{
			$sql_obj->string = "UPDATE config SET value='". $data[$data_key] ."' WHERE name='$data_key' LIMIT 1";
			$sql_obj->execute();
		}


		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "process", "An error occured whilst updating configuration, no changes have been applied.");
		}
		else
		{
			$sql_obj->trans_commit();

			log_write("notification", "process", "Configuration Updated Successfully");
		}

		header("Location: ../index.php?page=admin/config.php");
		exit(0);


	} // if valid data input
	
	
} // end of "is user logged in?"
else
{
	// user does not have permissions to access this page.
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
