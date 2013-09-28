<?php
/*
	queue/queue-empty.php

	access:
		admin

	Delete all items currently in the queue.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('stats_config'))
{
	$obj_sql_stats			= New sql_query;
	$obj_sql_stats->string		= "TRUNCATE TABLE `stats_incoming`";

	if (!$obj_sql_stats->execute())
	{
		log_write("error", "process", "An unexpected error occured whilst truncating table");
	}

	header("Location: ../index.php?page=queue/queue.php");
	exit(0);
	
} // end of "is user logged in?"
else
{
	// user does not have permissions to access this page.
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
