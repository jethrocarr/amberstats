<?php
/*
	admin/config.php
	
	access: admin only

	Allows administrators to change system-wide settings stored in the config table that affect
	the key operation of the application.
*/

class page_output
{
	var $obj_form;


	function check_permissions()
	{
		return user_permissions_get("admin");
	}

	function check_requirements()
	{
		// nothing to do
		return 1;
	}


	function execute()
	{
		/*
			Define form structure
		*/
		
		$this->obj_form = New form_input;
		$this->obj_form->formname = "config";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "admin/config-process.php";
		$this->obj_form->method = "post";


		// queue options
		$structure = NULL;
		$structure["fieldname"]				= "QUEUE_DELETE_PROCESSED";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Once incoming records have been processed, delete them from the queue. (Always enable, unless debugging)";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "QUEUE_DELETE_INVALID";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Invalid records (records with unknown app names or server types) can be left in the queue to be reviewed and processed once the rules are adjusted. Or they can be ignored and deleted.";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);




/*
		// security options
		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_ENABLE";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Enable to prevent brute-force login attempts";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_LIMIT";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
*/


		// date/time configuration
		$structure = form_helper_prepare_timezonedropdown("TIMEZONE_DEFAULT");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"]				= "DATEFORMAT";
		$structure["type"]				= "radio";
		$structure["values"]				= array("yyyy-mm-dd", "mm-dd-yyyy", "dd-mm-yyyy");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		// amberstats phone home
		$structure = NULL;
		$structure["fieldname"]				= "PHONE_HOME";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Report back to the developers with application, OS, PHP version and a random unique ID so we can better improve this software. (all information is anonymous, private and greatly appreciated. We use this information to focus development and packaging on the main platforms our users are running to better meet your needs.";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$phone_home_info = New phone_home;
		$phone_home_info->stats_generate();

		$structure = NULL;
		$structure["fieldname"]				= "PHONE_HOME_EXAMPLE";
		$structure["type"]				= "text";
		$structure["defaultvalue"]			= "<i>Actual information to be sent: ". format_arraytocommastring(array_values($phone_home_info->stats)) ."</i>";
		$structure["options"]["no_fieldname"]		= "yes";
		$structure["options"]["no_shift"]		= "yes";
		$this->obj_form->add_input($structure);


		// submit section
		$structure = NULL;
		$structure["fieldname"]					= "submit";
		$structure["type"]					= "submit";
		$structure["defaultvalue"]				= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["config_queue"]		= array("QUEUE_DELETE_PROCESSED", "QUEUE_DELETE_INVALID");
//		$this->obj_form->subforms["config_security"]		= array("BLACKLIST_ENABLE", "BLACKLIST_LIMIT");
		$this->obj_form->subforms["config_dateandtime"]		= array("DATEFORMAT", "TIMEZONE_DEFAULT");
		$this->obj_form->subforms["config_amberstats"]		= array("PHONE_HOME", "PHONE_HOME_EXAMPLE");
		$this->obj_form->subforms["submit"]			= array("submit");


		if (error_check())
		{
			// load error datas
			$this->obj_form->load_data_error();
		}
		else
		{
			// fetch all the values from the database
			$sql_config_obj		= New sql_query;
			$sql_config_obj->string	= "SELECT name, value FROM config ORDER BY name";
			$sql_config_obj->execute();
			$sql_config_obj->fetch_array();

			foreach ($sql_config_obj->data as $data_config)
			{
				$this->obj_form->structure[ $data_config["name"] ]["defaultvalue"] = $data_config["value"];
			}

			unset($sql_config_obj);
		}
	}



	function render_html()
	{
		// Title + Summary
		print "<h3>CONFIGURATION</h3><br>";
		print "<p>Use this page to adjust AmberStats' configuration to suit your requirements.</p>";
	
		// display the form
		$this->obj_form->render_form();
	}

	
}

?>
