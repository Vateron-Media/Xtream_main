<?php
set_time_limit(0);
require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';

$ipTV_db->query("CREATE TABLE IF NOT EXISTS `servers_stats` (`id` int(11) NOT NULL AUTO_INCREMENT, `server_id` int(11) DEFAULT '0', `connections` int(11) DEFAULT '0', `streams` int(11) DEFAULT '0', `users` int(11) DEFAULT '0', `cpu` float DEFAULT '0', `cpu_cores` int(11) DEFAULT '0', `cpu_avg` float DEFAULT '0', `total_mem` int(11) DEFAULT '0', `total_mem_free` int(11) DEFAULT '0', `total_mem_used` int(11) DEFAULT '0', `total_mem_used_percent` float DEFAULT '0', `total_disk_space` bigint(20) DEFAULT '0', `uptime` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, `total_running_streams` int(11) DEFAULT '0', `bytes_sent` bigint(20) DEFAULT '0', `bytes_received` bigint(20) DEFAULT '0', `bytes_sent_total` bigint(128) DEFAULT '0', `bytes_received_total` bigint(128) DEFAULT '0', `cpu_load_average` float DEFAULT '0', `gpu_info` mediumtext COLLATE utf8_unicode_ci, `iostat_info` mediumtext COLLATE utf8_unicode_ci, `time` int(16) DEFAULT '0', `total_users` int(11) DEFAULT '0', PRIMARY KEY (`id`) USING BTREE) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
$ipTV_db->query("ALTER TABLE streaming_servers ADD COLUMN `sysctl` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL");
$ipTV_db->query("ALTER TABLE streaming_servers ADD COLUMN `video_devices` mediumtext COLLATE utf8_unicode_ci");
$ipTV_db->query("ALTER TABLE streaming_servers ADD COLUMN `audio_devices` mediumtext COLLATE utf8_unicode_ci");
$ipTV_db->query("ALTER TABLE streaming_servers ADD COLUMN `gpu_info` mediumtext COLLATE utf8_unicode_ci");
$ipTV_db->query("ALTER TABLE streaming_servers ADD COLUMN `limit_requests` INT(11) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE streaming_servers ADD COLUMN `enable_gzip` TINYINT(1) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE settings ADD COLUMN `restart_php_fpm` TINYINT(4) NULL DEFAULT '1'");




return true;
