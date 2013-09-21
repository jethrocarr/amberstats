<?php
/*
	apps/delete-process.php

	access:
		admin

	Deletes an unwanted app.
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
	$obj_app->id	= security_form_input_predefined("int", "id_app", 0, "");


	// for error return if needed
	@security_form_input_predefined("any", "app_name", 1, "");
	@security_form_input_predefined("any", "app_description", 0, "");

	// confirm deletion
	@security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");




	/*
		Verify Data
	*/

	if (!$obj_app->verify_id())
	{
		log_write("error", "process", "The app you have attempted to delete - ". $obj_app->id ." - does not exist in this system.");
	}

	if (!$obj_app->verify_delete_ok())
	{
		log_write("error", "process", "The app you have attempted to delete - ". $obj_app->id ." - is actively used by stats and cannot be deleted.");
	}



	/*
		Process Data
	*/

	if (error_check())
	{
		$_SESSION["error"]["form"]["app_delete"]	= "failed";
		header("Location: ../index.php?page=apps/delete.php&id=". $obj_app->id ."");

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();



		/*
			Delete
		*/

		$obj_app->action_delete();



		/*
			Return
		*/

		header("Location: ../index.php?page=apps/apps.php");
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
