<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
	include "/home/xtreamcodes/admin/functions.php";

	$rPID = getmypid();
	if (isset($rAdminSettings["stats_pid"])) {
		if ((file_exists("/proc/" . $rAdminSettings["stats_pid"])) && (strlen($rAdminSettings["stats_pid"]) > 0)) {
			exit;
		} else {
			$ipTV_db_admin->query("UPDATE `admin_settings` SET `value` = " . intval($rPID) . " WHERE `type` = 'stats_pid';");
		}
	} else {
		$ipTV_db_admin->query("INSERT INTO `admin_settings`(`type`, `value`) VALUES('stats_pid', " . intval($rPID) . ");");
	}

	$rAdminSettings = getAdminSettings();
	$rSettings = getSettings();

	$rTimeout = 3000;       // Limit by time.
	set_time_limit($rTimeout);
	ini_set('max_execution_time', $rTimeout);

	$rStatistics = array("users" => array(), "conns" => array());
	$rPeriod = intval($rAdminSettings["dashboard_stats_frequency"]) ?: 600;

	if (($rPeriod >= 60) && ($rAdminSettings["dashboard_stats"])) {
		$ipTV_db_admin->query("SELECT MIN(`date_start`) AS `min` FROM `user_activity`;");
		$rMin = roundUpToAny(intval($ipTV_db_admin->get_row()["min"]), $rPeriod);
		$ipTV_db_admin->query("SELECT MAX(`time`) AS `max` FROM `dashboard_statistics` WHERE `type` IN ('users', 'conns');");
		$rMinProc = roundUpToAny(intval($ipTV_db_admin->get_row()["max"]), $rPeriod);
		if ($rMinProc > $rMin) {
			$rMin = $rMinProc - ($rPeriod * 3);
		}
		$rRange = range($rMin, roundUpToAny(time(), $rPeriod), $rPeriod);
		foreach ($rRange as $rDate) {
			$rCount = 0;
			$ipTV_db_admin->query("SELECT COUNT(`activity_id`) AS `count` FROM `user_activity` WHERE `date_start` <= " . intval($rDate) . " AND `date_end` >= " . intval($rDate) . ";");
			$rCount += $ipTV_db_admin->get_row()["count"];
			$ipTV_db_admin->query("SELECT COUNT(`activity_id`) AS `count` FROM `lines_live` WHERE `date_start` <= " . intval($rDate) . ";");
			$rCount += $ipTV_db_admin->get_row()["count"];
			$rStatistics["conns"][] = array(intval($rDate), $rCount);
			$rCount = 0;
			$ipTV_db_admin->query("SELECT COUNT(DISTINCT(`activity_id`)) AS `count` FROM `user_activity` WHERE `date_start` <= " . intval($rDate) . " AND `date_end` >= " . intval($rDate) . ";");
			$rCount += $ipTV_db_admin->get_row()["count"];
			$ipTV_db_admin->query("SELECT COUNT(DISTINCT(`activity_id`)) AS `count` FROM `lines_live` WHERE `date_start` <= " . intval($rDate) . ";");
			$rCount += $ipTV_db_admin->get_row()["count"];
			$rStatistics["users"][] = array(intval($rDate), $rCount);
		}
		$ipTV_db_admin->query("DELETE FROM `dashboard_statistics` WHERE `type` IN ('users', 'conns') AND `time` >= " . intval($rMin) . ";");
		foreach ($rStatistics as $rType => $rData) {
			foreach ($rData as $rValue) {
				$ipTV_db_admin->query("INSERT INTO `dashboard_statistics`(`type`, `time`, `count`) VALUES(?,?,?);", $ipTV_db_admin->escape($rType), intval($rValue[0]), intval($rValue[1]));
			}
		}
	}
} else {
	exit('Please run as XC_VM!' . "\n");
}
