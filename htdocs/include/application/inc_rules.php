<?php
/*
	include/application/inc_rules.php

	Functions for processing rules that have been defined for version and server type
	matching with user-defined PCRE/regex.
*/


/*
	rules_find_app_version

	Take the provided application ID and the string with the application version and either
	match an existing version entry, or create a new one, then return the ID.

	Fields
	app_id			ID of the application (as per apps.id table field)
	app_version_string	String with the provided application version.

	Returns
	0	Failure to process
	#	ID of the application version stats field in stats_app_versions
*/

function rules_find_app_version($app_id, $app_version_string)
{
	log_write("debug", "inc_rules" , "Executing rules_find_app_version($app_id, $app_version_string)");

	// serve from cache where possible - keep bulk processing FAST
	if (isset($GLOBALS["cache"]["rules_find_app_version"]["{$app_id}_{$app_version_string}"]))
	{
		return $GLOBALS["cache"]["rules_find_app_version"]["{$app_id}_{$app_version_string}"];
	}

	log_write("debug", "inc_rules", "Version not in cache, running lookup and regex process...");


	// Obtain major/minor version using the defined regex rules. If we can't get a match,
	// then we set to the full provided value

	$data = sql_get_singlerow("SELECT regex_version_minor, regex_version_major FROM apps WHERE id='$app_id' LIMIT 1");

	if ($data["regex_version_major"])
	{
		if (@preg_match($data["regex_version_major"], $app_version_string, $matches))
		{
			$app_version_major = $matches[1];
		}
		else
		{
			$app_version_major = $app_version_string;
		}
	}
	else
	{
		$app_version_major = $app_version_string;
	}

	if ($data["regex_version_minor"])
	{
		if (@preg_match($data["regex_version_minor"], $app_version_string, $matches))
		{
			$app_version_minor = $matches[1];
		}
		else
		{
			$app_version_minor = $app_version_string;
		}
	}
	else
	{
		$app_version_minor = $app_version_string;
	}

	// Do we have an existing version in our stats DB?
	$app_version_id = sql_get_singlevalue("SELECT id as value FROM stats_app_versions WHERE id_app='$app_id' AND version_major='$app_version_major' AND version_minor='$app_version_minor' LIMIT 1");

	if (!$app_version_id)
	{
		// We haven't seen this version before - create a new DB entry, it will be used to associated statistics
		// for this application version.

		log_write("debug", "inc_rules", "Generating new application version entry");

		$obj_sql		= New sql_query;
		$obj_sql->string	= "INSERT INTO stats_app_versions (id_app, version_major, version_minor) VALUES ('$app_id', '$app_version_major', '$app_version_minor')";
		$obj_sql->execute();

		$app_version_id	= $obj_sql->fetch_insert_id();
	}

	// append to cache to avoid duplicate lookups
	$GLOBALS["cache"]["rules_find_app_version"]["{$app_id}_{$app_version_string}"] = $app_version_id;

	return $app_version_id;

} // end of rules_find_app_version




/*
	rules_find_platform_version

	Take the provided application ID and the string with the platform version and either
	match an existing version entry, or create a new one, then return the ID.


	Fields
	app_id			ID of the application (as per apps.id table field)
	platform_version_string	String with the provided platform version.

	Returns
	0	Failure to process
	#	ID of the application version stats field in stats_platform_versions
*/

function rules_find_platform_version($app_id, $platform_version_string)
{
	log_write("debug", "inc_rules" , "Executing rules_find_platform_version($app_id, $platform_version_string)");

	// serve from cache where possible - keep bulk processing FAST
	if (isset($GLOBALS["cache"]["rules_find_platform_version"]["{$app_id}_{$platform_version_string}"]))
	{
		return $GLOBALS["cache"]["rules_find_platform_version"]["{$app_id}_{$platform_version_string}"];
	}

	log_write("debug", "inc_rules", "Version not in cache, running lookup and regex process...");


	// We use the application ID to find the ID of the platform in question.
	$platform_id = sql_get_singlevalue("SELECT id_platform as value FROM apps WHERE id='$app_id' LIMIT 1");

	if (!$platform_id)
	{
		log_write("inc_rules", "error", "Unable to find a platform/application for application id '". $app_id ."'");
		return 0;
	}


	// Obtain major/minor version using the defined regex rules. If we can't get a match,
	// then we set to the full provided value

	$data = sql_get_singlerow("SELECT regex_version_minor, regex_version_major FROM apps_platform WHERE id='$platform_id' LIMIT 1");

	if ($data["regex_version_major"])
	{
		if (@preg_match($data["regex_version_major"], $platform_version_string, $matches))
		{
			$platform_version_major = $matches[1];
		}
		else
		{
			$platform_version_major = $platform_version_string;
		}
	}
	else
	{
		$platform_version_major = $platform_version_string;
	}

	if ($data["regex_version_minor"])
	{
		if (@preg_match($data["regex_version_minor"], $platform_version_string, $matches))
		{
			$platform_version_minor = $matches[1];
		}
		else
		{
			$platform_version_minor = $platform_version_string;
		}
	}
	else
	{
		$platform_version_minor = $platform_version_string;
	}

	// Do we have an existing version in our stats DB?
	$platform_version_id = sql_get_singlevalue("SELECT id as value FROM stats_platform_versions WHERE id_platform='$platform_id' AND version_major='$platform_version_major' AND version_minor='$platform_version_minor' LIMIT 1");

	if (!$platform_version_id)
	{
		// We haven't seen this version before - create a new DB entry, it will be used to associated statistics
		// for this platform version.

		log_write("debug", "inc_rules", "Generating new platform version entry");

		$obj_sql		= New sql_query;
		$obj_sql->string	= "INSERT INTO stats_platform_versions (id_platform, version_major, version_minor) VALUES ('$platform_id', '$platform_version_major', '$platform_version_minor')";
		$obj_sql->execute();

		$platform_version_id	= $obj_sql->fetch_insert_id();
	}

	// append to cache to avoid duplicate lookups
	$GLOBALS["cache"]["rules_find_platform_version"]["{$app_id}_{$platform_version_string}"] = $platform_version_id;

	return $platform_version_id;

} // end of rules_find_platform_version








/*
	rules_find_server_version

	Server versions are a little more tricky as the range of input can vary a lot,
	and type of server is an unknown factor - we need to take in the regex that we
	have and from that, work out the os type, os version, server type and server version,
	where possible.

	Fields
	server_version_string	String with the provided server version

	Returns
	0	Failure to process
	#	ID of the server version stats field in stats_server_versions
*/

function rules_find_server_version($server_version_string)
{
	log_write("debug", "inc_rules" , "Executing rules_find_server_version($server_version_string)");

	// serve from cache where possible - keep bulk processing FAST
	if (isset($GLOBALS["cache"]["rules_find_server_version"][ $server_version_string ]))
	{
		return $GLOBALS["cache"]["rules_find_server_version"][ $server_version_string ];
	}

	log_write("debug", "inc_rules", "Version not in cache, running lookup and regex process...");



	// Firstly we need to fetch all the server ID regex types, and use the regex against this string
	// to match the server type. If we can't match the server type, then there's nothing more we can do,
	// as we won't know what other regexes to apply.

	$server_id = 0;

	$obj_sql		= New sql_query;
	$obj_sql->string	= "SELECT id, regex_serverid FROM apps_servers";
	$obj_sql->execute();
	
	if (!$obj_sql->num_rows())
	{
		log_write("error", "inc_rules", "Unable to process due to no server types being defined");
		return 0;
	}

	$obj_sql->fetch_array();

	foreach ($obj_sql->data as $data_row)
	{
		if (!empty($data_row["regex_serverid"]))
		{
			if (@preg_match($data_row["regex_serverid"], $server_version_string))
			{
				$server_id = $data_row["id"];
				break;
			}
		}
	}

	if (!$server_id)
	{
		log_write("error", "inc_rules", "Unable to process server type \"$server_version_string\" due to no match against regex server types.");
		return 0;
	}



	// Fetch the regex rules for the server type and then apply against the string to obtain what we can - ideally
	// the minor/major versions and the OS type/version.

	$data = sql_get_singlerow("SELECT regex_version_minor, regex_version_major, regex_os_type, regex_os_version FROM apps_servers WHERE id='$server_id' LIMIT 1");

	if ($data["regex_version_major"])
	{
		if (@preg_match($data["regex_version_major"], $server_version_string, $matches))
		{
			$server_version_major = $matches[1];
		}
		else
		{
			$server_version_major = "Unknown";
		}
	}
	else
	{
		$server_version_major = "Unknown";
	}

	if ($data["regex_version_minor"])
	{
		if (@preg_match($data["regex_version_minor"], $server_version_string, $matches))
		{
			$server_version_minor = $matches[1];
		}
		else
		{
			$server_version_minor = "Unknown";
		}
	}
	else
	{
		$server_version_minor = "Unknown";
	}

	if ($data["regex_os_type"])
	{
		if (@preg_match($data["regex_os_type"], $server_version_string, $matches))
		{
			$server_os_type = $matches[1];
		}
		else
		{
			$server_os_type = "Unknown";
		}
	}
	else
	{
		$server_os_type = "Unknown";
	}

	if ($data["regex_os_version"])
	{
		if (@preg_match($data["regex_os_version"], $server_version_string, $matches))
		{
			$server_os_version = $matches[1];
		}
		else
		{
			$server_os_version = "Unknown";
		}
	}
	else
	{
		$server_os_version = "Unknown";
	}


	// Do we have an existing version in our stats DB?
	$server_version_id = sql_get_singlevalue("SELECT id as value FROM stats_server_versions WHERE id_server='$server_id' AND version_major='$server_version_major' AND version_minor='$server_version_minor' AND os_type='$server_os_type' AND os_version='$server_os_version' LIMIT 1");

	if (!$server_version_id)
	{
		// We haven't seen this version before - create a new DB entry, it will be used to associated statistics
		// for this server version.

		log_write("debug", "inc_rules", "Generating new server version entry");

		$obj_sql		= New sql_query;
		$obj_sql->string	= "INSERT INTO stats_server_versions (id_server, os_type, os_version, version_major, version_minor) VALUES ('$server_id', '$server_os_type', '$server_os_version', '$server_version_major', '$server_version_minor')";
		$obj_sql->execute();

		$server_version_id	= $obj_sql->fetch_insert_id();
	}

	// append to cache to avoid duplicate lookups
	$GLOBALS["cache"]["rules_find_server_version"][ $server_version_string ] = $server_version_id;

	return $server_version_id;

} // end of rules_find_server_version





?>
