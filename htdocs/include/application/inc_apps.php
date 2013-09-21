<?php
/*
	include/application/inc_apps.php
	
	Class for managing apps.
*/


class app
{
	var $id;		// ID of the app to manipulate (if any)
	var $data;



	/*
		verify_id

		Checks that the provided ID is a valid existing app

		Results
		0	Failure to find the ID
		1	Success - app exists
	*/

	function verify_id()
	{
		log_debug("apps", "Executing verify_id()");

		if ($this->id)
		{
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `apps` WHERE id='". $this->id ."' LIMIT 1";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				return 1;
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_app_name

		Checks that the app name supplied has not already been taken.

		Results
		0	Failure - name in use
		1	Success - name is available
	*/

	function verify_app_name()
	{
		log_debug("apps", "Executing verify_app_name()");

		$sql_obj			= New sql_query;
		$sql_obj->string		= "SELECT id FROM `apps` WHERE app_name='". $this->data["app_name"] ."' ";

		if ($this->id)
			$sql_obj->string	.= " AND id!='". $this->id ."'";

		$sql_obj->string		.= " LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}
		
		return 1;

	} // end of verify_app_name


	/*
		verify_delete_ok

		Checks if the app is safe to delete (nothing using it)
	
		Results
		0	Failure - Unsafe to delete
		1	Success - OK to delete
	*/

	function verify_delete_ok()
	{
		log_debug("apps", "Executing verify_delete_ok()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT id FROM `stats` WHERE id_app='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}

		return 1;

	} // end of verify_delete_ok





	/*
		load_data

		Load the app's information into the $this->data array.

		Returns
		0	failure
		1	success
	*/
	function load_data()
	{
		log_debug("apps", "Executing load_data()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT * FROM apps WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();
			$this->data = $sql_obj->data[0];

			return 1;
		}

		// failure
		return 0;

	} // end of load_data




	/*
		action_create

		Create a new app based on the data in $this->data

		Results
		0	Failure
		#	Success - return ID
	*/
	function action_create()
	{
		log_debug("apps", "Executing action_create()");

		// create a new app
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `apps` (app_name, app_description, regex_version_minor, regex_version_major) VALUES ('". $this->data["app_name"] ."', '', '', '')";
		$sql_obj->execute();

		$this->id = $sql_obj->fetch_insert_id();


		// add an agent along with the app
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `apps_agents` (id_application, agent_name, agent_description) VALUES ('". $this->id ."', '". $this->data["app_name"] ."', '')";
		$sql_obj->execute();



		// assign the app to the domains
		return $this->id;

	} // end of action_create




	/*
		action_update

		Update a app's details based on the data in $this->data. If no ID is provided,
		it will first call the action_create function.

		Returns
		0	failure
		#	success - returns the ID
	*/
	function action_update()
	{
		log_debug("apps", "Executing action_update()");


		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			If no ID supplied, create a new name app first
		*/
		if (!$this->id)
		{
			$mode = "create";

			if (!$this->action_create())
			{
				return 0;
			}
		}
		else
		{
			$mode = "update";
		}



		/*
			Update app details
		*/

		$sql_obj->string	= "UPDATE `apps` SET "
						."app_name='". $this->data["app_name"] ."', "
						."app_description='". $this->data["app_description"] ."', "
						."id_platform='". $this->data["id_platform"] ."', "
						."regex_version_minor='". $this->data["regex_version_minor"] ."', "
						."regex_version_major='". $this->data["regex_version_major"] ."' "
						."WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();



		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "apps", "An error occured when updating the app.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			if ($mode == "update")
			{
				log_write("notification", "apps", "Application has been successfully updated.");
			}
			else
			{
				log_write("notification", "apps", "Application successfully created.");
			}
			
			return $this->id;
		}

	} // end of action_update



	/*
		action_delete

		Deletes the app.

		Results
		0	Failure
		1	Success - deleted
	*/

	function action_delete()
	{
		log_debug("apps", "Executing action_delete()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "DELETE FROM `apps` WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if (error_check())
		{
			log_write("error", "apps", "An unexpected error occured when deleting the app.");
			return 0;
		}


		return 1;

	} // end of verify_delete_ok




} // end of class:apps



?>
