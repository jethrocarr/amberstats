<?php
/*
	queue/queue-process.php

	access:
		admin

	Process any outstanding items in the queue.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('admin'))
{
	$obj_sql_stats			= New sql_query;
	$obj_sql_stats->string		= "SELECT id FROM stats_incoming";
	$obj_sql_stats->execute();

	if ($obj_sql_stats->num_rows())
	{
		log_write("info", "process_queue", "There are ". $obj_sql_stats->data_num_rows ." items in the incoming queue to process");

		process_queue();
	}
	else
	{
		log_write("info", "process_queue", "No items in the incoming queue to process. Shutting down");
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
