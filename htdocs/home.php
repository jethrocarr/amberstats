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
		function check_permissions()
		{
			if (user_permissions_get("admin"))
			{
				return 1;
			}
			else
			{
				log_write("error", "page_output", "You do not have permissions to access this interface, request your administrator to assign you to the namedadmins group");
				return 0;
			}
		}


		function check_requirements()
		{
			// nothing todo
			return 1;
		}
			
		function execute()
		{
			// nothing todo
			return 1;
		}

		function render_html()
		{
			print "<h3>OVERVIEW</h3>";
			print "<p>Welcome to AmberStats, a phone home statistics and software usage reporting system and utility.</p>";


		}
	}
}

?>
