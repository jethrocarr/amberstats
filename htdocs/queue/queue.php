<?php
/*
	queue/queue.php

	access:
		admin

	View the status of the queue.
*/


class page_output
{
	var $obj_table;


	function check_permissions()
	{
		return user_permissions_get("admin");
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	function execute()
	{
		// establish a new table object
		$this->obj_table = New table;

		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "queue";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "timestamp", "");
		$this->obj_table->add_column("standard", "ipaddress", "");
		$this->obj_table->add_column("standard", "app_name", "");
		$this->obj_table->add_column("standard", "app_version", "");
		$this->obj_table->add_column("standard", "server_app", "");
		$this->obj_table->add_column("standard", "server_platform", "");
		$this->obj_table->add_column("standard", "subscription_type", "");
		$this->obj_table->add_column("standard", "subscription_id", "");

		// defaults
		$this->obj_table->columns		= array("timestamp", "ipaddress", "app_name", "app_version", "server_app", "server_platform", "subscription_type", "subscription_id");
		$this->obj_table->columns_order		= array("timestamp");
		$this->obj_table->columns_order_options	= array("timestamp", "ipaddress", "app_name", "app_version", "server_app", "server_platform", "subscription_type", "subscription_id");

		$this->obj_table->sql_obj->prepare_sql_settable("stats_incoming");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "");

		// options form
		$this->obj_table->load_options_form();

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();

		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			$this->obj_table->data[$i]["timestamp"] = date("Y-m-d H:i:s", $this->obj_table->data[$i]["timestamp"]);
		}

	}


	function render_html()
	{
		// title + summary
		print "<h3>INCOMING PHONE HOME QUEUE</h3>";
		print "<p>All phone home requests get queued in the incoming table, where they are then batch processed against the configured application, platform and server regexes. You can review the current queue contents below to handle any records that aren't being processed, which can occur if the record references an application or server that you haven't configured a type for.</p>";

		// table data
		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>The queue is currently empty.</p>");
		}
		else
		{
			// display the table
			$this->obj_table->render_options_form();
			$this->obj_table->render_table_html();

		}

		// options
		print "<p>";
		print "<a class=\"button\" href=\"queue/queue-process.php\">Force a process of the current queue</a> ";
		print "<a class=\"button\" href=\"queue/queue-empty.php\">Delete queue contents</a>";
		print "</p>";
	}
}


?>
