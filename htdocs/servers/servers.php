<?php
/*
	servers/servers.php

	access:
		admin
	
	Platforms are languages/plaforms, such as PHP, Ruby, Java, etc. We maintain a DB of these in order
	to correlate details from the application agents with the right technology.
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
		$this->obj_table->tablename	= "apps_servers";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "server_name", "");
		$this->obj_table->add_column("standard", "server_description", "");

		// defaults
		$this->obj_table->columns		= array("server_name", "server_description");
		$this->obj_table->columns_order		= array("server_name");
		$this->obj_table->columns_order_options	= array("server_name");

		$this->obj_table->sql_obj->prepare_sql_settable("apps_servers");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "apps_servers.id");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();

/*
		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
		}
*/

	}


	function render_html()
	{
		// title + summary
		print "<h3>SERVER TYPES</h3>";
		print "<p>Known server types. We configure our matching rules for server types to allow us to learn more about the server version and operating system that our application and platform is running under, such Apache on Linux, Nginx on FreeBSD or IIS on Windows.</p>";

		// table data
		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>There are currently no servers defined - you'll need to add at least one to obtain good statistics.</p>");
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_details", "servers/view.php", $structure);

			// stats links
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_stats_servers", "servers/stats.php", $structure);

			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_stats_os", "servers/osstats.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		print "<p><a class=\"button\" href=\"index.php?page=servers/add.php\">Add a new server type</a></p>";
	}
}


?>
