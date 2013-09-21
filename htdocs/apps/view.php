<?php
/*
	apps/view.php

	access:
		admin

	Details of the selected app.
*/

class page_output
{
	var $obj_app;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_app		= New app;

		// fetch variables
		$this->obj_app->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=apps/view.php&id=". $this->obj_app->id ."", TRUE);
		$this->obj_menu_nav->add_item("Application Statistics", "page=apps/stats.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Geographical Statistics", "page=apps/geostats.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Delete", "page=apps/delete.php&id=". $this->obj_app->id ."");
	}


	function check_permissions()
	{
		return user_permissions_get("admin");
	}


	function check_requirements()
	{
		if (!$this->obj_app->verify_id())
		{
			log_write("error", "page_output", "The requested app (". $this->obj_app->id .") does not exist - possibly the app has been deleted?");
			return 0;
		}

		return 1;
	}



	function execute()
	{
		/*
			Define form structure
		*/
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "app_edit";
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
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]		= "regex_version_major";
		$structure["type"]		= "input";
		$structure["options"]["label"]	= " ". lang_trans("help_regex_version_major");
		$this->obj_form->add_input($structure);


		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_app";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_app->id;
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["app_details"]	= array("app_name", "app_description", "id_platform");
		$this->obj_form->subforms["app_regex"]		= array("regex_version_minor", "regex_version_major");
		$this->obj_form->subforms["hidden"]		= array("id_app");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			if ($this->obj_app->load_data())
			{
				$this->obj_form->structure["app_name"]["defaultvalue"]		= $this->obj_app->data["app_name"];
				$this->obj_form->structure["app_description"]["defaultvalue"]	= $this->obj_app->data["app_description"];
				$this->obj_form->structure["id_platform"]["defaultvalue"]	= $this->obj_app->data["id_platform"];
				$this->obj_form->structure["regex_version_minor"]["defaultvalue"]	= $this->obj_app->data["regex_version_minor"];
				$this->obj_form->structure["regex_version_major"]["defaultvalue"]	= $this->obj_app->data["regex_version_major"];
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>APPLICATION DETAILS</h3><br>";
		print "<p>View and adjust details for your selected app.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
