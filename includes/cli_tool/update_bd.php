<?php
set_time_limit(0);
require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';

$ipTV_db->query("SELECT * FROM `settings` WHERE `name` = 'disable_ministra';");
if ($ipTV_db->num_rows() == 0) {
    $ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('disable_ministra', '0')");
}

$ipTV_db->query("SELECT * FROM `settings` WHERE `name` = 'show_expiring_video';");
if ($ipTV_db->num_rows() == 0) {
    $ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('show_expiring_video', '1')");
}

$ipTV_db->query("SELECT * FROM `settings` WHERE `name` = 'expired_video_path';");
if ($ipTV_db->num_rows() == 0) {
    $ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('expired_video_path', '')");
}

$ipTV_db->query("SELECT * FROM `settings` WHERE `name` = 'update_chanel';");
if ($ipTV_db->num_rows() == 0) {
    $ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('update_chanel', 'stable')");
}

$ipTV_db->query("SHOW COLUMNS FROM `streams` LIKE 'llod';");
if ($ipTV_db->num_rows() == 0) {
    $ipTV_db->query("ALTER TABLE `streams` ADD COLUMN `llod` tinyint(4) DEFAULT '0'");
}

$ipTV_db->query("SELECT * FROM `crontab` WHERE `filename` = 'streams_logs.php';");
if ($ipTV_db->num_rows() == 0) {
    $ipTV_db->query("INSERT INTO `crontab` (`filename`, `time`, `enabled`) VALUES ('streams_logs.php', '* * * * *', 1)");
}

$ipTV_db->query("DROP TABLE `stream_logs`;");
$ipTV_db->query("CREATE TABLE IF NOT EXISTS `stream_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `stream_id` int(11) DEFAULT NULL,
    `server_id` int(11) DEFAULT NULL,
    `action` varchar(500) DEFAULT NULL,
    `source` varchar(1024) DEFAULT NULL,
    `date` int(11) DEFAULT NULL,
    `error` varchar(500) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `stream_id` (`stream_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

return true;
