<?php
/*
	servers/delete.php

	access:
		admin

*/

class page_output
{
	var $obj_server;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_server	= New server;

		// fetch variables
		$this->obj_server->id	= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=servers/view.php&id=". $this->obj_server->id ."");
		$this->obj_menu_nav->add_item("Server Statistics", "page=servers/stats.php&id=". $this->obj_server->id ."");
		$this->obj_menu_nav->add_item("Operating System Statistics", "page=servers/osstats.php&id=". $this->obj_server->id ."");
		$this->obj_menu_nav->add_item("Delete", "page=servers/delete.php&id=". $this->obj_server->id ."", TRUE);
	}


	function check_permissions()
	{
		return user_permissions_get("stats_config");
	}


	function check_requirements()
	{
		// make sure the server is valid
		if (!$this->obj_server->verify_id())
		{
			log_write("error", "page_output", "The requested server (". $this->obj_server->id .") does not exist - possibly the server has been deleted?");
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
		$this->obj_form->formname	= "server_delete";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "servers/delete-process.php";
		$this->obj_form->method		= "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "server_name";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "server_description";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_server";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_server->id;
		$this->obj_form->add_input($structure);
			

		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this server and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);

		// submit
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["server_delete"]	= array("server_name","server_description");
		$this->obj_form->subforms["hidden"]		= array("id_server");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");


		// import data
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			if ($this->obj_server->load_data())
			{
				$this->obj_form->structure["server_name"]["defaultvalue"]		= $this->obj_server->data["server_name"];
				$this->obj_form->structure["server_description"]["defaultvalue"]	= $this->obj_server->data["server_description"];
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>DELETE SERVER</h3><br>";
		print "<p>This page allows you to delete an unwanted server.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
