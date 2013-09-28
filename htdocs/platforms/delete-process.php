<?php
/*
	platforms/delete-process.php

	access:
		admin

	Deletes an unwanted platform.
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


	// for error return if needed
	@security_form_input_predefined("any", "platform_name", 1, "");
	@security_form_input_predefined("any", "platform_description", 0, "");

	// confirm deletion
	@security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");




	/*
		Verify Data
	*/

	if (!$obj_platform->verify_id())
	{
		log_write("error", "process", "The platform you have attempted to delete - ". $obj_platform->id ." - does not exist in this system.");
	}

	if (!$obj_platform->verify_delete_ok())
	{
		log_write("error", "process", "The platform you have attempted to delete - ". $obj_platform->id ." - is actively used by applications and cannot be deleted.");
	}



	/*
		Process Data
	*/

	if (error_check())
	{
		$_SESSION["error"]["form"]["platform_delete"]	= "failed";
		header("Location: ../index.php?page=platforms/delete.php&id=". $obj_platform->id ."");

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();



		/*
			Delete
		*/

		$obj_platform->action_delete();



		/*
			Return
		*/

		header("Location: ../index.php?page=platforms/platforms.php");
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
