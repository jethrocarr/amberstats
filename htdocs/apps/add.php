<?php
/*
	apps/add.php

	access: admin only

	Add a new application to be monitored.
*/

class page_output
{
	var $obj_menu_nav;
	var $obj_form;


	function check_permissions()
	{
		return user_permissions_get("stats_config");
	}


	function check_requirements()
	{
		// nothing todo
		return 1;
	}



	function execute()
	{
		/*
			Define form structure
		*/
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "app_add";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "apps/edit-process.php";
		$this->obj_form->method		= "post";


		// general
		$structure = NULL;
		$structure["fieldname"] 	= "app_name";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "app_description";
		$structure["type"]		= "textarea";
		$this->obj_form->add_input($structure);


		// platform details
		$structure				= form_helper_prepare_dropdownfromdb("id_platform", "SELECT id, platform_name as label FROM apps_platform");
		$structure["options"]["req"]		= "yes";
		$structure["options"]["autoselect"]	= 1;
		$this->obj_form->add_input($structure);


		// regex magic
		$structure = NULL;
		$structure["fieldname"]		= "regex_version_minor";
		$structure["type"]		= "input";
		$structure["options"]["label"]	= " ". lang_trans("help_regex_version_minor");
		$structure["defaultvalue"]	= "/\/([0-9.]*)/";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]		= "regex_version_major";
		$structure["type"]		= "input";
		$structure["options"]["label"]	= " ". lang_trans("help_regex_version_major");
		$structure["defaultvalue"]	= "/\/([0-9]*.[0-9]*)/";
		$this->obj_form->add_input($structure);


		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		

		// subforms
		$this->obj_form->subforms["app_details"]	= array("app_name", "app_description", "id_platform");
		$this->obj_form->subforms["app_regex"]		= array("regex_version_minor", "regex_version_major");
		$this->obj_form->subforms["submit"]		= array("submit");



		// load data
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>ADD NEW APPLICATION</h3><br>";
		print "<p>This page allows you to add an application to collect statistics for.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
