<?php
set_time_limit(0);
require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';

$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('disable_ministra', '0')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('show_expiring_video', '1')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('expired_video_path', '')");
$ipTV_db->query("INSERT INTO `settings` (`name`, `value`) VALUES ('update_chanel', 'stable')");


$ipTV_db->query("ALTER TABLE `streams` ADD COLUMN `llod` tinyint(4) DEFAULT '0'");

return true;
