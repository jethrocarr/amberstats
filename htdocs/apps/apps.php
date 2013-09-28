<?php
/*
	apps/apps.php

	access:
		admin

	Interface to view and manage applications that are managed by AmberStats.
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
		$this->obj_table->tablename	= "apps";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "app_name", "");
		$this->obj_table->add_column("standard", "app_platform", "apps_platform.platform_name");
		$this->obj_table->add_column("standard", "app_description", "");

		// defaults
		$this->obj_table->columns		= array("app_name", "app_platform", "app_description");
		$this->obj_table->columns_order		= array("app_name");
		$this->obj_table->columns_order_options	= array("app_name");

		$this->obj_table->sql_obj->prepare_sql_settable("apps");
		$this->obj_table->sql_obj->prepare_sql_addjoin("LEFT JOIN apps_platform ON apps_platform.id = apps.id_platform");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "apps.id");

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
		print "<h3>APPLICATIONS</h3>";
		print "<p>View and manage the statistics for all the applications that you have configured.</p>";

		// table data
		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>There are currently no applications being managed - add some and start collecting stats!</p>");
		}
		else
		{
			// details link
			if (user_permissions_get("stats_config"))
			{
				$structure = NULL;
				$structure["id"]["column"]	= "id";
				$this->obj_table->add_link("tbl_lnk_details", "apps/view.php", $structure);
			}

			// stats link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_stats_app", "apps/stats.php", $structure);

			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_stats_geo", "apps/geostats.php", $structure);

			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		if (user_permissions_get("stats_config"))
		{
			print "<p><a class=\"button\" href=\"index.php?page=apps/add.php\">Add a new application</a></p>";
		}
	}
}


?>
