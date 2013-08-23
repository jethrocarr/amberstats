#!/usr/bin/php
<?php
/*
	include/cron/process_queue.php

	Processes any/all items in the incoming Amberstats queue and turns the data into proper
	statistics.
*/

// load framework
require("../config.php");
require("../amberphplib/main.php");
require("../application/main.php");


// check queue contents (nothing to process? then we should shut down again asap)
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


// Display Stats if debugging
if ($_SESSION["user"]["debug"] == "on")
{
	log_debug_render();
}

log_write("debug", "process_queue", "Clean shutdown");
exit(0);

?>
