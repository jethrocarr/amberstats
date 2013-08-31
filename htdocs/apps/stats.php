<?php
/*
	apps/stats.php

	access:
		admin

	Statistics and other details relating to the application.
*/

class page_output
{
	var $requires;
	var $option_versions;

	var $obj_app;
	var $obj_menu_nav;
	var $obj_form;

	var $graph_activity;
	var $graph_versions;
	var $graph_versions_atm;


	function page_output()
	{

		// initate object
		$this->obj_app			= New app;

		// fetch variables
		$this->obj_app->id		= @security_script_input('/^[0-9]*$/', $_GET["id"]);
		$this->option_versions		= @security_script_input('/^[a-z]*$/', $_GET["versions"]);

		// options for stats
		if (!$this->option_versions)
		{
			$this->option_versions = "major";
		}


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=apps/view.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Statistics", "page=apps/stats.php&id=". $this->obj_app->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete", "page=apps/delete.php&id=". $this->obj_app->id ."");

		// include the grafico library and it's dependencies
		$this->requires["javascript"][]	= "external/prototype.js";
		$this->requires["javascript"][]	= "external/raphael.js";
		$this->requires["javascript"][]	= "external/grafico.base.js";
		$this->requires["javascript"][]	= "external/grafico.line.js";
		$this->requires["javascript"][]	= "external/grafico.bar.js";
		$this->requires["javascript"][]	= "external/grafico.spark.js";
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
			Fetch data for use with graphs from SQL database
		*/

		$date_today = date("Y-m-d");
		$date_month_start	= time_calculate_monthdate_first($date_today);
		$date_month_end		= time_calculate_monthdate_last($date_today);


		/*
			Version phone home activity by year and month for lifespan of the application.

			This graph shows an overview of how actively used the application is, by counting
			each time a unique installation is logged into each month.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					A.year as year,
					A.month as month,
					stats_app_versions.version_". $this->option_versions ." as version,
					stats_app_versions.id as version_id,
					A.total as total
					FROM
					(
						SELECT
						MONTH(date) as month,
						YEAR(date) as year,
						id_app_version as version,
						COUNT(DISTINCT(subscription_id)) as total
						FROM stats
						WHERE id_app_version IN 
							(SELECT id FROM stats_app_versions WHERE id_app='". $this->obj_app->id ."')
						GROUP BY YEAR(date),
						MONTH(date),
						id_app_version
					) A
					LEFT JOIN stats_app_versions ON A.version = stats_app_versions.id
					ORDER BY year, month";

		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$data		= array();
			$data2		= array();
			$versions	= array();
			$versions_map	= array();
			$dates		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$versions_map[ $data_row["version"] ] = $data_row["version_id"];
				@$data[ $data_row["year"] ."-". $data_row["month"] ][ $data_row["version"] ] += $data_row["total"];
			}

			$versions	= array_keys($versions_map);
			$dates		= array_keys($data);

			foreach ($dates as $date)
			{
				foreach ($versions as $version)
				{
					if (!empty($data[ $date ][ $version ]))
					{
						$data2[ $version ][] = $data[ $date ][ $version ];
					}
					else
					{
						$data2[ $version ][] = 0;
					}
				}
			}
			$this->graph_activity["count"]		= $obj_sql->data_num_rows;
			$this->graph_activity["versions"] 	= $versions;
			$this->graph_activity["data"]		= $data2;

			$data3 = array();
			foreach ($this->graph_activity["versions"] as $version)
			{
				$data3[] = "{$versions_map[$version]}: [ ". format_arraytocommastring($this->graph_activity["data"][$version]) ."]\n";
			}
			$this->graph_activity["data"] = format_arraytocommastring($data3);

			$data3 = array();
			foreach ($this->graph_activity["versions"] as $version)
			{
			  	$data3[] = "{$versions_map[$version]}: '". $version ."'\n";
			}
			$this->graph_activity["versions"] = format_arraytocommastring($data3);

		}

		unset($obj_sql);



		/*
			Report on current versions reporting home in the last 28 days, by unique number of installations.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT stats_app_versions.version_". $this->option_versions ." as version, COUNT(A.subscription_id) as total "
					 ."FROM "
					 ."(SELECT DISTINCT id_app_version, subscription_id FROM stats WHERE id_app='". $this->obj_app->id ."' AND DATE_SUB(CURDATE(),INTERVAL 28 DAY) <= date) A "
					 ."LEFT JOIN stats_app_versions ON A.id_app_version = stats_app_versions.id "
					 ."GROUP BY version "
					 ."ORDER BY version";
		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$labels		= array();
			$datalabels	= array();
			$count		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$labels[]	= $data_row["version"];
				$datalabels[]	= $data_row["version"] ." - ". $data_row["total"] ." unique installations";
				$count[] 	= $data_row["total"];
			}

			$this->graph_versions_atm["labels"] 	= format_arraytocommastring($labels, '"');
			$this->graph_versions_atm["datalabels"]	= format_arraytocommastring($datalabels, '"');
			$this->graph_versions_atm["data"]	= format_arraytocommastring($count);
		}

		unset($obj_sql);





		/*
			Unique installations of all versions of the application for the life time of
			the application.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT stats_app_versions.version_". $this->option_versions ." as version, COUNT(A.subscription_id) as total "
					 ."FROM "
					 ."(SELECT DISTINCT id_app_version, subscription_id FROM stats WHERE id_app='". $this->obj_app->id ."') A "
					 ."LEFT JOIN stats_app_versions ON A.id_app_version = stats_app_versions.id "
					 ."GROUP BY version "
					 ."ORDER BY version";
		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$labels		= array();
			$datalabels	= array();
			$count		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$labels[]	= $data_row["version"];
				$datalabels[]	= $data_row["version"] ." - ". $data_row["total"] ." unique installations";
				$count[] 	= $data_row["total"];
			}

			$this->graph_versions["labels"] 	= format_arraytocommastring($labels, '"');
			$this->graph_versions["datalabels"]	= format_arraytocommastring($datalabels, '"');
			$this->graph_versions["data"]		= format_arraytocommastring($count);
		}

		unset($obj_sql);

	}

	function render_html()
	{
		// title + summary
		print "<h3>APPLICATION STATISTICS</h3><br>";


		// options panel
		print "<div class=\"stats_options\">";
		print "<form method=\"get\" class=\"form_standard\">";
		
		$form = New form_input;
		$form->formname = "apps_options";
		$form->language = "en_us";

		// include page name
		$structure = NULL;
		$structure["fieldname"] 	= "page";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $_GET["page"];
		$form->add_input($structure);
		$form->render_field("page");

		// include ID
		$structure = NULL;
		$structure["fieldname"]		= "id";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_app->id;
		$form->add_input($structure);
		$form->render_field("id");

		// options
		$structure = NULL;
		$structure["fieldname"]			= "versions";
		$structure["type"]			= "radio";
		$structure["defaultvalue"]		= $this->option_versions;
		$structure["values"]			= array("minor", "major");
		$structure["translations"]		= array("minor" => "Minor Versions", "major" => "Major Versions");
		$form->add_input($structure);
		$form->render_field("versions");


		// submit button	
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Apply Options";
		$form->add_input($structure);
		$form->render_field("submit");

		print "</div>";


		// Phone home activity
		print "<p><b>Unique installation activity over time</b></p>";
		if (empty($this->graph_activity))
		{
			format_msgbox("info", "There is no current statistics for application activity, unable to generate graph");
		}
		elseif ($this->graph_activity["count"] < 10)
		{
			print "<div class=\"circle\">". $this->graph_activity["count"] ."</div>";
			print "<p align=\"center\"><i>There are insufficent unique installations to plot installation activity over time yet. Come back when more installations have reported home.</i></p>";
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var app_activity = new Grafico.StreamGraph($('app_activity'),
			{
			  ". $this->graph_activity["data"] ."
			},
			{
			  stream_line_smoothing: 'simple',
			  stream_smart_insertion: false,
			  stream_label_threshold: 5,
			  datalabels: {
			    ". $this->graph_activity["versions"] ."
			  }
			});
			});
			</script>";

			print "<div id=\"app_activity\" class=\"graph_standard\"></div>";
		}

		// This month only
		print "<br><br>";
		print "<p><b>Active, unique installations this month</b><br>
		Number of unique installation of the application per version seen. Note that users who upgrade will appear in the total for both versions.</p>";
		if (empty($this->graph_versions_atm))
		{
			format_msgbox("info", "There is no current statistics for application versions, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var app_versions_atm = new Grafico.BarGraph($('app_versions_atm'),
			[". $this->graph_versions_atm["data"] ."],
			{
				labels :              [". $this->graph_versions_atm["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_versions_atm["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"app_versions_atm\" class=\"graph_standard\"></div>";
		}

		// Major application versions - unique installs
		print "<p><b>Unique application installations per version.</b><br>
		Number of unique installation of the application per version seen. Note that users who upgrade will appear in the total for both versions.</p>";
		if (empty($this->graph_versions))
		{
			format_msgbox("info", "There is no current statistics for application versions, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var app_versions = new Grafico.BarGraph($('app_versions'),
			[". $this->graph_versions["data"] ."],
			{
				labels :              [". $this->graph_versions["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_versions["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"app_versions\" class=\"graph_standard\"></div>";
		}
		
	
	}

}

?>
