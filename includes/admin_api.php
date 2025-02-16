<?php

class API {
	public static $ipTV_db = null;
	public static $rSettings = array();
	public static $rServers = array();
	public static $rUserInfo = array();

	public static function init($rUserID = null) {
		self::$rSettings = ipTV_lib::getSettings();
		self::$rServers = getStreamingServers();

		if (!$rUserID && isset($_SESSION['hash'])) {
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
			case 'processServer':
				return !empty($rData['server_name']) && !empty($rData['server_ip']);
		}

		return true;
	}
	public static function installServer($rData) {
		if (hasPermissions('adv', 'add_server')) {
			if (self::checkMinimumRequirements($rData)) {
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
				$rArray = verifyPostTable('servers', $rData);
				$rArray['status'] = 3;
				unset($rArray['id']);

				if (strlen($rArray['server_ip']) != 0 && filter_var($rArray['server_ip'], FILTER_VALIDATE_IP)) {
					$rArray['network_interface'] = 'auto';

					$rPrepare = prepareArray($rArray);

					$rQuery = 'INSERT INTO `servers`(' . $rPrepare['columns'] . ') VALUES(' . $rPrepare['placeholder'] . ');';

					if (self::$ipTV_db->query($rQuery, ...$rPrepare['data'])) {
						$rInsertID = self::$ipTV_db->last_insert_id();

						//Create user and add permisions
						$userBD = "lb_" . intval($rInsertID);
						self::$ipTV_db->query("CREATE USER `" . $userBD . "`@`" . $rArray["server_ip"] . "`;");
						self::$ipTV_db->query("GRANT ALL PRIVILEGES ON xc_vm.* TO `" . $userBD . "`@`" . $rArray["server_ip"] . "` WITH GRANT OPTION;");
						self::$ipTV_db->query("FLUSH PRIVILEGES;");
						$rCommand = PHP_BIN . ' ' . CLI_PATH . 'balancer.php ' . intval($rData['type']) . ' ' . intval($rInsertID) . ' ' . intval($rData['ssh_port']) . ' ' . escapeshellarg($rData['root_username']) . ' ' . escapeshellarg($rData['root_password']) . ' ' . $rArray["http_broadcast_port"] . ' ' . $rArray["https_broadcast_port"] . ' ' . intval($rUpdateSysctl) . ' > "' . BIN_PATH . 'install/' . intval($rInsertID) . '.install" 2>/dev/null &';
						shell_exec($rCommand);
						return array('status' => STATUS_SUCCESS, 'data' => array('insert_id' => $rInsertID));
					}
					return array('status' => STATUS_FAILURE, 'data' => $rData);
				}
				return array('status' => STATUS_INVALID_IP, 'data' => $rData);
			}
			return array('status' => STATUS_INVALID_INPUT, 'data' => $rData);
		}
		exit();
	}

	public static function processServer($rData) {
		if (!hasPermissions('adv', 'edit_server')) {
			exit();
		}

		if (!self::checkMinimumRequirements($rData)) {
			return ['status' => STATUS_INVALID_INPUT, 'data' => $rData];
		}

		$rServer = getStreamingServersByID($rData['edit']);
		if (!$rServer) {
			return ['status' => STATUS_INVALID_INPUT, 'data' => $rData];
		}

		$rArray = verifyPostTable('servers', $rData, true);
		$rArray['http_broadcast_port'] = $rData['http_broadcast_port'];
		$rArray['https_broadcast_port'] = $rData['https_broadcast_port'];

		foreach (['http_broadcast_port', 'https_broadcast_port'] as $key) {
			unset($rData[$key]);
		}

		foreach (['enable_gzip', 'enable_geoip', 'timeshift_only', 'enable_https', 'enable_isp', 'enabled', 'enable_proxy'] as $rKey) {
			$rArray[$rKey] = isset($rData[$rKey]) ? 1 : 0;
		}

		if ($rServer['is_main']) {
			$rArray['enabled'] = 1;
		}

		$rArray['geoip_countries'] = isset($rData['geoip_countries']) ? $rData['geoip_countries'] : [];
		if (isset($rData['isp_names'])) {
			$rArray['isp_names'] = array();

			foreach ($rData['isp_names'] as $rISP) {
				$rArray['isp_names'][] = strtolower(trim(preg_replace('/[^A-Za-z0-9 ]/', '', $rISP)));
			}
		} else {
			$rArray['isp_names'] = array();
		}

		if (strlen($rData['server_ip']) > 0 && filter_var($rData['server_ip'], FILTER_VALIDATE_IP)) {
			if (strlen($rData['private_ip']) <= 0 || filter_var($rData['private_ip'], FILTER_VALIDATE_IP)) {
				$rArray['total_services'] = $rData['total_services'];
				$rPrepare = prepareArray($rArray);
				$rPrepare['data'][] = $rData['edit'];
				$rQuery = 'UPDATE `servers` SET ' . $rPrepare['update'] . ' WHERE `id` = ?;';

				if (self::$ipTV_db->query($rQuery, ...$rPrepare['data'])) {
					$rInsertID = $rData['edit'];

					changePort($rInsertID, 0, $rArray['http_broadcast_port'], false);
					changePort($rInsertID, 1, $rArray['https_broadcast_port'], false);
					changePort($rInsertID, 2, $rArray['rtmp_port'], false);
					setServices($rInsertID, intval($rArray['total_services']), true);

					if (!empty($rArray['sysctl'])) {
						setSysctl($rInsertID, $rArray['sysctl']);
					}

					if (file_exists(CACHE_TMP_PATH . 'servers')) {
						unlink(CACHE_TMP_PATH . 'servers');
					}

					return ['status' => STATUS_SUCCESS, 'data' => ['insert_id' => $rInsertID]];
				} else {
					return ['status' => STATUS_FAILURE, 'data' => $rData];
				}
			} else {
				return ['status' => STATUS_INVALID_IP, 'data' => $rData];
			}
		} else {
			return ['status' => STATUS_INVALID_IP, 'data' => $rData];
		}
	}
	public static function editSettings($rData) {
		if (self::checkMinimumRequirements($rData)) {
			$rArray = getSettings();

			foreach (array("active_mannuals", "allow_cdn_access", "always_enabled_subtitles", "audio_restart_loss", "block_proxies", "block_streaming_servers", "block_svp", "case_sensitive_line", "change_own_dns", "change_own_email", "change_own_lang", "change_own_password", "change_usernames", "client_logs_save", "cloudflare", "county_override_1st", "dashboard_stats", "dashboard_world_map_activity", "dashboard_world_map_live", "debug_show_errors", "detect_restream_block_user", "disable_hls", "disable_hls_allow_restream", "disable_mag_token", "disable_ministra", "disable_trial", "disable_ts", "disable_ts_allow_restream", "disallow_2nd_ip_con", "disallow_empty_user_agents", "download_images", "enable_connection_problem_indication", "enable_debug_stalker", "enable_isp_lock", "encrypt_hls", "encrypt_playlist", "encrypt_playlist_restreamer", "ffmpeg_warnings", "ignore_invalid_users", "ignore_keyframes", "ip_logout", "ip_subnet_match", "kill_rogue_ffmpeg", "mag_disable_ssl", "mag_keep_extension", "mag_legacy_redirect", "mag_security", "monitor_connection_status", "on_demand_failure_exit", "on_demand_instant_off", "ondemand_balance_equal", "playlist_from_mysql", "priority_backup", "recaptcha_enable", "reseller_can_isplock", "reseller_mag_events", "reseller_reset_isplock", "reseller_restrictions", "restart_php_fpm", "restream_deny_unauthorised", "restrict_playlists", "restrict_same_ip", "rtmp_random", "save_closed_connection", "save_restart_logs", "show_all_category_mag", "show_banned_video", "show_channel_logo_in_preview", "show_expired_video","show_expiring_video", "show_isps", "show_not_on_air_video", "show_tv_channel_logo", "stb_change_pass", "stream_logs_save", "use_buffer", "use_mdomain_in_lists", ) as $rSetting) {
				if (isset($rData[$rSetting])) {
					$rArray[$rSetting] = 1;
					unset($rData[$rSetting]);
				} else {
					$rArray[$rSetting] = 0;
				}
			}

			if (!isset($rData['allowed_stb_types_for_local_recording'])) {
				$rArray['allowed_stb_types_for_local_recording'] = array();
			}

			if (!isset($rData['allowed_stb_types'])) {
				$rArray['allowed_stb_types'] = array();
			}

			if (!isset($rData['allow_countries'])) {
				$rArray['allow_countries'] = array('ALL');
			}

			// if ($rArray['mag_legacy_redirect']) {
			// 	if (!file_exists(MAIN_DIR . 'wwwdir/c/')) {
			// 		self::$db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', SERVER_ID, time(), json_encode(array('action' => 'enable_ministra')));
			// 	}
			// } else {
			// 	if (file_exists(MAIN_DIR . 'wwwdir/c/')) {
			// 		self::$db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', SERVER_ID, time(), json_encode(array('action' => 'disable_ministra')));
			// 	}
			// }

			foreach ($rData as $rKey => $rValue) {
				if (isset($rArray[$rKey])) {
					$rArray[$rKey] = $rValue;
				}
			}

			if (ipTV_lib::setSettings($rArray)) {
				clearSettingsCache();
				return array('status' => STATUS_SUCCESS);
			} else {
				return array('status' => STATUS_FAILURE);
			}
		} else {
			return array('status' => STATUS_INVALID_INPUT, 'data' => $rData);
		}
	}

}
