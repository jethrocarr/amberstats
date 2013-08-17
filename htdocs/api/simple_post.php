<?php
/*
	api/opensource/amberdms_phone_home.php

	Backend form for recieving phone home messages from Amberdms
	products. This is used to track market share and other
	statistical information.

	Returns HTTP error code "400 Bad Request" if an issue occurs
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");



/*
	Fetch Data
*/
$data["app_name"]			= security_form_input_predefined("any", "app_name", 1, "");
$data["app_version"]			= security_form_input_predefined("any", "app_version", 1, "");
$data["server_app"]			= security_form_input_predefined("any", "server_app", 1, "");
$data["server_php"]			= security_form_input_predefined("any", "server_php", 1, "");
$data["subscription_support"]		= security_form_input_predefined("any", "subscription_support", 1, "");
$data["subscription_id"]		= security_form_input_predefined("any", "subscription_id", 1, "");


/*
	Error Handling
*/

// check that it's a valid application
if ($data["app_name"] != "Amberdms Billing System")
{
	log_write("error", "process", "Invalid application submitted");
}


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
	$sql_obj->string	= "INSERT INTO product_phone_home ("
					."timestamp, "
					."ipaddress, "
					."app_name, "
					."app_version, "
					."server_app, "
					."server_php, "
					."subscription_support, "
					."subscription_id"
					.") VALUES ("
					."'". mktime() ."', "
					."'". $_SERVER["REMOTE_ADDR"] ."', "
					."'". $data["app_name"] ."', "
					."'". $data["app_version"] ."', "
					."'". $data["server_app"] ."', "
					."'". $data["server_php"] ."', "
					."'". $data["subscription_support"] ."', "
					."'". $data["subscription_id"] ."' "
					.")";
	$sql_obj->execute();

	// return success!
	header(http_header_lookup("200"));
	exit(0);
}


?>
