<?php
/*
	apps/edit-process.php

	access:
		admin

	Updates or creates a new application.
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

	$obj_app		= New app;
	$obj_app->id		= security_form_input_predefined("int", "id_app", 0, "");


	if ($obj_app->id)
	{
		// editing an existing application
		if (!$obj_app->verify_id())
		{
			log_write("error", "process", "The application you have attempted to edit - ". $obj_app->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_app->load_data();
		}
	}

	// basic fields
	$obj_app->data["app_name"]			= security_form_input_predefined("any", "app_name", 1, "A name must be assigned to this application.");
	$obj_app->data["app_description"]		= security_form_input_predefined("any", "app_description", 0, "");
	$obj_app->data["id_platform"]			= security_form_input_predefined("int", "id_platform", 1, "A platform must be created and assigned to this application.");
	$obj_app->data["regex_version_minor"]		= security_form_input_predefined("pcre", "regex_version_minor", 0, "");
	$obj_app->data["regex_version_major"]		= security_form_input_predefined("pcre", "regex_version_major", 0, "");


	/*
		Verify Data
	*/

	// ensure the app name is unique
	if (!$obj_app->verify_app_name())
	{
		log_write("error", "process", "The requested application name already exists.");

		error_flag_field("app_name");
	}

	// verify the ID of the platform
	$obj_platform		= New platform;
	$obj_platform->id	= $obj_app->data["id_platform"];

	if (!$obj_platform->verify_id())
	{
		log_write("error", "process", "The selected platform does not exist! Perhaps it has just recently been removed?");
		error_flag_field("id_platform");
	}


	/*
		Process Data
	*/

	if (error_check())
	{
		if ($obj_app->id)
		{
			$_SESSION["error"]["form"]["app_edit"]	= "failed";
			header("Location: ../index.php?page=apps/view.php&id=". $obj_app->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["app_edit"]	= "failed";
			header("Location: ../index.php?page=apps/add.php");
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

		$obj_app->action_update();


		/*
			Return
		*/

		header("Location: ../index.php?page=apps/view.php&id=". $obj_app->id ."");
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
