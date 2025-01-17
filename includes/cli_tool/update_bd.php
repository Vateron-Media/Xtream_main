<?php
set_time_limit(0);
require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';

$ipTV_db->query("CREATE TABLE IF NOT EXISTS `servers_stats` (`id` int(11) NOT NULL AUTO_INCREMENT, `server_id` int(11) DEFAULT '0', `connections` int(11) DEFAULT '0', `streams` int(11) DEFAULT '0', `users` int(11) DEFAULT '0', `cpu` float DEFAULT '0', `cpu_cores` int(11) DEFAULT '0', `cpu_avg` float DEFAULT '0', `total_mem` int(11) DEFAULT '0', `total_mem_free` int(11) DEFAULT '0', `total_mem_used` int(11) DEFAULT '0', `total_mem_used_percent` float DEFAULT '0', `total_disk_space` bigint(20) DEFAULT '0', `uptime` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, `total_running_streams` int(11) DEFAULT '0', `bytes_sent` bigint(20) DEFAULT '0', `bytes_received` bigint(20) DEFAULT '0', `bytes_sent_total` bigint(128) DEFAULT '0', `bytes_received_total` bigint(128) DEFAULT '0', `cpu_load_average` float DEFAULT '0', `gpu_info` mediumtext COLLATE utf8_unicode_ci, `iostat_info` mediumtext COLLATE utf8_unicode_ci, `time` int(16) DEFAULT '0', `total_users` int(11) DEFAULT '0', PRIMARY KEY (`id`) USING BTREE) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
$ipTV_db->query("CREATE TABLE IF NOT EXISTS `queue` (`id` int(11) NOT NULL AUTO_INCREMENT, `type` varchar(32) DEFAULT NULL, `server_id` int(11) DEFAULT NULL, `stream_id` int(11) DEFAULT NULL, `pid` int(11) DEFAULT NULL, `added` int(11) DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
$ipTV_db->query("CREATE TABLE IF NOT EXISTS `rtmp_ips` (`id` int(11) NOT NULL AUTO_INCREMENT, `ip` varchar(255) DEFAULT NULL, `password` varchar(128) DEFAULT NULL, `notes` mediumtext, `push` tinyint(1) DEFAULT NULL, `pull` tinyint(1) DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `ip` (`ip`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `sysctl` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `video_devices` mediumtext COLLATE utf8_unicode_ci");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `audio_devices` mediumtext COLLATE utf8_unicode_ci");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `gpu_info` mediumtext COLLATE utf8_unicode_ci");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `limit_requests` INT(11) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `enable_gzip` TINYINT(1) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `requests_per_second` INT(11) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `connections` INT(16) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `users` INT(16) NULL DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streaming_servers` ADD COLUMN `server_type` int(1) DEFAULT '0';");

$ipTV_db->query("ALTER TABLE `streaming_servers` ALTER http_broadcast_port SET DEFAULT 25461;");
$ipTV_db->query("ALTER TABLE `streaming_servers` ALTER https_broadcast_port SET DEFAULT 25463;");
$ipTV_db->query("ALTER TABLE `streaming_servers` ALTER total_clients SET DEFAULT 250;");
$ipTV_db->query("ALTER TABLE `streaming_servers` ALTER network_interface SET DEFAULT 'auto';");
$ipTV_db->query("ALTER TABLE `streaming_servers` ALTER rtmp_port SET DEFAULT 25462;");
$ipTV_db->query("ALTER TABLE `streaming_servers` ALTER network_guaranteed_speed SET DEFAULT 1000;");

$ipTV_db->query("ALTER TABLE `streams` ADD COLUMN `fps_restart` tinyint(1) DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streams` ADD COLUMN `vframes_server_id` int(11) DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streams` ADD COLUMN `vframes_pid` int(11) DEFAULT '0';");
$ipTV_db->query("ALTER TABLE `streams` ADD COLUMN `year` int(4) DEFAULT NULL;");

$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('restart_php_fpm', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('max_encode_movies', '10')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('max_encode_cc', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('queue_loop', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('redis_password', '')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('redis_handler', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('cache_playlists', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('php_loopback', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('pass_length', '8')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('disable_trial', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('automatic_backups', 'off')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('cc_time', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('recaptcha_v2_secret_key', '')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('recaptcha_v2_site_key', '')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('recaptcha_enable', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('total_users', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('request_prebuffer', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('playlist_from_mysql', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('cloudflare', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('backups_pid', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('dashboard_stats_frequency', '600')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('login_flood', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('backups_to_keep', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('release_parser', 'python')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('tmdb_language', '')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('default_entries', '10')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('reseller_mag_events', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('ip_logout', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('alternate_scandir', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('download_images', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('auto_refresh', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('local_api', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('dark_mode_login', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('dashboard_stats', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('dashboard_world_map_live', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('dashboard_world_map_activity', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('change_usernames', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('change_own_dns', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('change_own_email', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('change_own_password', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('reseller_restrictions', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('change_own_lang', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('active_mannuals', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('reseller_can_isplock', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('reseller_reset_isplock', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('watch_pid', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('tmdb_pid', '')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('stats_pid', '')");
$ipTV_db->query("DELETE FROM `settings` WHERE `name`='userpanel_mainpage'");
$ipTV_db->query("DELETE FROM `settings` WHERE `name`='page_mannuals'");

$ipTV_db->query("DROP TABLE admin_settings;");

$ipTV_db->query("UPDATE `crontab` SET `filename`='series.php' WHERE `filename`='vod_cc_series.php'");
$ipTV_db->query("UPDATE `crontab` SET `filename`='backups.php' WHERE `filename`='auto_backups.php'");

$ipTV_db->query("SHOW TABLES LIKE 'admin_settings';");
if ($ipTV_db->num_rows() > 0) {
    $rAdminSettings = array();
    $ipTV_db->query('SELECT * FROM `admin_settings`;');
    foreach ($ipTV_db->get_rows() as $rRow) {
        $rAdminSettings[$rRow['type']] = $rRow['value'];
    }
    if (0 < strlen($rAdminSettings['recaptcha_v2_secret_key']) && 0 < strlen($rAdminSettings['recaptcha_v2_site_key'])) {
        ipTV_lib::setSettings(["recaptcha_v2_secret_key" => $rAdminSettings['recaptcha_v2_secret_key']]);
        ipTV_lib::setSettings(["recaptcha_v2_site_key" => $rAdminSettings['recaptcha_v2_site_key']]);
    }
}

return true;
