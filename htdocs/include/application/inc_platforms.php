<?php
/*
	include/application/inc_platforms.php
	
	Class for managing platforms.
*/


class platform
{
	var $id;		// ID of the platform to manipulate (if any)
	var $data;



	/*
		verify_id

		Checks that the provided ID is a valid existing platform

		Results
		0	Failure to find the ID
		1	Success - platform exists
	*/

	function verify_id()
	{
		log_debug("platforms", "Executing verify_id()");

		if ($this->id)
		{
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `apps_platform` WHERE id='". $this->id ."' LIMIT 1";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				return 1;
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_platform_name

		Checks that the platform name supplied has not already been taken.

		Results
		0	Failure - name in use
		1	Success - name is available
	*/

	function verify_platform_name()
	{
		log_debug("platforms", "Executing verify_platform_name()");

		$sql_obj			= New sql_query;
		$sql_obj->string		= "SELECT id FROM `apps_platform` WHERE platform_name='". $this->data["platform_name"] ."' ";

		if ($this->id)
			$sql_obj->string	.= " AND id!='". $this->id ."'";

		$sql_obj->string		.= " LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}
		
		return 1;

	} // end of verify_platform_name


	/*
		verify_delete_ok

		Checks if the platform is safe to delete (nothing using it)
	
		Results
		0	Failure - Unsafe to delete
		1	Success - OK to delete
	*/

	function verify_delete_ok()
	{
		log_debug("platforms", "Executing verify_delete_ok()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT id FROM `apps` WHERE id_platform='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}

		return 1;

	} // end of verify_delete_ok





	/*
		load_data

		Load the platform's information into the $this->data array.

		Returns
		0	failure
		1	success
	*/
	function load_data()
	{
		log_debug("platforms", "Executing load_data()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT * FROM apps_platform WHERE id='". $this->id ."' LIMIT 1";
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

		Create a new platform based on the data in $this->data

		Results
		0	Failure
		#	Success - return ID
	*/
	function action_create()
	{
		log_debug("platforms", "Executing action_create()");

		// create a new platform
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `apps_platform` (platform_name, platform_description, regex_version_minor, regex_version_major) VALUES ('". $this->data["platform_name"] ."', '', '', '')";
		$sql_obj->execute();

		$this->id = $sql_obj->fetch_insert_id();

		// assign the platform to the domains
		return $this->id;

	} // end of action_create




	/*
		action_update

		Update a platform's details based on the data in $this->data. If no ID is provided,
		it will first call the action_create function.

		Returns
		0	failure
		#	success - returns the ID
	*/
	function action_update()
	{
		log_debug("platforms", "Executing action_update()");


		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			If no ID supplied, create a new name platform first
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
			Update platform details
		*/

		$sql_obj->string	= "UPDATE `apps_platform` SET "
						."platform_name='". $this->data["platform_name"] ."', "
						."platform_description='". $this->data["platform_description"] ."', "
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

			log_write("error", "platforms", "An error occured when updating the platform.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			if ($mode == "update")
			{
				log_write("notification", "platforms", "Platform has been successfully updated.");
			}
			else
			{
				log_write("notification", "platforms", "Platform successfully created.");
			}
			
			return $this->id;
		}

	} // end of action_update



	/*
		action_delete

		Deletes the platform.

		Results
		0	Failure
		1	Success - deleted
	*/

	function action_delete()
	{
		log_debug("platforms", "Executing action_delete()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "DELETE FROM `apps_platform` WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if (error_check())
		{
			log_write("error", "platforms", "An unexpected error occured when deleting the platform.");
			return 0;
		}


		return 1;

	} // end of verify_delete_ok




} // end of class:platforms



?>
