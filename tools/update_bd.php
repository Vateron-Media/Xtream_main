<?php
set_time_limit(0);
require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';

// create table mysql_syslog
$ipTV_db->query("CREATE TABLE IF NOT EXISTS `mysql_syslog` (`id` int(11) NOT NULL AUTO_INCREMENT, `type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,`error` longtext COLLATE utf8_unicode_ci, `username` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL, `ip` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL, `database` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL, `date` int(11) DEFAULT NULL, `server_id` tinyint(4) DEFAULT '1', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");


return true;
