<?php
/*
	Summary/Welcome page for AmberStats
*/

if (!user_online())
{
	// Because this is the default page to be directed to, if the user is not
	// logged in, they should go straight to the login page.
	//
	// All other pages will display an error and prompt the user to login.
	//
	include_once("user/login.php");
}
else
{
	class page_output
	{
		var $requires;
		var $graph_activity;
		var $graph_servers;
		var $graph_os;
		var $graph_platform;

		function page_output()
		{
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
			// everyone permitted
			return 1;
		}


		function check_requirements()
		{
			// nothing todo
			return 1;
		}
			
		function execute()
		{

			/*
				Fetch the current list of applications we look after
			*/

			$obj_sql		= New sql_query;
			$obj_sql->string	= "SELECT id, app_name FROM apps";
			$obj_sql->execute();
			
			$app_map = array();
			
			if ($obj_sql->num_rows())
			{
				$obj_sql->fetch_array();

				foreach ($obj_sql->data as $data_row)
				{
					$app_map[ $data_row["id"] ] = $data_row["app_name"];
				}
			}

			unset($obj_sql);



			/*
				Fetch application activity graph data

				This graph shows the number of unique users by month, by application over
				the past 12 months. It's a quick overview that shows the scale differences
				between the different applications that the user has configured.
			*/

			$obj_sql		= New sql_query;
			$obj_sql->string	= "SELECT MONTH(date) as month,
							YEAR(date) as year,
							id_app,
							COUNT(DISTINCT(subscription_id)) as total
							FROM stats
							WHERE DATE_SUB(CURDATE(),INTERVAL 1 YEAR) <= date
							GROUP BY YEAR(date), MONTH(date), id_app
							ORDER BY year, month";
			$obj_sql->execute();

			if ($obj_sql->num_rows())
			{
				$obj_sql->fetch_array();

				$data		= array();
				$data2		= array();
				$apps		= array();
				$dates		= array();

				foreach ($obj_sql->data as $data_row)
				{
					$apps[ $data_row["id_app"] ] = 1;
					$data[ $data_row["year"] ."-". $data_row["month"] ][ $data_row["id_app"] ] = $data_row["total"];
				}

				$apps		= array_keys($apps);
				$dates		= array_keys($data);

				foreach ($dates as $date)
				{
					foreach ($apps as $app)
					{
						if (!empty($data[ $date ][ $app ]))
						{
							$data2[ $app ][] = $data[ $date ][ $app ];
						}
						else
						{
							$data2[ $app ][] = 0;
						}
					}
				}

				$this->graph_activity["apps"] 		= $apps;
				$this->graph_activity["data"]		= $data2;

				$data3 = array();
				foreach ($this->graph_activity["apps"] as $app)
				{
					$data3[] = "{$app}: [ ". format_arraytocommastring($this->graph_activity["data"][$app]) ."]";
				}
				$this->graph_activity["data"] = format_arraytocommastring($data3);

				$data3 = array();
				foreach ($this->graph_activity["apps"] as $app)
				{
					$data3[] = "{$app}: '". $app_map[$app] ."'";
				}
				$this->graph_activity["apps"] = format_arraytocommastring($data3);

			}

			unset($obj_sql);



			/*
				Mini Bar - HTTP servers
			*/

			$obj_sql		= New sql_query;
			$obj_sql->string	= "SELECT DISTINCT
							apps_servers.server_name as server,
                		                        SUM(A.total) as total
                                		        FROM
		                                        (
                		                                SELECT
                                		                id_server_version as version,
                                                		COUNT(DISTINCT(subscription_id)) as total
		                                                FROM stats
								WHERE DATE_SUB(CURDATE(),INTERVAL 1 YEAR) <= date
                	                	                GROUP BY id_server_version
                        		                ) A
		                                        LEFT JOIN stats_server_versions ON A.version = stats_server_versions.id
							LEFT JOIN apps_servers ON stats_server_versions.id_server = apps_servers.id
							GROUP BY server";

			$obj_sql->execute();

			if ($obj_sql->num_rows())
			{
				$obj_sql->fetch_array();

				$labels		= array();
				$datalabels	= array();
				$count		= array();

				foreach ($obj_sql->data as $data_row)
				{
					$labels[]	= $data_row["server"];
					$datalabels[]	= $data_row["server"] ." - ". $data_row["total"] ." unique installations";
					$count[] 	= $data_row["total"];
				}

				$this->graph_servers["labels"] 		= format_arraytocommastring($labels, '"');
				$this->graph_servers["datalabels"]	= format_arraytocommastring($datalabels, '"');
				$this->graph_servers["data"]		= format_arraytocommastring($count);
			}

			unset($obj_sql);


			/*
				Minibar - Operating Systems
			*/

			$obj_sql		= New sql_query;
			$obj_sql->string	= "SELECT DISTINCT
							stats_server_versions.os_type as os,
                		                        SUM(A.total) as total
                                		        FROM
		                                        (
                		                                SELECT
                                		                id_server_version as version,
                                                		COUNT(DISTINCT(subscription_id)) as total
		                                                FROM stats
								WHERE DATE_SUB(CURDATE(),INTERVAL 1 YEAR) <= date
                	                	                GROUP BY id_server_version
                        		                ) A
		                                        LEFT JOIN stats_server_versions ON A.version = stats_server_versions.id
							GROUP BY os";

			$obj_sql->execute();

			if ($obj_sql->num_rows())
			{
				$obj_sql->fetch_array();

				$labels		= array();
				$datalabels	= array();
				$count		= array();

				foreach ($obj_sql->data as $data_row)
				{
					$labels[]	= $data_row["os"];
					$datalabels[]	= $data_row["os"] ." - ". $data_row["total"] ." unique installations";
					$count[] 	= $data_row["total"];
				}

				$this->graph_os["labels"] 	= format_arraytocommastring($labels, '"');
				$this->graph_os["datalabels"]	= format_arraytocommastring($datalabels, '"');
				$this->graph_os["data"]		= format_arraytocommastring($count);
			}

			unset($obj_sql);



			/*
				Minibar - Platforms
			*/

			$obj_sql		= New sql_query;
			$obj_sql->string	= "SELECT DISTINCT
							apps_platform.platform_name as name,
                		                        SUM(A.total) as total
                                		        FROM
		                                        (
                		                                SELECT
                                		                id_platform_version as version,
                                                		COUNT(DISTINCT(subscription_id)) as total
		                                                FROM stats
								WHERE DATE_SUB(CURDATE(),INTERVAL 1 YEAR) <= date
                	                	                GROUP BY id_platform_version
                        		                ) A
		                                        LEFT JOIN stats_platform_versions ON A.version = stats_platform_versions.id
							LEFT JOIN apps_platform ON stats_platform_versions.id_platform = apps_platform.id
							GROUP BY name";

			$obj_sql->execute();

			if ($obj_sql->num_rows())
			{
				$obj_sql->fetch_array();

				$labels		= array();
				$datalabels	= array();
				$count		= array();

				foreach ($obj_sql->data as $data_row)
				{
					$labels[]	= $data_row["name"];
					$datalabels[]	= $data_row["name"] ." - ". $data_row["total"] ." unique installations";
					$count[] 	= $data_row["total"];
				}

				$this->graph_platform["labels"] 	= format_arraytocommastring($labels, '"');
				$this->graph_platform["datalabels"]	= format_arraytocommastring($datalabels, '"');
				$this->graph_platform["data"]		= format_arraytocommastring($count);
			}

			unset($obj_sql);



			return 1;
		}

		function render_html()
		{
			print "<h3>OVERVIEW</h3>";
			print "<p>Amberstats is a lightweight reporting tool that takes phone home data in from applications and generates statistics such as user base size, the versions being used, the versions of the platform your app runs on and makes estimations about the size and activity of your user base.</p>";


			/*
				Activity Graph
			*/
			if (empty($this->graph_activity))
			{
				format_msgbox("info", "You haven't collected any statistics yet - get started by adding in your applications, platform and servers, then ensure you're pushing content to the API and running the cronjob.");
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
				    ". $this->graph_activity["apps"] ."
				  }
				});
				});
				</script>";

				print "<div id=\"app_activity\" class=\"graph_standard\"></div>";
				print "<p align=\"center\"><i>Unique installation sizes for your applications in proportion to each other, over past 12 months.</i></p>";
			}


			/*
				Small Bar Graphs
				- Platforms
				- HTTP servers
				- Operating Systems
			*/

			print "<div class=\"center\">";

			if (!empty($this->graph_platform))
			{
				print "<script>
				Event.observe(window, 'load', function() {
				var platform = new Grafico.BarGraph($('platform'),
				[". $this->graph_platform["data"] ."],
				{
					labels :              [". $this->graph_platform["labels"] ."],
					label_rotation :      -30,
					color :               '#4b80b6',
					hover_color :         '#006677',
					datalabels :          {one: [". $this->graph_platform["datalabels"] ."]}
				});
				});
				</script>";

				print "<div id=\"platform\" class=\"graph_small\"></div>";
			}


			if (!empty($this->graph_servers))
			{
				print "<script>
				Event.observe(window, 'load', function() {
				var servers = new Grafico.BarGraph($('servers'),
				[". $this->graph_servers["data"] ."],
				{
					labels :              [". $this->graph_servers["labels"] ."],
					color :               '#4b80b6',
					hover_color :         '#006677',
					datalabels :          {one: [". $this->graph_servers["datalabels"] ."]}
				});
				});
				</script>";

				print "<div id=\"servers\" class=\"graph_small\"></div>";
			}


			if (!empty($this->graph_os))
			{
				print "<script>
				Event.observe(window, 'load', function() {
				var os = new Grafico.BarGraph($('os'),
				[". $this->graph_os["data"] ."],
				{
					labels :              [". $this->graph_os["labels"] ."],
					label_rotation :      -30,
					color :               '#4b80b6',
					hover_color :         '#006677',
					datalabels :          {one: [". $this->graph_os["datalabels"] ."]}
				});
				});
				</script>";

				print "<div id=\"os\" class=\"graph_small\"></div>";
			}

			print "</div>";

		}
	}
}

?>
