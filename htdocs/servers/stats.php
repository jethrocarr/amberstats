<?php
/*
	servers/stats.php

	access:
		admin

	Statistics and other details relating to the serverlication.
*/

class page_output
{
	var $obj_server;
	var $obj_menu_nav;
	var $obj_form;

	var $option_versions;
	var $requires;

	var $graph_activity;
	var $graph_versions;
	var $graph_versions_atm;


	function page_output()
	{

		// initate object
		$this->obj_server			= New server;

		// fetch variables
		$this->obj_server->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);
		$this->option_versions		= @security_script_input('/^[a-z]*$/', $_GET["versions"]);

		// options for stats
		if (!$this->option_versions)
		{
			$this->option_versions = "major";
		}



		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=servers/view.php&id=". $this->obj_server->id ."");
		$this->obj_menu_nav->add_item("Statistics", "page=servers/stats.php&id=". $this->obj_server->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete", "page=servers/delete.php&id=". $this->obj_server->id ."");

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
			Activity of phone homes for unique installations over lifespan of the server.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					A.year as year,
					A.month as month,
					stats_server_versions.version_". $this->option_versions ." as version,
					stats_server_versions.id as version_id,
					A.total as total
					FROM
					(
						SELECT
						MONTH(date) as month,
						YEAR(date) as year,
						id_server_version as version,
						COUNT(DISTINCT(subscription_id)) as total
						FROM stats
						WHERE id_server_version IN 
							(SELECT id FROM stats_server_versions WHERE id_server='". $this->obj_server->id ."')
						GROUP BY YEAR(date),
						MONTH(date),
						id_server_version
					) A
					LEFT JOIN stats_server_versions ON A.version = stats_server_versions.id
					ORDER BY year, month";

		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$data		= array();
			$data2		= array();
			$versions	= array();
			$version_map	= array();
			$dates		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$version_map[ $data_row["version"] ] = $data_row["version_id"];
				@$data[ $data_row["year"] ."-". $data_row["month"] ][ $data_row["version"] ] += $data_row["total"];
			}

			$versions	= array_keys($version_map);
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
				$data3[] = "{$version_map[$version]}: [ ". format_arraytocommastring($this->graph_activity["data"][$version]) ."]";
			}
			$this->graph_activity["data"] = format_arraytocommastring($data3);

			$data3 = array();
			foreach ($this->graph_activity["versions"] as $version)
			{
			  	$data3[] = "{$version_map[$version]}: '". $version ."'";
			}
			$this->graph_activity["versions"] = format_arraytocommastring($data3);


		}

		unset($obj_sql);




		/*
			Unique installation versions reported in past 28 days.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					stats_server_versions.version_". $this->option_versions ." as version,
					COUNT(A.subscription_id) as total
					FROM
					(
						SELECT DISTINCT
						id_server_version,
						subscription_id
						FROM stats
						WHERE id_server_version IN
							(SELECT id FROM stats_server_versions WHERE id_server='". $this->obj_server->id ."')
						AND DATE_SUB(CURDATE(),INTERVAL 28 DAY) <= date
					) A
					LEFT JOIN stats_server_versions ON A.id_server_version = stats_server_versions.id 
					GROUP BY version
					ORDER BY version";
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
			All versions reported in the life span of the server
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					stats_server_versions.version_". $this->option_versions ." as version,
					COUNT(A.subscription_id) as total
					FROM
					(
						SELECT DISTINCT
						id_server_version,
						subscription_id
						FROM stats
						WHERE id_server_version IN
							(SELECT id FROM stats_server_versions WHERE id_server='". $this->obj_server->id ."')
					) A
					LEFT JOIN stats_server_versions ON A.id_server_version = stats_server_versions.id 
					GROUP BY version
					ORDER BY version";

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
		print "<h3>SERVER STATISTICS</h3><br>";

		// options panel
		print "<div class=\"stats_options\">";
		print "<form method=\"get\" class=\"form_standard\">";
		
		$form = New form_input;
		$form->formname = "server_options";
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
		$structure["defaultvalue"]	= $this->obj_server->id;
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
			format_msgbox("info", "There is no current statistics for serverlication activity, unable to generate graph");
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
			var server_activity = new Grafico.StreamGraph($('server_activity'),
			{
			  ". $this->graph_activity["data"] ."
			},
			{
			  stream_line_smoothing: 'simple',
			  stream_smart_insertion: false,
			  stream_label_threshold: 2,
			  datalabels: {
			    ". $this->graph_activity["versions"] ."
			  }
			});
			});
			</script>";

			print "<div id=\"server_activity\" class=\"graph_standard\"></div>";
		}

		// This month only
		print "<br><br>";
		print "<p><b>Active, unique installations in past 28 days</b><br>
		Number of unique installation of the server per version seen in the past 28 days. Note that users who upgrade will appear in the total for both versions.</p>";

		if (empty($this->graph_versions_atm))
		{
			format_msgbox("info", "There are no current statistics for server versions, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var server_versions_atm = new Grafico.BarGraph($('server_versions_atm'),
			[". $this->graph_versions_atm["data"] ."],
			{
				labels :              [". $this->graph_versions_atm["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_versions_atm["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"server_versions_atm\" class=\"graph_standard\"></div>";
		}


		// Major Server Versions
		print "<p><b>Unique server installations per version.</b><br>
		Number of unique installation of the server per version seen. Note that users who upgrade will appear in the total for both versions.</p>";

		if (empty($this->graph_versions))
		{
			format_msgbox("info", "There are no current statistics for server versions, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var server_versions = new Grafico.BarGraph($('server_versions'),
			[". $this->graph_versions["data"] ."],
			{
				labels :              [". $this->graph_versions["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_versions["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"server_versions\" class=\"graph_standard\"></div>";
		}
	
	}
}

?>
