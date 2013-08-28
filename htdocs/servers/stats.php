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
			We need to get a list of all the version IDs for this particular server.
		*/

		$version_id_list = array();

		$obj_version_sql		= New sql_query;
		$obj_version_sql->string	= "SELECT id FROM stats_server_versions WHERE id_server='". $this->obj_server->id ."'";
		$obj_version_sql->execute();
		
		if ($obj_version_sql->num_rows())
		{
			$obj_version_sql->fetch_array();

			foreach ($obj_version_sql->data as $data_row)
			{
				$version_id_list[] = $data_row["id"];
			}
		}

		$version_id_list = format_arraytocommastring($version_id_list);

		unset($obj_version_sql);




		/*
			Install base over past 30 days
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					stats_server_versions.version_major as version,
					COUNT(A.subscription_id) as total
					FROM
					(
						SELECT DISTINCT
						id_server_version,
						subscription_id
						FROM stats
						WHERE id_server_version IN
							(SELECT id FROM stats_server_versions WHERE id_server='". $this->obj_server->id ."')
						AND DATE_SUB(CURDATE(),INTERVAL 1 YEAR) <= date
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
			Obtain size over time range for unique members actively reporting their
			status back.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					A.year as year,
					A.month as month,
					stats_server_versions.version_major as version,
					A.total as total
					FROM
					(
						SELECT
						MONTHNAME(date) as month,
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
					LEFT JOIN stats_server_versions ON A.version = stats_server_versions.id";

		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$data		= array();
			$data2		= array();
			$versions	= array();
			$dates		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$versions[ $data_row["version"] ] = 1;
				$data[ $data_row["year"] ."-". $data_row["month"] ][ $data_row["version"] ] = $data_row["total"];
			}

			$versions	= array_keys($versions);
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

			$this->graph_activity["versions"] 	= $versions;
			$this->graph_activity["data"]		= $data2;

			$data3 = array();
			foreach ($this->graph_activity["versions"] as $version)
			{
				$data3[] = "{$version}: [ ". format_arraytocommastring($this->graph_activity["data"][$version]) ."]";
			}
			$this->graph_activity["data"] = format_arraytocommastring($data3);

			$data3 = array();
			foreach ($this->graph_activity["versions"] as $version)
			{
			  	$data3[] = "{$version}: '". $version ."'";
			}
			$this->graph_activity["versions"] = format_arraytocommastring($data3);


		}

		unset($obj_sql);



		/*
			Obtain historical stats and sizes
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT
					stats_server_versions.version_major as version,
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
		print "<h3>APPLICATION STATISTICS</h3><br>";

		// Phone home activity
		print "<p><b>Unique installation activity over time</b></p>";
		if (empty($this->graph_activity))
		{
			format_msgbox("info", "There is no current statistics for serverlication activity, unable to generate graph");
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
		print "<p><b>Active, unique installations this month</b><br>
		Number of unique installation of the serverlication per version seen. Note that users who upgrade will serverear in the total for both versions.</p>";
		if (empty($this->graph_versions_atm))
		{
			format_msgbox("info", "There is no current statistics for serverlication versions, unable to generate graph");
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

		// Major serverlication versions - unique installs
		print "<p><b>Unique serverlication installations per version.</b><br>
		Number of unique installation of the serverlication per version seen. Note that users who upgrade will serverear in the total for both versions.</p>";
		if (empty($this->graph_versions))
		{
			format_msgbox("info", "There is no current statistics for serverlication versions, unable to generate graph");
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
