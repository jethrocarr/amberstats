-- AMBERSTATS INITAL INSTALLATION DB
--
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oss-amberstats`
--

CREATE DATABASE `amberstats` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `amberstats`;


-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE IF NOT EXISTS `apps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_platform` int(10) unsigned NOT NULL DEFAULT '0',
  `app_name` varchar(255) NOT NULL,
  `app_description` text NOT NULL,
  `regex_version_major` varchar(255) NOT NULL DEFAULT '',
  `regex_version_minor` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `apps_agents`
--

CREATE TABLE IF NOT EXISTS `apps_agents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_application` int(11) unsigned NOT NULL,
  `agent_name` varchar(50) NOT NULL,
  `agent_description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_application` (`id_application`),
  KEY `agent_name` (`agent_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `apps_platform`
--

CREATE TABLE IF NOT EXISTS `apps_platform` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(255) NOT NULL DEFAULT '',
  `platform_description` text NOT NULL,
  `regex_version_minor` varchar(255) NOT NULL DEFAULT '',
  `regex_version_major` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;


--
-- Table structure for table `apps_servers`
--

CREATE TABLE IF NOT EXISTS `apps_servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_name` varchar(255) NOT NULL,
  `server_description` text NOT NULL,
  `regex_serverid` varchar(255) NOT NULL,
  `regex_version_major` varchar(255) NOT NULL,
  `regex_version_minor` varchar(255) NOT NULL,
  `regex_os_type` varchar(255) NOT NULL,
  `regex_os_version` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('AUTH_METHOD', 'sql'),
('AUTH_PERMS_CACHE', 'enabled'),
('BLACKLIST_ENABLE', 'disabled'),
('BLACKLIST_LIMIT', '10'),
('DATA_STORAGE_LOCATION', 'use_database'),
('DATA_STORAGE_METHOD', 'database'),
('DATEFORMAT', 'yyyy-mm-dd'),
('LANGUAGE_DEFAULT', 'en_us'),
('LANGUAGE_LOAD', 'preload'),
('PATH_TMPDIR', '/tmp'),
('PHONE_HOME', '1'),
('PHONE_HOME_TIMER', ''),
('QUEUE_DELETE_INVALID', '0'),
('QUEUE_DELETE_PROCESSED', '1'),
('QUEUE_PURGE_COLLECTED', '0'),
('SCHEMA_VERSION', '20130817'),
('STATS_GEOIP_COUNTRYDB_V4', '/usr/share/GeoIP/GeoIP.dat'),
('STATS_GEOIP_COUNTRYDB_V6', '/usr/share/GeoIP/GeoIPv6.dat'),
('STATS_GEOIP_LOOKUP', '1'),
('SUBSCRIPTION_ID', ''),
('SUBSCRIPTION_SUPPORT', 'opensource'),
('TIMEZONE_DEFAULT', 'SYSTEM'),
('UPLOAD_MAXBYTES', '5242880');

-- --------------------------------------------------------

--
-- Table structure for table `file_uploads`
--

CREATE TABLE IF NOT EXISTS `file_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customid` int(11) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL,
  `timestamp` bigint(20) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_location` char(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `file_uploads`
--


-- --------------------------------------------------------

--
-- Table structure for table `file_upload_data`
--

CREATE TABLE IF NOT EXISTS `file_upload_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fileid` int(11) NOT NULL DEFAULT '0',
  `data` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table for use as database-backed file storage system' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `file_upload_data`
--


-- --------------------------------------------------------

--
-- Table structure for table `journal`
--

CREATE TABLE IF NOT EXISTS `journal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `journalname` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `customid` int(11) NOT NULL DEFAULT '0',
  `timestamp` bigint(20) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `journalname` (`journalname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `journal`
--


-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `translation` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language` (`language`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=377 ;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES
(292, 'en_us', 'placeholder', 'placeholder'),
(293, 'en_us', 'menu_overview', 'Dashboard'),
(294, 'en_us', 'menu_apps', 'Applications'),
(295, 'en_us', 'menu_queue', 'Incoming Queue'),
(296, 'en_us', 'menu_admin_users', 'User Management'),
(297, 'en_us', 'menu_configuration', 'Configuration'),
(298, 'en_us', 'menu_apps_view', 'View Applications'),
(299, 'en_us', 'menu_apps_add', 'Define Application'),
(300, 'en_us', 'menu_apps_platforms', 'Platforms'),
(301, 'en_us', 'menu_apps_servers', 'Servers'),
(302, 'en_us', 'menu_apps_platforms_view', 'View Platforms'),
(303, 'en_us', 'menu_apps_platforms_add', 'Define Platform'),
(304, 'en_us', 'menu_apps_servers_view', 'View Servers'),
(305, 'en_us', 'menu_apps_servers_add', 'Define Server'),
(306, 'en_us', 'app_name', 'Application Name'),
(307, 'en_us', 'app_platform', 'Platform'),
(308, 'en_us', 'app_description', 'Description'),
(309, 'en_us', 'app_regex', 'Matching Regex'),
(310, 'en_us', 'submit', 'Save Changes'),
(311, 'en_us', 'tbl_lnk_details', 'details'),
(312, 'en_us', 'tbl_lnk_stats_app', 'application statistics'),
(313, 'en_us', 'tbl_lnk_stats_geo', 'geographic statistics'),
(314, 'en_us', 'app_details', 'Details'),
(315, 'en_us', 'platform_name', 'Platform Name'),
(316, 'en_us', 'platform_description', 'Description'),
(317, 'en_us', 'tbl_lnk_stats', 'statistics'),
(318, 'en_us', 'platform_delete', 'Delete Platform'),
(319, 'en_us', 'platform_details', 'Platform Details'),
(320, 'en_us', 'platform_regex', 'Version-matching Regex'),
(321, 'en_us', 'delete_confirm', 'Confirm Deletion'),
(322, 'en_us', 'server_name', 'Server Name/Type'),
(323, 'en_us', 'server_description', 'Description'),
(324, 'en_us', 'tbl_lnk_stats_servers', 'server statistics'),
(325, 'en_us', 'tbl_lnk_stats_os', 'operating system statistics'),
(326, 'en_us', 'server_details', 'Server Details'),
(327, 'en_us', 'server_regex_id', 'Server Name/Type Matching Regex'),
(328, 'en_us', 'server_regex_versions', 'Version-matching Regex'),
(329, 'en_us', 'server_regex_os', 'Operating System Matching Regex'),
(330, 'en_us', 'username', 'Username'),
(331, 'en_us', 'realname', 'Real Name'),
(332, 'en_us', 'contact_email', 'Email Address'),
(333, 'en_us', 'lastlogin_time', 'Last Login'),
(334, 'en_us', 'timestamp', 'Timestamp'),
(335, 'en_us', 'ipaddress', 'IP Address'),
(336, 'en_us', 'app_version', 'Application Version'),
(337, 'en_us', 'server_app', 'Server Name/Type'),
(338, 'en_us', 'server_platform', 'Platform Version'),
(339, 'en_us', 'subscription_type', 'Subscription Type'),
(340, 'en_us', 'subscription_id', 'Subscription Unique ID'),
(341, 'en_us', 'tbl_lnk_permissions', 'permissions'),
(342, 'en_us', 'tbl_lnk_delete', 'delete'),
(343, 'en_us', 'config_queue', 'Queue Configuration'),
(344, 'en_us', 'config_stats', 'Statistics Options'),
(345, 'en_us', 'config_dateandtime', 'Locale Settings'),
(346, 'en_us', 'config_amberstats', 'Amberstats Developer Phone Home'),
(347, 'en_us', 'filter_searchbox', 'Search Box'),
(348, 'en_us', 'lastlogin_ipaddress', 'Last Logged in From'),
(349, 'en_us', 'id_platform', 'Platform'),
(350, 'en_us', 'regex_version_minor', 'Regex match Minor Version'),
(351, 'en_us', 'regex_version_major', 'Regex match Major Version'),
(352, 'en_us', 'regex_serverid', 'Regex match Server Name/Type'),
(353, 'en_us', 'regex_os_type', 'Regex match OS Name/Type'),
(354, 'en_us', 'regex_os_version', 'Regex match OS Version'),
(355, 'en_us', 'help_regex_version_minor', 'Regex to match the entire version string returned by the application.'),
(356, 'en_us', 'help_regex_version_major', 'Regex to match just the major versions from the version strings returned by the application.'),
(357, 'en_us', 'help_regex_serverid', 'Need to match the name/type of server, eg "Apache" or "Nginx"'),
(358, 'en_us', 'help_regex_os_type', 'Need to match the name/type of operating system - generally included in brackets in the server version string, eg (Linux).'),
(359, 'en_us', 'help_regex_os_version', 'Need to match the version of the operating system, if it''s been provided by the server string.'),
(360, 'en_us', 'user_permissions', 'User Permissions'),
(361, 'en_us', 'user_view', 'User Details'),
(362, 'en_us', 'user_delete', 'Delete User'),
(363, 'en_us', 'user_password', 'User Password'),
(364, 'en_us', 'user_info', 'User Details'),
(365, 'en_us', 'user_options', 'User-specific Options'),
(366, 'en_us', 'id_user', 'User ID'),
(367, 'en_us', 'password', 'Password'),
(368, 'en_us', 'password_confirm', 'Password (Confirm)'),
(369, 'en_us', 'option_lang', 'Language'),
(370, 'en_us', 'option_dateformat', 'Locale'),
(371, 'en_us', 'option_shrink_tableoptions', 'Table Options'),
(372, 'en_us', 'option_debug', 'Debug Logging'),
(373, 'en_us', 'option_concurrent_logins', 'Concurrent Logins'),
(374, 'en_us', 'filter_searchbox', 'Search Box'),
(375, 'en_us', 'lastlogin_ipaddress', 'Last Logged in From'),
(376, 'en_us', 'server_delete', 'Delete Server');

-- --------------------------------------------------------

--
-- Table structure for table `language_avaliable`
--

CREATE TABLE IF NOT EXISTS `language_avaliable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `language_avaliable`
--

INSERT INTO `language_avaliable` (`id`, `name`) VALUES
(1, 'en_us');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL DEFAULT '0',
  `parent` varchar(50) NOT NULL,
  `topic` varchar(50) NOT NULL,
  `link` varchar(50) NOT NULL,
  `permid` int(11) NOT NULL DEFAULT '0',
  `config` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`, `config`) VALUES
(1, 100, 'top', 'menu_overview', 'home.php', 0, ''),
(2, 500, 'top', 'menu_apps', 'apps/apps.php', 2, ''),
(3, 501, 'menu_apps', 'menu_apps_view', 'apps/apps.php', 2, ''),
(4, 502, 'menu_apps', 'menu_apps_add', 'apps/add.php', 2, ''),
(5, 510, 'menu_apps_view', '', 'apps/view.php', 2, ''),
(6, 510, 'menu_apps_view', '', 'apps/stats.php', 2, ''),
(7, 510, 'menu_apps_view', '', 'apps/delete.php', 2, ''),
(8, 520, 'menu_apps', 'menu_apps_platforms', 'platforms/platforms.php', 2, ''),
(9, 521, 'menu_apps_platforms', 'menu_apps_platforms_view', 'platforms/platforms.php', 2, ''),
(10, 522, 'menu_apps_platforms', 'menu_apps_platforms_add', 'platforms/add.php', 2, ''),
(11, 522, 'menu_apps_platforms_view', '', 'platforms/view.php', 2, ''),
(12, 522, 'menu_apps_platforms_view', '', 'platforms/delete.php', 2, ''),
(13, 920, 'top', 'menu_admin_users', 'user/users.php', 2, 'AUTH_METHOD=sql'),
(14, 921, 'menu_admin_users', '', 'user/user-view.php', 2, 'AUTH_METHOD=sql'),
(15, 921, 'menu_admin_users', '', 'user/user-permissions.php', 2, 'AUTH_METHOD=sql'),
(16, 921, 'menu_admin_users', '', 'user/user-delete.php', 2, 'AUTH_METHOD=sql'),
(17, 921, 'menu_admin_users', '', 'user/user-add.php', 2, 'AUTH_METHOD=sql'),
(18, 921, 'top', 'menu_configuration', 'admin/config.php', 2, ''),
(20, 530, 'menu_apps', 'menu_apps_servers', 'servers/servers.php', 2, ''),
(21, 531, 'menu_apps_servers', 'menu_apps_servers_view', 'servers/servers.php', 2, ''),
(22, 532, 'menu_apps_servers', 'menu_apps_servers_add', 'servers/add.php', 2, ''),
(23, 532, 'menu_apps_servers_view', '', 'servers/view.php', 2, ''),
(24, 532, 'menu_apps_servers_view', '', 'servers/delete.php', 2, ''),
(25, 522, 'menu_apps_platforms_view', '', 'platforms/stats.php', 2, ''),
(26, 532, 'menu_apps_servers_view', '', 'servers/stats.php', 2, ''),
(27, 600, 'top', 'menu_queue', 'queue/queue.php', 2, ''),
(28, 532, 'menu_apps_servers_view', '', 'servers/osstats.php', 2, ''),
(29, 510, 'menu_apps_view', '', 'apps/geostats.php', 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores all the possible permissions' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `value`, `description`) VALUES
(1, 'disabled', 'Enabling the disabled permission will prevent the user from being able to login.'),
(2, 'admin', 'Provides access to user and configuration management features (note: any user with admin can provide themselves with access to any other section of this program)');

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_app` int(10) unsigned NOT NULL,
  `id_app_version` int(10) unsigned NOT NULL,
  `id_server_version` int(10) unsigned NOT NULL,
  `id_platform_version` int(10) unsigned NOT NULL,
  `id_country` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `subscription_type` varchar(20) NOT NULL,
  `subscription_id` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_app_versions`
--

CREATE TABLE IF NOT EXISTS `stats_app_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_app` int(10) unsigned NOT NULL,
  `version_major` varchar(20) NOT NULL,
  `version_minor` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_app_versions`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_country`
--

CREATE TABLE IF NOT EXISTS `stats_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` char(2) NOT NULL,
  `country_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_country`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_incoming`
--

CREATE TABLE IF NOT EXISTS `stats_incoming` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` bigint(20) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `app_name` varchar(50) NOT NULL,
  `app_version` varchar(20) NOT NULL,
  `server_app` varchar(50) NOT NULL,
  `server_platform` varchar(20) NOT NULL,
  `subscription_type` varchar(20) NOT NULL,
  `subscription_id` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_incoming`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_platform_versions`
--

CREATE TABLE IF NOT EXISTS `stats_platform_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_platform` int(10) unsigned NOT NULL,
  `version_major` varchar(20) NOT NULL,
  `version_minor` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_platform_versions`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats_server_versions`
--

CREATE TABLE IF NOT EXISTS `stats_server_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_server` int(10) unsigned NOT NULL,
  `os_type` varchar(20) NOT NULL,
  `os_version` varchar(20) NOT NULL,
  `version_major` varchar(20) NOT NULL,
  `version_minor` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `stats_server_versions`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `password_salt` varchar(20) NOT NULL DEFAULT '',
  `contact_email` varchar(255) NOT NULL DEFAULT '',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `ipaddress` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `ipaddress` (`ipaddress`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='User authentication system.' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `realname`, `password`, `password_salt`, `contact_email`, `time`, `ipaddress`) VALUES
(1, 'setup', 'Setup Account', '14c2a5c3681b95582c3e01fc19f49853d9cdbb31', 'hctw8lbz3uhxl6sj8ixr', 'support@amberdms.com', 1379774701, '');

-- --------------------------------------------------------

--
-- Table structure for table `users_blacklist`
--

CREATE TABLE IF NOT EXISTS `users_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(15) NOT NULL,
  `failedcount` int(11) NOT NULL DEFAULT '0',
  `time` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Prevents automated login attacks.' AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users_blacklist`
--


-- --------------------------------------------------------

--
-- Table structure for table `users_options`
--

CREATE TABLE IF NOT EXISTS `users_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=197 ;

--
-- Dumping data for table `users_options`
--

INSERT INTO `users_options` (`id`, `userid`, `name`, `value`) VALUES
(191, 1, 'lang', 'en_us'),
(192, 1, 'dateformat', 'yyyy-mm-dd'),
(193, 1, 'shrink_tableoptions', 'on'),
(194, 1, 'default_employeeid', ''),
(195, 1, 'debug', ''),
(196, 1, 'concurrent_logins', 'on');

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `permid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user permissions.' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users_permissions`
--

INSERT INTO `users_permissions` (`id`, `userid`, `permid`) VALUES
(1, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users_sessions`
--

CREATE TABLE IF NOT EXISTS `users_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `authkey` varchar(40) NOT NULL,
  `ipv4` varchar(15) NOT NULL DEFAULT '',
  `ipv6` varchar(255) NOT NULL DEFAULT '',
  `time` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users_sessions`
--

