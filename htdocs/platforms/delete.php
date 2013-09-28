<?php
/*
	platforms/delete.php

	access:
		admin

*/

class page_output
{
	var $obj_platform;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_platform	= New platform;

		// fetch variables
		$this->obj_platform->id	= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=platforms/view.php&id=". $this->obj_platform->id ."");
		$this->obj_menu_nav->add_item("Statistics", "page=platforms/stats.php&id=". $this->obj_platform->id ."");
		$this->obj_menu_nav->add_item("Delete", "page=platforms/delete.php&id=". $this->obj_platform->id ."", TRUE);
	}


	function check_permissions()
	{
		return user_permissions_get("stats_config");
	}


	function check_requirements()
	{
		// make sure the server is valid
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
		$this->obj_form->formname	= "platform_delete";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "platforms/delete-process.php";
		$this->obj_form->method		= "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "platform_name";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "platform_description";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_platform";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_platform->id;
		$this->obj_form->add_input($structure);
			

		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this platform and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);

		// submit
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["platform_delete"]	= array("platform_name","platform_description");
		$this->obj_form->subforms["hidden"]		= array("id_platform");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");


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
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>DELETE PLATFORM</h3><br>";
		print "<p>This page allows you to delete an unwanted platform.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
