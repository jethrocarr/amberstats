<?php
/*
	platforms/stats.php

	access:
		admin

	Statistics and other details relating to the platformlication.
*/

class page_output
{
	var $obj_platform;
	var $obj_menu_nav;
	var $obj_form;
	var $requires;

	var $graph_activity;
	var $graph_versions;
	var $graph_versions_atm;


	function page_output()
	{

		// initate object
		$this->obj_platform			= New platform;

		// fetch variables
		$this->obj_platform->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=platforms/view.php&id=". $this->obj_platform->id ."");
		$this->obj_menu_nav->add_item("Statistics", "page=platforms/stats.php&id=". $this->obj_platform->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete", "page=platforms/delete.php&id=". $this->obj_platform->id ."");

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
			Fetch data for use with graphs from SQL database
		*/

		$obj_version_sql		= New sql_query;
		$obj_version_sql->string	= "SELECT id, version_minor FROM stats_platform_versions WHERE id_platform='". $this->obj_platform->id ."'";
		$obj_version_sql->execute();
		
		$version_map = array();
		
		if ($obj_version_sql->num_rows())
		{
			$obj_version_sql->fetch_array();

			foreach ($obj_version_sql->data as $data_row)
			{
				$version_map[ $data_row["id"] ] = $data_row["version_minor"];
			}
		}

		$version_id_list = format_arraytocommastring(array_keys($version_map));

		unset($obj_version_sql);








		$date_today = date("Y-m-d");

		$date_month_start	= time_calculate_monthdate_first($date_today);
		$date_month_end		= time_calculate_monthdate_last($date_today);


		// Current Version Splits

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT stats_platform_versions.version_minor as version, COUNT(A.subscription_id) as total "
					 ."FROM "
					 ."(SELECT DISTINCT id_platform_version, subscription_id FROM stats WHERE id_platform_version IN (". $version_id_list .") AND date >= '$date_month_start') A "
					 ."LEFT JOIN stats_platform_versions ON A.id_platform_version = stats_platform_versions.id "
					 ."GROUP BY id_platform_version "
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




		// Phone Home Activity
		// For the last 12 months show the phone home activity per major version

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT MONTHNAME(date) as month,
						YEAR(date) as year,
						id_platform_version as version,
						COUNT(DISTINCT(subscription_id)) as total
						FROM stats
						WHERE id_platform_version IN (". $version_id_list .")
						GROUP BY YEAR(date), MONTH(date), id_platform_version";
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
			  	$data3[] = "{$version}: '". $version_map[$version] ."'";
			}
			$this->graph_activity["versions"] = format_arraytocommastring($data3);


		}

		unset($obj_sql);




		// Historical totals for platformlication per minor version
		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT stats_platform_versions.version_minor as version, COUNT(A.subscription_id) as total "
					 ."FROM "
					 ."(SELECT DISTINCT id_platform_version, subscription_id FROM stats WHERE id_platform_version IN (". $version_id_list .")) A "
					 ."LEFT JOIN stats_platform_versions ON A.id_platform_version = stats_platform_versions.id "
					 ."GROUP BY id_platform_version "
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

		// Phone home activity
		print "<p><b>Unique installation activity over time</b></p>";
		if (empty($this->graph_activity))
		{
			format_msgbox("info", "There is no current statistics for platformlication activity, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var platform_activity = new Grafico.StreamGraph($('platform_activity'),
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

			print "<div id=\"platform_activity\" class=\"graph_standard\"></div>";
		}

		// This month only
		print "<p><b>Active, unique installations this month</b><br>
		Number of unique installation of the platformlication per version seen. Note that users who upgrade will platformear in the total for both versions.</p>";
		if (empty($this->graph_versions_atm))
		{
			format_msgbox("info", "There is no current statistics for platformlication versions, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var platform_versions_atm = new Grafico.BarGraph($('platform_versions_atm'),
			[". $this->graph_versions_atm["data"] ."],
			{
				labels :              [". $this->graph_versions_atm["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_versions_atm["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"platform_versions_atm\" class=\"graph_standard\"></div>";
		}

		// Major platformlication versions - unique installs
		print "<p><b>Unique platformlication installations per version.</b><br>
		Number of unique installation of the platformlication per version seen. Note that users who upgrade will platformear in the total for both versions.</p>";
		if (empty($this->graph_versions))
		{
			format_msgbox("info", "There is no current statistics for platformlication versions, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var platform_versions = new Grafico.BarGraph($('platform_versions'),
			[". $this->graph_versions["data"] ."],
			{
				labels :              [". $this->graph_versions["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_versions["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"platform_versions\" class=\"graph_standard\"></div>";
		}
		
	
	}

}

?>
