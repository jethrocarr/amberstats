<?php
/*
	apps/delete.php

	access:
		admin

*/

class page_output
{
	var $obj_app;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{
		// initate object
		$this->obj_app	= New app;

		// fetch variables
		$this->obj_app->id	= security_script_input('/^[0-9]*$/', $_GET["id"]);

		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=apps/view.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Application Statistics", "page=apps/stats.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Geographical Statistics", "page=apps/geostats.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Delete", "page=apps/delete.php&id=". $this->obj_app->id ."", TRUE);
	}


	function check_permissions()
	{
		return user_permissions_get("admin");
	}


	function check_requirements()
	{
		// make sure the app is valid
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
		$this->obj_form->formname	= "app_delete";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "apps/delete-process.php";
		$this->obj_form->method		= "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "app_name";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "app_description";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_app";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_app->id;
		$this->obj_form->add_input($structure);
			

		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this app and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);

		// submit
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["app_delete"]		= array("app_name","app_description");
		$this->obj_form->subforms["hidden"]		= array("id_app");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");


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
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>DELETE APPLICATION</h3><br>";
		print "<p>This page allows you to delete an unwanted app.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
