<?php
/*
	include/application/inc_queue.php

	Functions for processing the queue of information provided by the API and turning it
	into useful statistics.
*/


/*
	process_queue

	Check the current incoming queue and process all items in it.

	Returns
	0	A serious issue occured
	1	Queue processed successfully (ignoring any individual record failures)
*/
function process_queue()
{
	log_write("debug", "inc_queue", "Executing process_queue()");


	/*
		If debugging QUEUE_PURGE_COLLECTED enabled, we should delete contents of all
		stats before running queue process.
	*/

	if ($GLOBALS["config"]["QUEUE_PURGE_COLLECTED"])
	{
		log_write("warning", "inc_queue", "Truncacting existing stats due to QUEUE_PURGE_COLLECTED option enabled");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "TRUNCATE TABLE `stats`";
		$sql_obj->execute();

		$sql_obj		= New sql_query;
		$sql_obj->string	= "TRUNCATE TABLE `stats_app_versions`";
		$sql_obj->execute();

		$sql_obj		= New sql_query;
		$sql_obj->string	= "TRUNCATE TABLE `stats_country`";
		$sql_obj->execute();

		$sql_obj		= New sql_query;
		$sql_obj->string	= "TRUNCATE TABLE `stats_platform_versions`";
		$sql_obj->execute();

		$sql_obj		= New sql_query;
		$sql_obj->string	= "TRUNCATE TABLE `stats_server_versions`";
		$sql_obj->execute();
	}
	



	/*
		Load rules and matching data needed for processing all the records
	*/

	// Application-Agent Matching
	$sql_obj		= New sql_query;
	$sql_obj->string	= "SELECT id_application, agent_name FROM apps_agents";
	$sql_obj->execute();
	
	if (!$sql_obj->num_rows())
	{
		log_write("error", "inc_queue", "Unable to process incoming queue, there are no applications defined yet!");
		return 0;
	}

	$sql_obj->fetch_array();

	$rules_agents = array();

	foreach ($sql_obj->data as $data_row)
	{
		$rules_agents[ $data_row["agent_name"] ] = $data_row["id_application"];
	}


	/*
		Load GeoIP DB if enabled
	*/

	if ($GLOBALS["config"]["STATS_GEOIP_LOOKUP"])
	{
		$obj_geoip_v4 = geoip_open($GLOBALS["config"]["STATS_GEOIP_COUNTRYDB_V4"], GEOIP_MEMORY_CACHE);

		if (!$obj_geoip_v4)
		{
			log_write("error", "inc_queue", "Unable to open GeoIP file \"". $GLOBALS["config"]["STATS_GEOIP_COUNTRYDB_V4"] ."\"");
			log_write("error", "inc_queue", "Disabling GeoIP for this run");

			$GLOBALS["config"]["STATS_GEOIP_LOOKUP"] = 0;
		}
		

		$obj_geoip_v6 = geoip_open($GLOBALS["config"]["STATS_GEOIP_COUNTRYDB_V6"], GEOIP_MEMORY_CACHE);

		if (!$obj_geoip_v6)
		{
			log_write("error", "inc_queue", "Unable to open GeoIP file \"". $GLOBALS["config"]["STATS_GEOIP_COUNTRYDB_V6"] ."\"");
			log_write("error", "inc_queue", "Disabling GeoIP for this run");

			$GLOBALS["config"]["STATS_GEOIP_LOOKUP"] = 0;
		}
	}



	/*
		Run through all the records
	*/

	$rows_to_delete = array();
	$rows_failed	= array();

	$obj_sql_queue			= New sql_query;
	$obj_sql_queue->string		= "SELECT id, timestamp, ipaddress, app_name, app_version, server_app, server_platform, subscription_type, subscription_id FROM stats_incoming";
	$obj_sql_queue->execute();

	if ($obj_sql_queue->num_rows())
	{
		$obj_sql_queue->fetch_array();

		foreach ($obj_sql_queue->data as $data_row)
		{
			log_write("debug", "process", "Processing entry ID ". $data_row["id"] ."");



			// 1. Match the application name to a known agent. If the application name is
			//    invalid, then there's little point continuing.

			$final_app_id = 0;

			foreach (array_keys($rules_agents) as $agent)
			{
				if ($data_row["app_name"] == $agent)
				{
					$final_app_id = $rules_agents[ $agent ];
				}
			}

			if (!$final_app_id)
			{
				log_write("debug", "process", "Failed, unable to match record to an application agent");
				$rows_failed = $data_row["id"];
				continue;
			}


			// 2. Process the application version

			$final_app_version_id = rules_find_app_version($final_app_id, $data_row["app_version"]);

			if (!$final_app_version_id)
			{
				log_write("debug", "process", "Failed, unknown problem during application version processing - input error?");
				$rows_failed = $data_row["id"];
				continue;
			}


			// 3. Process the platform(language) that the application is running on

			$final_platform_version_id = rules_find_platform_version($final_app_id, $data_row["server_platform"]);

			if (!$final_platform_version_id)
			{
				log_write("debug", "process", "Failed, unknown problem during platform version processing - input error?");
				$rows_failed[] = $data_row["id"];
				continue;
			}


			// 4. Process the web server type

			$final_server_version_id = rules_find_server_version($data_row["server_app"]);

			if (!$final_server_version_id)
			{
				log_write("debug", "process", "Failed, unknown problem during server version processing - input error?");
				$rows_failed[] = $data_row["id"];
				continue;
			}



			// 5. Process unique ID details for this particular installation.

			$final_subscription_type	= $data_row["subscription_type"];
			$final_subscription_id		= $data_row["subscription_id"];


			// 6. Process IP address. We don't really care about the IP address itself, but we will
			//    probably want to lookup the IP address against GeoIP and record the origin of the
			//    traffic to a particular region, then discard the address.
			
			if ($GLOBALS["config"]["STATS_GEOIP_LOOKUP"])
			{
				if (ip_type_detect($data_row["ipaddress"]) == 4)
				{
					$geoip_country_code = geoip_country_code_by_addr($obj_geoip_v4, $data_row["ipaddress"]);
					$geoip_country_name = geoip_country_name_by_addr($obj_geoip_v4, $data_row["ipaddress"]);
				}
				else
				{
					$geoip_country_code = geoip_country_code_by_addr_v6($obj_geoip_v6, $data_row["ipaddress"]);
					$geoip_country_name = geoip_country_name_by_addr_v6($obj_geoip_v6, $data_row["ipaddress"]);
				}

				if ($geoip_country_code == "" || $geoip_country_name == "")
				{
					$geoip_country_code = "??";
					$geoip_country_name = "Unknown";
				}
			
				$final_country_id = rules_find_country_id($geoip_country_code, $geoip_country_name);
			}
			else
			{
				$final_country_id = 0;
			}


			// 7. Timestamp.

			$final_date = date("Y-m-d", $data_row["timestamp"]);



			// Processing complete - time to create the record!
			$obj_sql_final		= New sql_query;
			$obj_sql_final->string	= "INSERT INTO stats "
							."(id_app, "
							."id_app_version, "
							."id_server_version, "
							."id_platform_version, "
							."id_country, "
							."date, "
							."subscription_type, "
							."subscription_id "
							.") VALUES ( "
							."'$final_app_id', "
							."'$final_app_version_id', "
							."'$final_server_version_id', "
							."'$final_platform_version_id', "
							."'$final_country_id', "
							."'$final_date', "
							."'$final_subscription_type', "
							."'$final_subscription_id' "
							.")";
			$obj_sql_final->execute();

			if ($obj_sql_final->fetch_insert_id())
			{
				log_write("debug", "inc_queue", "Stats record processed successfully");
			}
			else
			{
				log_write("error", "inc_queue", "A fatal error occured during stats record insert.");
			}


			// SUCCESS!
			// We have processed the record and can now delete the incoming row
			$rows_to_delete[] = $data_row["id"];
		}
	}


	/*
		Delete processed rows
	*/
	if ($GLOBALS["config"]["QUEUE_DELETE_PROCESSED"] && !empty($rows_to_delete))
	{
		$rows_to_delete = format_arraytocommastring($rows_to_delete);

		$obj_sql 		= New sql_query;
		$obj_sql->string	= "DELETE FROM stats_incoming WHERE id IN ($rows_to_delete)";
		
		if (!$obj_sql->execute())
		{
			log_write("error", "inc_queue", "An unexpected error occured whilst deleting records from the queue");
		}
	}
	else
	{
		log_write("warning", "inc_queue", "Amberstats is configured to keep processed entires inside stats_incoming table - this will lead to duplicate values being imported!");
	}

	if ($GLOBALS["config"]["QUEUE_DELETE_INVALID"] && !empty($rows_failed)) 
	{
		$rows_failed = format_arraytocommastring($rows_failed);

		$obj_sql 		= New sql_query;
		$obj_sql->string	= "DELETE FROM stats_incoming WHERE id IN ($rows_failed)";
		
		if (!$obj_sql->execute())
		{
			log_write("error", "inc_queue", "An unexpected error occured whilst deleting records from the queue");
		}
	}
	
	
} // end of process_queue



?>
