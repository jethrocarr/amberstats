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


	// Platform Maching

	// Web Server/OS Matching




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
				$rows_failed = $data_row["id"];
				continue;
			}


			// 4. Process the web server type

			$final_server_version_id = rules_find_server_version($data_row["server_app"]);

			if (!$final_server_version_id)
			{
				log_write("debug", "process", "Failed, unknown problem during server version processing - input error?");
				$rows_failed = $data_row["id"];
				continue;
			}



			// 5. Process unique ID details for this particular application
			// TODO

			$final_subscription_type	= $data_row["subscription_type"];
			$final_subscription_id		= $data_row["subscription_id"];


			// 6. Process IP address. We don't really care about the IP address itself, but we will
			//    probably want to lookup the IP address against GeoIP and record the origin of the
			//    traffic to a particular region, then discard the address.
			$final_country_id = 0;
			

			// 7. Timestamp.

			$final_timestamp = $data_row["timestamp"];



			// Processing complete - time to create the record!
			$obj_sql_final		= New sql_query;
			$obj_sql_final->string	= "INSERT INTO stats
							(id_app,
							id_app_version,
							id_server_version,
							id_platform_version,
							id_country,
							timestamp,
							subscription_type,
							subscription_id
							) VALUES (
							'$final_app_id',
							'$final_app_version_id',
							'$final_server_version_id',
							'$final_platform_version_id',
							'$final_country_id',
							'$final_timestamp',
							'$final_subscription_type',
							'$final_subscription_id'
							)";
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
	
} // end of process_queue




?>
