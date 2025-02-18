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

return true;
