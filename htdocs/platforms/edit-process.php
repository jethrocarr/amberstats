<?php
/*
	platforms/edit-process.php

	access:
		admin

	Updates or creates a new platform entry.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('stats_config'))
{
	/*
		Form Input
	*/

	$obj_platform		= New platform;
	$obj_platform->id	= security_form_input_predefined("int", "id_platform", 0, "");


	if ($obj_platform->id)
	{
		// we are editing an existing platform

		if (!$obj_platform->verify_id())
		{
			log_write("error", "process", "The platform you have attempted to edit - ". $obj_platform->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_platform->load_data();
		}
	}

	// basic fields
	$obj_platform->data["platform_name"]			= security_form_input_predefined("any", "platform_name", 1, "A name must be assigned to this platform.");
	$obj_platform->data["platform_description"]		= security_form_input_predefined("any", "platform_description", 0, "");
	$obj_platform->data["regex_version_minor"]		= security_form_input_predefined("any", "regex_version_minor", 0, "");
	$obj_platform->data["regex_version_major"]		= security_form_input_predefined("any", "regex_version_major", 0, "");


	/*
		Verify Data
	*/

	// ensure the platform name is unique
	if (!$obj_platform->verify_platform_name())
	{
		log_write("error", "process", "The requested platform already exists!");

		error_flag_field("platform_name");
	}



	/*
		Process Data
	*/

	if (error_check())
	{
		if ($obj_platform->id)
		{
			$_SESSION["error"]["form"]["platform_edit"]	= "failed";
			header("Location: ../index.php?page=platforms/view.php&id=". $obj_platform->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["platform_edit"]	= "failed";
			header("Location: ../index.php?page=platforms/add.php");
		}

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();


		/*
			Update name server
		*/

		$obj_platform->action_update();


		/*
			Return
		*/

		header("Location: ../index.php?page=platforms/view.php&id=". $obj_platform->id ."");
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
