<?php
/*
	platforms/platforms.php

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
		return user_permissions_get("stats_read");
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
		$this->obj_table->tablename	= "apps_platform";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "platform_name", "");
		$this->obj_table->add_column("standard", "platform_description", "");

		// defaults
		$this->obj_table->columns		= array("platform_name", "platform_description");
		$this->obj_table->columns_order		= array("platform_name");
		$this->obj_table->columns_order_options	= array("platform_name");

		$this->obj_table->sql_obj->prepare_sql_settable("apps_platform");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "apps_platform.id");

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
		print "<h3>PLATFORMS</h3>";
		print "<p>Platforms we are collecting stats for. Platforms are the language or technology your applications are running on, such as PHP, Ruby, Python, Java or something else entirely.</p>";

		// table data
		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>There are currently no platforms defined - you'll need to add at least one before you can start adding applications.</p>");
		}
		else
		{
			// details link
			if (user_permissions_get("stats_config"))
			{
				$structure = NULL;
				$structure["id"]["column"]	= "id";
				$this->obj_table->add_link("tbl_lnk_details", "platforms/view.php", $structure);
			}

			// stats link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_stats", "platforms/stats.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		if (user_permissions_get("stats_config"))
		{
			print "<p><a class=\"button\" href=\"index.php?page=platforms/add.php\">Add a new platform</a></p>";
		}
	}
}


?>
