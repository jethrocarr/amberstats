<?php
/*
	servers/view.php

	access:
		admin

	Details of the selected server.
*/

class page_output
{
	var $obj_server;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_server		= New server;

		// fetch variables
		$this->obj_server->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=servers/view.php&id=". $this->obj_server->id ."", TRUE);
		$this->obj_menu_nav->add_item("Statistics", "page=servers/stats.php&id=". $this->obj_server->id ."");
		$this->obj_menu_nav->add_item("Delete", "page=servers/delete.php&id=". $this->obj_server->id ."");
	}


	function check_permissions()
	{
		return user_permissions_get("admin");
	}


	function check_requirements()
	{
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
		$this->obj_form->formname	= "server_edit";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "servers/edit-process.php";
		$this->obj_form->method		= "post";

	
		// general
		$structure = NULL;
		$structure["fieldname"] 	= "server_name";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "server_description";
		$structure["type"]		= "textarea";
		$this->obj_form->add_input($structure);

		// regex magic
		$structure = NULL;
		$structure["fieldname"]		= "regex_serverid";
		$structure["type"]		= "input";
		$structure["options"]["label"]	= " ". lang_trans("help_regex_serverid");
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

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

		$structure = NULL;
		$structure["fieldname"]		= "regex_os_type";
		$structure["type"]		= "input";
		$structure["options"]["label"]	= " ". lang_trans("help_regex_os_type");
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]		= "regex_os_version";
		$structure["type"]		= "input";
		$structure["options"]["label"]	= " ". lang_trans("help_regex_os_version");
		$this->obj_form->add_input($structure);






		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_server";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_server->id;
		$this->obj_form->add_input($structure);
		

		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["server_details"]		= array("server_name", "server_description");
		$this->obj_form->subforms["server_regex_id"]		= array("regex_serverid");
		$this->obj_form->subforms["server_regex_versions"]	= array("regex_version_minor", "regex_version_major");
		$this->obj_form->subforms["server_regex_os"]		= array("regex_os_type", "regex_os_version");
		$this->obj_form->subforms["hidden"]			= array("id_server");
		$this->obj_form->subforms["submit"]			= array("submit");


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
				$this->obj_form->structure["regex_serverid"]["defaultvalue"]		= $this->obj_server->data["regex_serverid"];
				$this->obj_form->structure["regex_version_minor"]["defaultvalue"]	= $this->obj_server->data["regex_version_minor"];
				$this->obj_form->structure["regex_version_major"]["defaultvalue"]	= $this->obj_server->data["regex_version_major"];
				$this->obj_form->structure["regex_os_version"]["defaultvalue"]		= $this->obj_server->data["regex_os_version"];
				$this->obj_form->structure["regex_os_type"]["defaultvalue"]		= $this->obj_server->data["regex_os_type"];
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>SERVER TYPE DETAILS</h3><br>";
		print "<p>View and adjust details for your selected server type.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
