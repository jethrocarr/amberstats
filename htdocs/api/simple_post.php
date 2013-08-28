<?php
/*
	api/simple_post.php

	Simple API that takes information from AmberStats agents supplied via a POST
	and inserts it into the incoming queue.

	Returns HTTP error code "400 Bad Request" if an issue occurs
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");



/*
	Fetch Data

	We do a few checks for legacy fields, but otherwise it's just a simple grab of detail from
	the collecting agents.
*/
$data["app_name"]			= @security_form_input_predefined("any", "app_name", 1, "");
$data["app_version"]			= @security_form_input_predefined("any", "app_version", 1, "");
$data["server_app"]			= @security_form_input_predefined("any", "server_app", 1, "");
$data["subscription_id"]		= @security_form_input_predefined("any", "subscription_id", 1, "");


if (!empty($_POST["subscription_support"]))
{
	// legacy
	$data["subscription_type"]	= @security_form_input_predefined("any", "subscription_support", 1, "");
}
else
{
	$data["subscription_type"]	= @security_form_input_predefined("any", "subscription_type", 1, "");
}

if (!empty($_POST["server_php"]))
{
	// legacy
	$data["server_platform"]	= @security_form_input_predefined("any", "server_php", 1, "");
}
else
{
	$data["server_platform"]	= @security_form_input_predefined("any", "server_platform", 1, "");
}



/*
	Error Handling
*/

if (error_check())
{	
	/*
		Invalid/Insufficent Information Provided
	
		Return HTTP error code "400 Bad Request"
	*/
	header(http_header_lookup("400"));
	exit(0);
}
else
{
	/*
		Valid Information Provided

		Now we need to enter the data into the tracking database
	*/

	$sql_obj		= New sql_query;
	$sql_obj->string	= "INSERT INTO stats_incoming ("
					."timestamp, "
					."ipaddress, "
					."app_name, "
					."app_version, "
					."server_app, "
					."server_platform, "
					."subscription_type, "
					."subscription_id"
					.") VALUES ("
					."'". mktime() ."', "
					."'". $_SERVER["REMOTE_ADDR"] ."', "
					."'". $data["app_name"] ."', "
					."'". $data["app_version"] ."', "
					."'". $data["server_app"] ."', "
					."'". $data["server_platform"] ."', "
					."'". $data["subscription_type"] ."', "
					."'". $data["subscription_id"] ."' "
					.")";
	$sql_obj->execute();


	if (error_check())
	{
		header(http_header_lookup("500"));

		foreach ($_SESSION["error"]["message"] as $errormsg)
		{
			print "$errormsg\n";
		}

		error_clear();
	}
	else
	{
		header(http_header_lookup("200"));
	}

	exit(0);

}


?>
