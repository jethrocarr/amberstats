<?php
/*
	apps/geostats.php

	access:
		admin

	GeoIP Country statistics for application checkins.
*/

class page_output
{
	var $requires;

	var $obj_app;
	var $obj_menu_nav;
	var $obj_form;

	var $graph_activity;
	var $graph_countries;
	var $graph_countries_atm;


	function page_output()
	{

		// initate object
		$this->obj_app			= New app;

		// fetch variables
		$this->obj_app->id		= @security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Details", "page=apps/view.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Application Statistics", "page=apps/stats.php&id=". $this->obj_app->id ."");
		$this->obj_menu_nav->add_item("Geographical Statistics", "page=apps/geostats.php&id=". $this->obj_app->id ."", TRUE);
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
					stats_country.id as country_id,
					stats_country.country_name as country,
					A.total as total
					FROM
					(
						SELECT
						MONTH(date) as month,
						YEAR(date) as year,
						id_country as country,
						COUNT(DISTINCT(subscription_id)) as total
						FROM stats
						WHERE id_app='". $this->obj_app->id ."' 
						GROUP BY YEAR(date),
						MONTH(date),
						country
					) A
					LEFT JOIN stats_country ON A.country = stats_country.id
					ORDER BY year, month";

		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$data		= array();
			$data2		= array();
			$countries	= array();
			$countries_map	= array();
			$dates		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$countries_map[ $data_row["country"] ] = $data_row["country_id"];
				@$data[ $data_row["year"] ."-". $data_row["month"] ][ $data_row["country"] ] += $data_row["total"];
			}

			$countries	= array_keys($countries_map);
			$dates		= array_keys($data);

			foreach ($dates as $date)
			{
				foreach ($countries as $country)
				{
					if (!empty($data[ $date ][ $country ]))
					{
						$data2[ $country ][] = $data[ $date ][ $country ];
					}
					else
					{
						$data2[ $country ][] = 0;
					}
				}
			}
			$this->graph_activity["count"]		= $obj_sql->data_num_rows;
			$this->graph_activity["countries"] 	= $countries;
			$this->graph_activity["data"]		= $data2;

			$data3 = array();
			foreach ($this->graph_activity["countries"] as $country)
			{
				$data3[] = "{$countries_map[$country]}: [ ". format_arraytocommastring($this->graph_activity["data"][$country]) ."]\n";
			}
			$this->graph_activity["data"] = format_arraytocommastring($data3);

			$data3 = array();
			foreach ($this->graph_activity["countries"] as $country)
			{
			  	$data3[] = "{$countries_map[$country]}: '". $country ."'\n";
			}
			$this->graph_activity["countries"] = format_arraytocommastring($data3);

		}

		unset($obj_sql);



		/*
			Report on current countries reporting home in the last 28 days, by unique number of installations.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT stats_country.country_name as country, stats_country.country_code as country_code, COUNT(A.subscription_id) as total "
					 ."FROM "
					 ."(SELECT DISTINCT subscription_id, id_country FROM stats WHERE id_app='". $this->obj_app->id ."' AND DATE_SUB(CURDATE(),INTERVAL 28 DAY) <= date) A "
					 ."LEFT JOIN stats_country ON A.id_country = stats_country.id "
					 ."GROUP BY country_code "
					 ."ORDER BY country_code";
		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$labels		= array();
			$datalabels	= array();
			$count		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$labels[]	= $data_row["country_code"];
				$datalabels[]	= $data_row["country_code"] ."/". $data_row["country"] ." - ". $data_row["total"] ." unique installations";
				$count[] 	= $data_row["total"];
			}

			$this->graph_countries_atm["labels"] 	= format_arraytocommastring($labels, '"');
			$this->graph_countries_atm["datalabels"]	= format_arraytocommastring($datalabels, '"');
			$this->graph_countries_atm["data"]	= format_arraytocommastring($count);
		}

		unset($obj_sql);





		/*
			Unique installations of all countries of the application for the life time of
			the application.
		*/

		$obj_sql		= New sql_query;
		$obj_sql->string	= "SELECT DISTINCT stats_country.country_name as country, stats_country.country_code as country_code, COUNT(A.subscription_id) as total "
					 ."FROM "
					 ."(SELECT DISTINCT subscription_id, id_country FROM stats WHERE id_app='". $this->obj_app->id ."') A "
					 ."LEFT JOIN stats_country ON A.id_country = stats_country.id "
					 ."GROUP BY country_code "
					 ."ORDER BY country_code";
		$obj_sql->execute();

		if ($obj_sql->num_rows())
		{
			$obj_sql->fetch_array();

			$labels		= array();
			$datalabels	= array();
			$count		= array();

			foreach ($obj_sql->data as $data_row)
			{
				$labels[]	= $data_row["country_code"];
				$datalabels[]	= $data_row["country_code"] ."/". $data_row["country"] ." - ". $data_row["total"] ." unique installations";
				$count[] 	= $data_row["total"];
			}

			$this->graph_countries["labels"] 	= format_arraytocommastring($labels, '"');
			$this->graph_countries["datalabels"]	= format_arraytocommastring($datalabels, '"');
			$this->graph_countries["data"]		= format_arraytocommastring($count);
		}

		unset($obj_sql);

	}

	function render_html()
	{
		// title + summary
		print "<h3>GEOGRAPHICAL STATISTICS</h3><br>";



		// Phone home activity
		print "<p><b>User base by Country over time</b></p>";
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
			    ". $this->graph_activity["countries"] ."
			  }
			});
			});
			</script>";

			print "<div id=\"app_activity\" class=\"graph_standard\"></div>";
		}

		// This month only
		print "<br><br>";
		print "<p><b>Active, unique installations in past 28 days.</b><br>
		Number of unique installation of the application per country seen in the past 28 days.</p>";
		if (empty($this->graph_countries_atm))
		{
			format_msgbox("info", "There is no current statistics for application countries, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var app_countries_atm = new Grafico.BarGraph($('app_countries_atm'),
			[". $this->graph_countries_atm["data"] ."],
			{
				labels :              [". $this->graph_countries_atm["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_countries_atm["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"app_countries_atm\" class=\"graph_standard\"></div>";
		}

		// Major application countries - unique installs
		print "<p><b>Unique application installations per country.</b><br>
		Number of unique installation of the application per country ever seen.</p>";

		if (empty($this->graph_countries))
		{
			format_msgbox("info", "There is no current statistics for application countries, unable to generate graph");
		}
		else
		{

			print "<script>
			Event.observe(window, 'load', function() {
			var app_countries = new Grafico.BarGraph($('app_countries'),
			[". $this->graph_countries["data"] ."],
			{
				labels :              [". $this->graph_countries["labels"] ."],
				color :               '#4b80b6',
  				hover_color :         '#006677',
				datalabels :          {one: [". $this->graph_countries["datalabels"] ."]}
			});
			});
			</script>";

			print "<div id=\"app_countries\" class=\"graph_standard\"></div>";
		}
		
	
	}

}

?>
