<?php

class API {
	public static $ipTV_db = null;
	public static $rSettings = array();
	public static $rServers = array();
	public static $rUserInfo = array();

	public static function init($rUserID = null) {
		self::$rSettings = ipTV_lib::getSettings();
		self::$rServers = getStreamingServers();

		if (!$rUserID || isset($_SESSION['hash'])) {
			$rUserID = $_SESSION['hash'];
		}

		if ($rUserID) {
			self::$rUserInfo = getRegisteredUser($rUserID);
		}
	}
	private static function checkMinimumRequirements($rData) {
		switch (debug_backtrace()[1]['function']) {
			case 'installServer':
				return !empty($rData['ssh_port']) && !empty($rData['root_password']);
		}

		return true;
	}
	public static function installServer($rData) {
		if (self::checkMinimumRequirements($rData)) {
			if (hasPermissions('adv', 'add_server')) {
				if (isset($rData['update_sysctl'])) {
					$rUpdateSysctl = 1;
				} else {
					$rUpdateSysctl = 0;
				}

				// if (isset($rData['edit'])) {
				// 	if (isset($rData['update_only'])) {
				// 		$rData['type'] = 3;
				// 	}
				// 	$rServer = self::$rServers[$rData['edit']];
				// 	if (!$rServer) {
				// 		return array('status' => STATUS_FAILURE, 'data' => $rData);
				// 	}
				// 	self::$ipTV_db->query('UPDATE `servers` SET `status` = 3, `parent_id` = ? WHERE `id` = ?; ', '[' . implode(',', $rParentIDs) . ']', $rServer['id']);

				// 	$rCommand = PHP_BIN . ' ' . CLI_PATH . 'balancer.php ' . intval($rData['type']) . ' ' . intval($rServer['id']) . ' ' . intval($rData['ssh_port']) . ' ' . escapeshellarg($rData['root_username']) . ' ' . escapeshellarg($rData['root_password']) . ' 80 443 ' . intval($rUpdateSysctl) . ' > "' . BIN_PATH . 'install/' . intval($rServer['id']) . '.install" 2>/dev/null &';

				// 	shell_exec($rCommand);
				// 	return array('status' => STATUS_SUCCESS, 'data' => array('insert_id' => $rServer['id']));
				// }
				$rData['can_delete'] = 1;
				$rArray = verifyPostTable('streaming_servers', $rData);
				$rArray['status'] = 3;
				unset($rArray['id']);

				if (strlen($rArray['server_ip']) != 0 && filter_var($rArray['server_ip'], FILTER_VALIDATE_IP)) {
					$rArray['network_interface'] = 'auto';

					$rPrepare = prepareArray($rArray);

					$rQuery = 'INSERT INTO `streaming_servers`(' . $rPrepare['columns'] . ') VALUES(' . $rPrepare['placeholder'] . ');';

					if (self::$ipTV_db->query($rQuery, ...$rPrepare['data'])) {
						$rInsertID = self::$ipTV_db->last_insert_id();

						//Create user and add permisions
						$userBD = "lb_" . intval($rInsertID);
						self::$ipTV_db->query("CREATE USER `" . $userBD . "`@`" . $rArray["server_ip"] . "`;");
						self::$ipTV_db->query("GRANT ALL PRIVILEGES ON xtream_iptvpro.* TO `" . $userBD . "`@`" . $rArray["server_ip"] . "` WITH GRANT OPTION;");
						self::$ipTV_db->query("FLUSH PRIVILEGES;");
						$rCommand = PHP_BIN . ' ' . CLI_PATH . 'balancer.php ' . intval($rData['type']) . ' ' . intval($rInsertID) . ' ' . intval($rData['ssh_port']) . ' ' . escapeshellarg($rData['root_username']) . ' ' . escapeshellarg($rData['root_password']) . ' ' . $rArray["http_broadcast_port"] . ' ' . $rArray["https_broadcast_port"] . ' ' . intval($rUpdateSysctl) . ' > "' . BIN_PATH . 'install/' . intval($rInsertID) . '.install" 2>/dev/null &';
						shell_exec($rCommand);
						return array('status' => STATUS_SUCCESS, 'data' => array('insert_id' => $rInsertID));
					}
					return array('status' => STATUS_FAILURE, 'data' => $rData);
				}
				return array('status' => STATUS_INVALID_IP, 'data' => $rData);
			}
			exit();
		}
		return array('status' => STATUS_INVALID_INPUT, 'data' => $rData);
	}
}
