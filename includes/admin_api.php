<?php

class AdminAPI {
	public static $ipTV_db = null;
	public static $rSettings = array();
	public static $rServers = array();
	public static $rUserInfo = array();

	public static function init($rUserID = null) {
		self::$rSettings = CoreUtilities::getSettings();
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
			case 'processChannel':
			case 'processStream':
			case 'processMovie':
			case 'processRadio':
				return !empty($rData['stream_display_name']) || isset($rData['review']) || isset($_FILES['m3u_file']);
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
				$rData['total_clients'] = 1000;
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

			foreach (array("active_mannuals", "allow_cdn_access", "always_enabled_subtitles", "audio_restart_loss", "block_proxies", "block_streaming_servers", "block_svp", "case_sensitive_line", "change_own_dns", "change_own_email", "change_own_lang", "change_own_password", "change_usernames", "client_logs_save", "cloudflare", "county_override_1st", "dashboard_stats", "dashboard_world_map_activity", "dashboard_world_map_live", "debug_show_errors", "detect_restream_block_user", "disable_hls", "disable_hls_allow_restream", "disable_mag_token", "disable_ministra", "disable_trial", "disable_ts", "disable_ts_allow_restream", "disallow_2nd_ip_con", "disallow_empty_user_agents", "download_images", "enable_connection_problem_indication", "enable_debug_stalker", "enable_isp_lock", "encrypt_hls", "encrypt_playlist", "encrypt_playlist_restreamer", "ffmpeg_warnings", "ignore_invalid_users", "ignore_keyframes", "ip_logout", "ip_subnet_match", "kill_rogue_ffmpeg", "mag_disable_ssl", "mag_keep_extension", "mag_legacy_redirect", "mag_security", "monitor_connection_status", "on_demand_failure_exit", "on_demand_instant_off", "ondemand_balance_equal", "playlist_from_mysql", "priority_backup", "recaptcha_enable", "reseller_can_isplock", "reseller_mag_events", "reseller_reset_isplock", "reseller_restrictions", "restart_php_fpm", "restream_deny_unauthorised", "restrict_playlists", "restrict_same_ip", "rtmp_random", "save_closed_connection", "save_restart_logs", "show_all_category_mag", "show_banned_video", "show_channel_logo_in_preview", "show_expired_video", "show_expiring_video", "show_isps", "show_not_on_air_video", "show_tv_channel_logo", "stb_change_pass", "stream_logs_save", "use_buffer", "use_mdomain_in_lists",) as $rSetting) {
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
			// 		self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', SERVER_ID, time(), json_encode(array('action' => 'enable_ministra')));
			// 	}
			// } else {
			// 	if (file_exists(MAIN_DIR . 'wwwdir/c/')) {
			// 		self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', SERVER_ID, time(), json_encode(array('action' => 'disable_ministra')));
			// 	}
			// }

			foreach ($rData as $rKey => $rValue) {
				if (isset($rArray[$rKey])) {
					$rArray[$rKey] = $rValue;
				}
			}

			if (CoreUtilities::setSettings($rArray)) {
				clearSettingsCache();
				return array('status' => STATUS_SUCCESS);
			} else {
				return array('status' => STATUS_FAILURE);
			}
		} else {
			return array('status' => STATUS_INVALID_INPUT, 'data' => $rData);
		}
	}
	public static function processStream($rData) {
		if (!self::checkMinimumRequirements($rData)) {
			return array('status' => STATUS_INVALID_INPUT, 'data' => $rData);
		}
		set_time_limit(0);
		ini_set('mysql.connect_timeout', 0);
		ini_set('max_execution_time', 0);
		ini_set('default_socket_timeout', 0);

		if (isset($rData['edit'])) {
			if (!hasPermissions('adv', 'edit_stream')) {
				exit();
			}
			$rArray = overwriteData(getStream($rData['edit']), $rData);
		} else {
			if (!hasPermissions('adv', 'add_stream')) {
				exit();
			}
			$rArray = verifyPostTable('streams', $rData);
			$rArray['type'] = 1;
			$rArray['added'] = time();
			unset($rArray['id']);
		}

		if (isset($rData['days_to_restart']) && preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $rData['time_to_restart'])) {
			$rTimeArray = array('days' => array(), 'at' => $rData['time_to_restart']);

			foreach ($rData['days_to_restart'] as $rID => $rDay) {
				$rTimeArray['days'][] = $rDay;
			}
			$rArray['auto_restart'] = $rTimeArray;
		} else {
			$rArray['auto_restart'] = '';
		}

		foreach (array('fps_restart', 'gen_timestamps', 'allow_record', 'rtmp_output', 'stream_all', 'direct_source', 'read_native') as $rKey) {
			if (isset($rData[$rKey])) {
				$rArray[$rKey] = 1;
			} else {
				$rArray[$rKey] = 0;
			}
		}

		if (!$rArray['transcode_profile_id']) {
			$rArray['transcode_profile_id'] = 0;
		}

		if ($rArray['transcode_profile_id'] > 0) {
			$rArray['enable_transcode'] = 1;
		}

		if (isset($rData['restart_on_edit'])) {
			$rRestart = true;
		} else {
			$rRestart = false;
		}

		$rReview = false;
		$rImportStreams = array();

		if (isset($rData['review'])) {
			$rReview = true;

			foreach ($rData['review'] as $rImportStream) {
				if (!$rImportStream['channel_id'] || $rImportStream['tvg_id']) {
					$rEPG = findEPG($rImportStream['tvg_id']);

					if (isset($rEPG)) {
						$rImportStream['epg_id'] = $rEPG['epg_id'];
						$rImportStream['channel_id'] = $rEPG['channel_id'];

						if (!empty($rEPG['epg_lang'])) {
							$rImportStream['epg_lang'] = $rEPG['epg_lang'];
						}
					}
				}

				$rImportStreams[] = $rImportStream;
			}
		} else {
			if (isset($_FILES['m3u_file'])) {
				if (!hasPermissions('adv', 'import_streams')) {
					exit();
				}
				if (empty($_FILES['m3u_file']['tmp_name']) || strtolower(pathinfo(explode('?', $_FILES['m3u_file']['name'])[0], PATHINFO_EXTENSION)) != 'm3u') {
					return array('status' => STATUS_INVALID_FILE, 'data' => $rData);
				}
				$rResults = parseM3U($_FILES['m3u_file']['tmp_name']);

				if (count($rResults) > 0) {
					$rEPGDatabase = $rSourceDatabase = $rStreamDatabase = array();
					self::$ipTV_db->query('SELECT `id`, `stream_display_name`, `stream_source`, `channel_id` FROM `streams` WHERE `type` = 1;');

					foreach (self::$ipTV_db->get_rows() as $rRow) {
						$rName = preg_replace('/[^A-Za-z0-9 ]/', '', strtolower($rRow['stream_display_name']));

						if (!empty($rName)) {
							$rStreamDatabase[$rName] = $rRow['id'];
						}

						$rEPGDatabase[$rRow['channel_id']] = $rRow['id'];

						foreach (json_decode($rRow['stream_source'], true) as $rSource) {
							if (!empty($rSource)) {
								$rSourceDatabase[md5(preg_replace('(^https?://)', '', str_replace(' ', '%20', $rSource)))] = $rRow['id'];
							}
						}
					}
					$rEPGMatch = $rEPGScan = array();
					$i = 0;

					foreach ($rResults as $rResult) {
						list($rTag) = $rResult->getExtTags();

						if ($rTag) {
							if ($rTag->getAttribute('tvg-id')) {
								$rID = $rTag->getAttribute('tvg-id');
								$rEPGScan[$rID][] = $i;
							}
						}

						$i++;
					}

					if (count($rEPGScan) > 0) {
						self::$ipTV_db->query('SELECT `id`, `data` FROM `epg`;');

						if (self::$ipTV_db->num_rows() > 0) {
							foreach (self::$ipTV_db->get_rows() as $rRow) {
								foreach (json_decode($rRow['data'], true) as $rChannelID => $rChannelData) {
									if (isset($rEPGScan[$rChannelID])) {
										if (0 < count($rChannelData['langs'])) {
											$rEPGLang = $rChannelData['langs'][0];
										} else {
											$rEPGLang = '';
										}

										foreach ($rEPGScan[$rChannelID] as $i) {
											$rEPGMatch[$i] = array('channel_id' => $rChannelID, 'epg_lang' => $rEPGLang, 'epg_id' => intval($rRow['id']));
										}
									}
								}
							}
						}
					}

					$i = 0;

					foreach ($rResults as $rResult) {
						list($rTag) = $rResult->getExtTags();

						if ($rTag) {
							$rURL = $rResult->getPath();
							$rImportArray = array('stream_source' => array($rURL), 'stream_icon' => ($rTag->getAttribute('tvg-logo') ?: ''), 'stream_display_name' => ($rTag->getTitle() ?: ''), 'epg_id' => null, 'epg_lang' => null, 'channel_id' => null);

							// if ($rTag->getAttribute('tvg-id')) {
							// 	$rEPG = ($rEPGMatch[$i] ?: null);

							// 	if (isset($rEPG)) {
							// 		$rImportArray['epg_id'] = $rEPG['epg_id'];
							// 		$rImportArray['channel_id'] = $rEPG['channel_id'];

							// 		if (!empty($rEPG['epg_lang'])) {
							// 			$rImportArray['epg_lang'] = $rEPG['epg_lang'];
							// 		}
							// 	}
							// }

							$rBackupID = $rExistsID = null;
							$rSourceID = md5(preg_replace('(^https?://)', '', str_replace(' ', '%20', $rURL)));

							if (isset($rSourceDatabase[$rSourceID])) {
								$rExistsID = $rSourceDatabase[$rSourceID];
							}

							$rName = preg_replace('/[^A-Za-z0-9 ]/', '', strtolower($rTag->getTitle()));

							if (!empty($rName) && isset($rStreamDatabase[$rName])) {
								$rBackupID = $rStreamDatabase[$rName];
							} else {
								if (!empty($rImportArray['channel_id']) || isset($rEPGDatabase[$rImportArray['channel_id']])) {
									$rBackupID = $rEPGDatabase[$rImportArray['channel_id']];
								}
							}

							if ($rBackupID && !$rExistsID && isset($rData['add_source_as_backup'])) {
								self::$ipTV_db->query('SELECT `stream_source` FROM `streams` WHERE `id` = ?;', $rBackupID);

								if (self::$ipTV_db->num_rows() > 0) {
									$rSources = (json_decode(self::$ipTV_db->get_row()['stream_source'], true) ?: array());
									$rSources[] = $rURL;
									self::$ipTV_db->query('UPDATE `streams` SET `stream_source` = ? WHERE `id` = ?;', json_encode($rSources), $rBackupID);
									$rImportStreams[] = array('update' => true, 'id' => $rBackupID);
								}
							} else {
								if ($rExistsID && isset($rData['update_existing'])) {
									$rImportArray['id'] = $rExistsID;
									$rImportStreams[] = $rImportArray;
								} else {
									if (!$rExistsID) {
										$rImportStreams[] = $rImportArray;
									}
								}
							}
						}

						$i++;
					}
				}
			} else {
				if ($rData['epg_api']) {
					$rArray['channel_id'] = $rData['epg_api_id'];
					$rArray['epg_id'] = 0;
					$rArray['epg_lang'] = null;
				}

				$rImportArray = array('stream_source' => array(), 'stream_icon' => $rArray['stream_icon'], 'stream_display_name' => $rArray['stream_display_name'], 'epg_id' => $rArray['epg_id'], 'epg_lang' => $rArray['epg_lang'], 'channel_id' => $rArray['channel_id']);

				if (isset($rData['stream_source'])) {
					foreach ($rData['stream_source'] as $rID => $rURL) {
						if (strlen($rURL) > 0) {
							$rImportArray['stream_source'][] = $rURL;
						}
					}
				}

				$rImportStreams[] = $rImportArray;
			}
		}

		if (0 >= count($rImportStreams)) {
			return array('status' => STATUS_NO_SOURCES, 'data' => $rData);
		}
		$rBouquetCreate = array();
		$rCategoryCreate = array();

		if (!$rReview) {
			foreach (json_decode($rData['bouquet_create_list'], true) as $rBouquet) {
				$rPrepare = prepareArray(array('bouquet_name' => $rBouquet, 'bouquet_channels' => array(), 'bouquet_movies' => array(), 'bouquet_series' => array(), 'bouquet_radios' => array()));
				$rQuery = 'INSERT INTO `bouquets`(' . $rPrepare['columns'] . ') VALUES(' . $rPrepare['placeholder'] . ');';

				if (self::$ipTV_db->query($rQuery, ...$rPrepare['data'])) {
					$rBouquetID = self::$ipTV_db->last_insert_id();
					$rBouquetCreate[$rBouquet] = $rBouquetID;
				}
			}

			foreach (json_decode($rData['category_create_list'], true) as $rCategory) {
				$rPrepare = prepareArray(array('category_type' => 'live', 'category_name' => $rCategory, 'parent_id' => 0, 'cat_order' => 99, 'is_adult' => 0));
				$rQuery = 'INSERT INTO `streams_categories`(' . $rPrepare['columns'] . ') VALUES(' . $rPrepare['placeholder'] . ');';

				if (self::$ipTV_db->query($rQuery, ...$rPrepare['data'])) {
					$rCategoryID = self::$ipTV_db->last_insert_id();
					$rCategoryCreate[$rCategory] = $rCategoryID;
				}
			}
		}

		foreach ($rImportStreams as $rImportStream) {
			if (!$rImportStream['update']) {
				$rImportArray = $rArray;

				if (self::$rSettings['download_images']) {
					$rImportStream['stream_icon'] = downloadImage($rImportStream['stream_icon'], 1);
				}

				if ($rReview) {
					$rImportArray['category_id'] = '[' . implode(',', array_map('intval', $rImportStream['category_id'])) . ']';
					$rBouquets = array_map('intval', $rImportStream['bouquets']);
					unset($rImportStream['bouquets']);
				} else {
					$rBouquets = array();

					foreach ($rData['bouquets'] as $rBouquet) {
						if (isset($rBouquetCreate[$rBouquet])) {
							$rBouquets[] = $rBouquetCreate[$rBouquet];
						} else {
							if (is_numeric($rBouquet)) {
								$rBouquets[] = intval($rBouquet);
							}
						}
					}
					$rCategories = array();

					foreach ($rData['category_id'] as $rCategory) {
						if (isset($rCategoryCreate[$rCategory])) {
							$rCategories[] = $rCategoryCreate[$rCategory];
						} else {
							if (is_numeric($rCategory)) {
								$rCategories[] = intval($rCategory);
							}
						}
					}
					$rImportArray['category_id'] = '[' . implode(',', array_map('intval', $rCategories)) . ']';
				}

				foreach (array_keys($rImportStream) as $rKey) {
					$rImportArray[$rKey] = $rImportStream[$rKey];
				}

				if (!isset($rData['edit']) || !isset($rImportStream['id'])) {
					$rImportArray['order'] = getNextOrder();
				}

				$rPrepare = prepareArray($rImportArray);
				$rQuery = 'REPLACE INTO `streams`(' . $rPrepare['columns'] . ') VALUES(' . $rPrepare['placeholder'] . ');';

				if (self::$ipTV_db->query($rQuery, ...$rPrepare['data'])) {
					$rInsertID = self::$ipTV_db->last_insert_id();
					$rStreamExists = array();

					if (isset($rData['edit']) || isset($rImportStream['id'])) {
						self::$ipTV_db->query('SELECT `server_stream_id`, `server_id` FROM `streams_servers` WHERE `stream_id` = ?;', $rInsertID);

						foreach (self::$ipTV_db->get_rows() as $rRow) {
							$rStreamExists[intval($rRow['server_id'])] = intval($rRow['server_stream_id']);
						}
					}

					$rStreamsAdded = array();
					$rServerTree = json_decode($rData['server_tree_data'], true);

					foreach ($rServerTree as $rServer) {
						if ($rServer['parent'] != '#') {
							$rServerID = intval($rServer['id']);
							$rStreamsAdded[] = $rServerID;
							$rOD = intval(in_array($rServerID, ($rData['on_demand'] ?: array())));

							if ($rServer['parent'] == 'source') {
								$rParent = null;
							} else {
								$rParent = intval($rServer['parent']);
							}

							if (isset($rStreamExists[$rServerID])) {
								self::$ipTV_db->query('UPDATE `streams_servers` SET `parent_id` = ?, `on_demand` = ? WHERE `server_stream_id` = ?;', $rParent, $rOD, $rStreamExists[$rServerID]);
							} else {
								self::$ipTV_db->query('INSERT INTO `streams_servers`(`stream_id`, `server_id`, `parent_id`, `on_demand`) VALUES(?, ?, ?, ?);', $rInsertID, $rServerID, $rParent, $rOD);
							}
						}
					}

					foreach ($rStreamExists as $rServerID => $rDBID) {
						if (!in_array($rServerID, $rStreamsAdded)) {
							deleteStream($rInsertID, $rServerID, false, false);
						}
					}
					self::$ipTV_db->query('DELETE FROM `streams_options` WHERE `stream_id` = ?;', $rInsertID);

					if (isset($rData['user_agent']) && 0 < strlen($rData['user_agent'])) {
						self::$ipTV_db->query('INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(?, 1, ?);', $rInsertID, $rData['user_agent']);
					}

					if (isset($rData['http_proxy']) && 0 < strlen($rData['http_proxy'])) {
						self::$ipTV_db->query('INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(?, 2, ?);', $rInsertID, $rData['http_proxy']);
					}

					if (isset($rData['cookie']) && 0 < strlen($rData['cookie'])) {
						self::$ipTV_db->query('INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(?, 17, ?);', $rInsertID, $rData['cookie']);
					}

					if (isset($rData['headers']) && 0 < strlen($rData['headers'])) {
						self::$ipTV_db->query('INSERT INTO `streams_options`(`stream_id`, `argument_id`, `value`) VALUES(?, 19, ?);', $rInsertID, $rData['headers']);
					}

					if ($rRestart) {
						APIRequest(array('action' => 'stream', 'sub' => 'start', 'stream_ids' => array($rInsertID)));
					}

					foreach ($rBouquets as $rBouquet) {
						addToBouquet('stream', $rBouquet, $rInsertID);
					}

					if (isset($rData['edit']) || isset($rImportStream['id'])) {
						foreach (getBouquets() as $rBouquet) {
							if (!in_array($rBouquet['id'], $rBouquets)) {
								removeFromBouquet('stream', $rBouquet['id'], $rInsertID);
							}
						}
					}

					if ($rArray['epg_id'] == 0 || !empty($rArray['channel_id'])) {
						processEPGAPI($rInsertID, $rArray['channel_id']);
					}

					ipTV_streaming::updateStream($rInsertID);
				} else {
					foreach ($rBouquetCreate as $rBouquet => $rID) {
						self::$ipTV_db->query('DELETE FROM `bouquets` WHERE `id` = ?;', $rID);
					}

					foreach ($rCategoryCreate as $rCategory => $rID) {
						self::$ipTV_db->query('DELETE FROM `streams_categories` WHERE `id` = ?;', $rID);
					}

					return array('status' => STATUS_FAILURE, 'data' => $rData);
				}
			}
		}

		return array('status' => STATUS_SUCCESS, 'data' => array('insert_id' => $rInsertID));
	}
}
