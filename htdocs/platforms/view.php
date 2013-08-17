<?php
/*
	platforms/view.php

	access:
		admin

	Details of the selected platform.
*/

class page_output
{
	var $obj_platform;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_platform		= New platform;

		// fetch variables
		$this->obj_platform->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=platforms/view.php&id=". $this->obj_platform->id ."", TRUE);
		$this->obj_menu_nav->add_item("Statistics", "page=platforms/stats.php&id=". $this->obj_platform->id ."");
		$this->obj_menu_nav->add_item("Delete", "page=platforms/delete.php&id=". $this->obj_platform->id ."");
	}


	function check_permissions()
	{
		return user_permissions_get("admin");
	}


	function check_requirements()
	{
		if (!$this->obj_platform->verify_id())
		{
			log_write("error", "page_output", "The requested platform (". $this->obj_platform->id .") does not exist - possibly the platform has been deleted?");
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
		$this->obj_form->formname	= "platform_edit";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "platforms/edit-process.php";
		$this->obj_form->method		= "post";

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "platform_name";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "platform_description";
		$structure["type"]		= "textarea";
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
		$structure["fieldname"] 	= "id_platform";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_platform->id;
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["platform_details"]	= array("platform_name", "platform_description");
		$this->obj_form->subforms["platform_regex"]	= array("regex_version_minor", "regex_version_major");
		$this->obj_form->subforms["hidden"]		= array("id_platform");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			if ($this->obj_platform->load_data())
			{
				$this->obj_form->structure["platform_name"]["defaultvalue"]		= $this->obj_platform->data["platform_name"];
				$this->obj_form->structure["platform_description"]["defaultvalue"]	= $this->obj_platform->data["platform_description"];
				$this->obj_form->structure["regex_version_minor"]["defaultvalue"]	= $this->obj_platform->data["regex_version_minor"];
				$this->obj_form->structure["regex_version_major"]["defaultvalue"]	= $this->obj_platform->data["regex_version_major"];
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>PLATFORM DETAILS</h3><br>";
		print "<p>View and adjust details for your selected platform.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
