<?php
/*
	servers/edit-process.php

	access:
		admin

	Updates or creates a new server entry.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('admin'))
{
	/*
		Form Input
	*/

	$obj_server		= New server;
	$obj_server->id		= security_form_input_predefined("int", "id_server", 0, "");


	if ($obj_server->id)
	{
		// we are editing an existing server

		if (!$obj_server->verify_id())
		{
			log_write("error", "process", "The server you have attempted to edit - ". $obj_server->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_server->load_data();
		}
	}

	// basic fields
	$obj_server->data["server_name"]			= security_form_input_predefined("any", "server_name", 1, "A name must be assigned to this server.");
	$obj_server->data["server_description"]			= security_form_input_predefined("any", "server_description", 0, "");

	$obj_server->data["regex_serverid"]			= security_form_input_predefined("pcre", "regex_serverid", 1, "A regex match to identify this server type is vital!");

	$obj_server->data["regex_os_type"]			= security_form_input_predefined("pcre", "regex_os_type", 0, "");
	$obj_server->data["regex_os_version"]			= security_form_input_predefined("pcre", "regex_os_version", 0, "");

	$obj_server->data["regex_version_minor"]		= security_form_input_predefined("pcre", "regex_version_minor", 0, "");
	$obj_server->data["regex_version_major"]		= security_form_input_predefined("pcre", "regex_version_major", 0, "");


	/*
		Verify Data
	*/

	// ensure the server name is unique
	if (!$obj_server->verify_server_name())
	{
		log_write("error", "process", "The requested server already exists!");

		error_flag_field("server_name");
	}



	/*
		Process Data
	*/

	if (error_check())
	{
		if ($obj_server->id)
		{
			$_SESSION["error"]["form"]["server_edit"]	= "failed";
			header("Location: ../index.php?page=servers/view.php&id=". $obj_server->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["server_add"]	= "failed";
			header("Location: ../index.php?page=servers/add.php");
		}

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();


		/*
			Update
		*/

		$obj_server->action_update();


		/*
			Return
		*/

		header("Location: ../index.php?page=servers/view.php&id=". $obj_server->id ."");
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
