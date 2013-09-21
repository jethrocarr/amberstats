<?php
/*
	include/application/inc_servers.php
	
	Class for managing servers.
*/


class server
{
	var $id;		// ID of the server to manipulate (if any)
	var $data;



	/*
		verify_id

		Checks that the provided ID is a valid existing server

		Results
		0	Failure to find the ID
		1	Success - server exists
	*/

	function verify_id()
	{
		log_debug("servers", "Executing verify_id()");

		if ($this->id)
		{
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `apps_servers` WHERE id='". $this->id ."' LIMIT 1";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				return 1;
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_server_name

		Checks that the server name supplied has not already been taken.

		Results
		0	Failure - name in use
		1	Success - name is available
	*/

	function verify_server_name()
	{
		log_debug("servers", "Executing verify_server_name()");

		$sql_obj			= New sql_query;
		$sql_obj->string		= "SELECT id FROM `apps_servers` WHERE server_name='". $this->data["server_name"] ."' ";

		if ($this->id)
			$sql_obj->string	.= " AND id!='". $this->id ."'";

		$sql_obj->string		.= " LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}
		
		return 1;

	} // end of verify_server_name


	/*
		verify_delete_ok

		Checks if the server is safe to delete (nothing using it)
	
		Results
		0	Failure - Unsafe to delete
		1	Success - OK to delete
	*/

	function verify_delete_ok()
	{
		log_debug("servers", "Executing verify_delete_ok()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT id FROM `stats_server_versions` WHERE id_server='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}

		return 1;

	} // end of verify_delete_ok





	/*
		load_data

		Load the server's information into the $this->data array.

		Returns
		0	failure
		1	success
	*/
	function load_data()
	{
		log_debug("servers", "Executing load_data()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT * FROM apps_servers WHERE id='". $this->id ."' LIMIT 1";
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

		Create a new server based on the data in $this->data

		Results
		0	Failure
		#	Success - return ID
	*/
	function action_create()
	{
		log_debug("servers", "Executing action_create()");

		// create a new server
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `apps_servers` (server_name, server_description, regex_serverid, regex_version_minor, regex_version_major, regex_os_type, regex_os_version) VALUES ('". $this->data["server_name"] ."', '', '', '', '', '', '')";
		$sql_obj->execute();

		$this->id = $sql_obj->fetch_insert_id();

		// assign the server to the domains
		return $this->id;

	} // end of action_create




	/*
		action_update

		Update a server's details based on the data in $this->data. If no ID is provided,
		it will first call the action_create function.

		Returns
		0	failure
		#	success - returns the ID
	*/
	function action_update()
	{
		log_debug("servers", "Executing action_update()");


		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			If no ID supplied, create a new name server first
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
			Update server details
		*/

		$sql_obj->string	= "UPDATE `apps_servers` SET "
						."server_name='". $this->data["server_name"] ."', "
						."server_description='". $this->data["server_description"] ."', "
						."regex_serverid='". $this->data["regex_serverid"] ."', "
						."regex_version_minor='". $this->data["regex_version_minor"] ."', "
						."regex_version_major='". $this->data["regex_version_major"] ."', "
						."regex_os_version='". $this->data["regex_os_version"] ."', "
						."regex_os_type='". $this->data["regex_os_type"] ."' "
						."WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();



		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "servers", "An error occured when updating the server.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			if ($mode == "update")
			{
				log_write("notification", "servers", "Server type has been successfully updated.");
			}
			else
			{
				log_write("notification", "servers", "Server type successfully created.");
			}
			
			return $this->id;
		}

	} // end of action_update



	/*
		action_delete

		Deletes the server.

		Results
		0	Failure
		1	Success - deleted
	*/

	function action_delete()
	{
		log_debug("servers", "Executing action_delete()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "DELETE FROM `apps_servers` WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if (error_check())
		{
			log_write("error", "servers", "An unexpected error occured when deleting the server.");
			return 0;
		}


		return 1;

	} // end of verify_delete_ok




} // end of class:servers



?>
