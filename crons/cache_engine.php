<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        $rPID = getmypid();
        register_shutdown_function('shutdown');
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);
        ipTV_lib::$settings = ipTV_lib::getSettings(true);
        $rSplit = 10000;
        $rThreadCount = (ipTV_lib::$settings['cache_thread_count'] ?: 10);
        $rGroupStart = $rGroupMax = $rType = null;
        if (1 < count($argv)) {
            $rType = $argv[1];
            if ($rType == 'streams_update' || $rType == 'users_update') {
                $rUpdateIDs = array_map('intval', explode(',', $argv[2]));
            } else {
                if (count($argv) > 2) {
                    $rGroupStart = intval($argv[2]);
                    $rGroupMax = intval($argv[3]);
                }
            }
            if ($rType == 'force') {
                echo 'Forcing cache regen...' . "\n";
                ipTV_lib::$settings['cache_changes'] = false;
            }
        } else {
            shell_exec("kill -9 \$(ps aux | grep 'cache_engine' | grep -v grep | grep -v " . $rPID . " | awk '{print \$2}')");
        }
        loadCron($rType, $rGroupStart, $rGroupMax);
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
class Thread {
    public $process = null;
    public $pipes = null;
    public $buffer = null;
    public $output = null;
    public $error = null;
    public $timeout = null;
    public $start_time = null;
    public function __construct() {
        $this->process = 0;
        $this->buffer = '';
        $this->pipes = (array) null;
        $this->output = '';
        $this->error = '';
        $this->start_time = time();
        $this->timeout = 0;
    }
    public static function create($command) {
        $t = new Thread();
        $descriptor = array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w'));
        $t->process = proc_open($command, $descriptor, $t->pipes);
        stream_set_blocking($t->pipes[1], 0);
        stream_set_blocking($t->pipes[2], 0);
        return $t;
    }
    public function isActive() {
        $this->buffer .= $this->listen();
        $f = stream_get_meta_data($this->pipes[1]);
        return !$f['eof'];
    }
    public function close() {
        $r = proc_close($this->process);
        $this->process = null;
        return $r;
    }
    public function tell($thought) {
        fwrite($this->pipes[0], $thought);
    }
    public function listen() {
        $buffer = $this->buffer;
        $this->buffer = '';
        while ($r = fgets($this->pipes[1], 1024)) {
            $buffer .= $r;
            $this->output .= $r;
        }
        return $buffer;
    }
    public function getStatus() {
        return proc_get_status($this->process);
    }
    public function isBusy() {
        return 0 < $this->timeout && $this->start_time + $this->timeout < time();
    }
    public function getError() {
        $buffer = '';
        while ($r = fgets($this->pipes[2], 1024)) {
            $buffer .= $r;
        }
        return $buffer;
    }
}
class Multithread {
    public $output = array();
    public $error = array();
    public $thread = null;
    public $commands = array();
    public $hasPool = false;
    public $toExecuted = array();
    public function __construct($commands, $sizePool = 0) {
        $this->hasPool = 0 < $sizePool;
        if ($this->hasPool) {
            $this->toExecuted = array_splice($commands, $sizePool);
        }
        $this->commands = $commands;
        foreach ($this->commands as $key => $command) {
            $this->thread[$key] = Thread::create($command);
        }
    }
    public function run() {
        while (0 < count($this->commands)) {
            foreach ($this->commands as $key => $command) {
                $this->output[$key] .= @$this->thread[$key]->listen();
                $this->error[$key] .= @$this->thread[$key]->getError();
                if ($this->thread[$key]->isActive()) {
                    $this->output[$key] .= $this->thread[$key]->listen();
                    if ($this->thread[$key]->isBusy()) {
                        $this->thread[$key]->close();
                        unset($this->commands[$key]);
                        self::launchNextInQueue();
                    }
                } else {
                    $this->thread[$key]->close();
                    unset($this->commands[$key]);
                    self::launchNextInQueue();
                }
            }
        }
        return $this->output;
    }
    public function launchNextInQueue() {
        if (count($this->toExecuted) != 0) {
            reset($this->toExecuted);
            $keyToExecuted = key($this->toExecuted);
            $this->commands[$keyToExecuted] = $this->toExecuted[$keyToExecuted];
            $this->thread[$keyToExecuted] = Thread::create($this->toExecuted[$keyToExecuted]);
            unset($this->toExecuted[$keyToExecuted]);
        } else {
            return true;
        }
    }
}
function getChangedStreams() {
    global $ipTV_db;
    $rReturn = array('changes' => array(), 'delete' => array());
    $rExisting = array();
    $ipTV_db->query('SELECT `id`, GREATEST(IFNULL(UNIX_TIMESTAMP(`streams`.`updated`), 0), IFNULL(MAX(UNIX_TIMESTAMP(`streams_servers`.`updated`)), 0)) AS `updated` FROM `streams` LEFT JOIN `streams_servers` ON `streams`.`id` = `streams_servers`.`stream_id` GROUP BY `id`;');
    if ($ipTV_db->dbh && $ipTV_db->result) {
        if ($ipTV_db->num_rows() > 0) {
            foreach ($ipTV_db->get_rows() as $rRow) {
                if (file_exists(STREAMS_TMP_PATH . 'stream_' . $rRow['id']) && ((filemtime(STREAMS_TMP_PATH . 'stream_' . $rRow['id']) ?: 0)) >= $rRow['updated']) {
                } else {
                    $rReturn['changes'][] = $rRow['id'];
                }
                $rExisting[] = $rRow['id'];
            }
        }
    }
    $rExisting = array_flip($rExisting);
    foreach (glob(STREAMS_TMP_PATH . 'stream_*') as $rFile) {
        $rStreamID = intval(end(explode('_', $rFile)));
        if (!isset($rExisting[$rStreamID])) {
            $rReturn['delete'][] = $rStreamID;
        }
    }
    return $rReturn;
}
function loadCron($rType, $rGroupStart, $rGroupMax) {
    global $ipTV_db;
    global $rSplit;
    global $rUpdateIDs;
    global $rThreadCount;
    $rStartTime = time();
    if (ipTV_lib::isRunning()) {
        if (ipTV_lib::$cached || isset($rUpdateIDs)) {
            switch ($rType) {
                case 'users':
                    generateUsers($rGroupStart, $rGroupMax);
                    break;
                case 'users_update':
                    generateUsers(null, null, $rUpdateIDs);
                    break;
                case 'series':
                    generateSeries($rGroupStart, $rGroupMax);
                    break;
                case 'streams':
                    generateStreams($rGroupStart, $rGroupMax);
                    break;
                case 'streams_update':
                    generateStreams(null, null, $rUpdateIDs);
                    break;
                case 'groups':
                    generateGroups();
                    break;
                case 'users_per_ip':
                    generateUsersPerIP();
                    break;
                case 'theft_detection':
                    generateTheftDetection();
                    break;
                default:
                    // $cacheInitTime = $rSeriesCategories = array();
                    // $ipTV_db->query('SELECT `series_id`, MAX(`streams`.`added`) AS `last_modified` FROM `streams_episodes` LEFT JOIN `streams` ON `streams`.`id` = `streams_episodes`.`stream_id` GROUP BY `series_id`;');
                    // foreach ($ipTV_db->get_rows() as $rRow) {
                    //     $cacheInitTime[$rRow['series_id']] = $rRow['last_modified'];
                    // }
                    // $ipTV_db->query('SELECT * FROM `streams_series`;');
                    // if ($ipTV_db->result) {
                    //     if ($ipTV_db->num_rows() > 0) {
                    //         foreach ($ipTV_db->get_rows() as $rRow) {
                    //             if (isset($cacheInitTime[$rRow['id']])) {
                    //                 $rRow['last_modified'] = $cacheInitTime[$rRow['id']];
                    //             }
                    //             $rSeriesCategories[$rRow['id']] = json_decode($rRow['category_id'], true);
                    //             file_put_contents(SERIES_TMP_PATH . 'series_' . $rRow['id'], igbinary_serialize($rRow));
                    //         }
                    //     }
                    // }
                    // file_put_contents(SERIES_TMP_PATH . 'series_categories', igbinary_serialize($rSeriesCategories));
                    $rDelete = array('streams' => array(), 'users_i' => array(), 'users_c' => array(), 'users_t' => array());
                    $cacheDataKey = array();
                    if (ipTV_lib::$settings['cache_changes']) {
                        $rChanges = getChangedUsers();
                        $rDelete['users_i'] = $rChanges['delete_i'];
                        $rDelete['users_c'] = $rChanges['delete_c'];
                        $rDelete['users_t'] = $rChanges['delete_t'];
                        if (count($rChanges['changes']) > 0) {
                            foreach (array_chunk($rChanges['changes'], $rSplit) as $rChunk) {
                                $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "users_update" "' . implode(',', $rChunk) . '"';
                            }
                        }
                    } else {
                        $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `lines`;');
                        $rUsersCount = $ipTV_db->get_row()['count'];

                        // Calculate the number of iterations needed
                        $numIterations = ceil($rUsersCount / $rSplit);
                        for ($i = 0; $i < $numIterations; $i++) {
                            $rStart = $i * $rSplit;
                            $rMax = min($rSplit, $rUsersCount - $rStart); // Calculate max for the last iteration
                            $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "users" ' . $rStart . ' ' . $rMax;
                        }
                    }
                    // $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `streams_episodes` WHERE `stream_id` IN (SELECT `id` FROM `streams` WHERE `type` = 5);');
                    // $cacheRetrieveMethod = $ipTV_db->get_row()['count'];
                    // $cacheStoreMethod = range(0, $cacheRetrieveMethod, $rSplit);
                    // if (!$cacheStoreMethod) {
                    //     $cacheStoreMethod = array(0);
                    // }
                    // foreach ($cacheStoreMethod as $rStart) {
                    //     $rMax = $rSplit;
                    //     if ($cacheRetrieveMethod >= $rStart + $rMax) {
                    //     } else {
                    //         $rMax = $cacheRetrieveMethod - $rStart;
                    //     }
                    //     $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "series" ' . $rStart . ' ' . $rMax;
                    // }
                    if (ipTV_lib::$settings['cache_changes']) {
                        $rChanges = getchangedstreams();
                        $rDelete['streams'] = $rChanges['delete'];
                        if (count($rChanges['changes']) > 0) {
                            foreach (array_chunk($rChanges['changes'], $rSplit) as $rChunk) {
                                $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "streams_update" "' . implode(',', $rChunk) . '"';
                            }
                        }
                    } else {
                        $ipTV_db->query('SELECT COUNT(*) AS `count` FROM `streams`;');
                        $cacheDeleteMethod = $ipTV_db->get_row()['count'];

                        // Calculate the number of iterations needed
                        $numIterations = ceil($cacheDeleteMethod / $rSplit);
                        for ($i = 0; $i < $numIterations; $i++) {
                            $rStart = $i * $rSplit;
                            $rMax = min($rSplit, $cacheDeleteMethod - $rStart); // Calculate max for the last iteration
                            $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "streams" ' . $rStart . ' ' . $rMax;
                        }
                    }
                    $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "groups"';
                    $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "users_per_ip"';
                    $cacheDataKey[] = PHP_BIN . ' ' . CRON_PATH . 'cache_engine.php "theft_detection"';
                    $cacheMetadataKey = new Multithread($cacheDataKey, $rThreadCount);
                    $cacheMetadataKey->run();
                    unset($cacheDataKey);
                    // $rSeriesEpisodes = $rSeriesMap = array();
                    // foreach ($cacheStoreMethod as $rStart) {
                    //     if (file_exists(SERIES_TMP_PATH . 'series_map_' . $rStart)) {
                    //         foreach (igbinary_unserialize(file_get_contents(SERIES_TMP_PATH . 'series_map_' . $rStart)) as $rStreamID => $rSeriesID) {
                    //             $rSeriesMap[$rStreamID] = $rSeriesID;
                    //         }
                    //         unlink(SERIES_TMP_PATH . 'series_map_' . $rStart);
                    //     }
                    //     if (file_exists(SERIES_TMP_PATH . 'series_episodes_' . $rStart)) {
                    //         $rSeasonData = igbinary_unserialize(file_get_contents(SERIES_TMP_PATH . 'series_episodes_' . $rStart));
                    //         foreach (array_keys($rSeasonData) as $rSeriesID) {
                    //             if (!isset($rSeriesEpisodes[$rSeriesID])) {
                    //                 $rSeriesEpisodes[$rSeriesID] = array();
                    //             }
                    //             foreach (array_keys($rSeasonData[$rSeriesID]) as $rSeasonNum) {
                    //                 foreach ($rSeasonData[$rSeriesID][$rSeasonNum] as $rEpisode) {
                    //                     $rSeriesEpisodes[$rSeriesID][$rSeasonNum][] = $rEpisode;
                    //                 }
                    //             }
                    //         }
                    //         unlink(SERIES_TMP_PATH . 'series_episodes_' . $rStart);
                    //     }
                    // }
                    // file_put_contents(SERIES_TMP_PATH . 'series_map', igbinary_serialize($rSeriesMap));
                    // foreach ($rSeriesEpisodes as $rSeriesID => $rSeasons) {
                    //     file_put_contents(SERIES_TMP_PATH . 'episodes_' . $rSeriesID, igbinary_serialize($rSeasons));
                    // }
                    if (ipTV_lib::$settings['cache_changes']) {
                        foreach ($rDelete['streams'] as $rStreamID) {
                            @unlink(STREAMS_TMP_PATH . 'stream_' . $rStreamID);
                        }
                        foreach ($rDelete['users_i'] as $rUserID) {
                            @unlink(USER_TMP_PATH . 'user_i_' . $rUserID);
                        }
                        foreach ($rDelete['users_c'] as $cacheExpirationTime) {
                            @unlink(USER_TMP_PATH . 'user_c_' . $cacheExpirationTime);
                        }
                        foreach ($rDelete['users_t'] as $rToken) {
                            @unlink(USER_TMP_PATH . 'user_t_' . $rToken);
                        }
                    } else {
                        foreach (array(STREAMS_TMP_PATH, USER_TMP_PATH, SERIES_TMP_PATH) as $rTmpPath) {
                            foreach (scandir($rTmpPath) as $rFile) {
                                if (filemtime($rTmpPath . $rFile) < $rStartTime - 1) {
                                    if (is_file($rTmpPath . $rFile)) {
                                        unlink($rTmpPath . $rFile);
                                    }
                                }
                            }
                        }
                    }
                    echo 'Cache updated!' . "\n";
                    file_put_contents(CACHE_TMP_PATH . 'cache_complete', time());
                    ipTV_lib::setSettings(["last_cache" => time(), "last_cache_taken" => time() - $rStartTime]);
                    break;
            }
        } else {
            echo 'Cache is disabled.' . "\n";
            echo 'Generating group permissions...' . "\n";
            generateGroups();
            echo 'Generating users per ip...' . "\n";
            generateUsersPerIP();
            echo 'Detecting theft of VOD...' . "\n";
            generateTheftDetection();
            echo 'Clearing old data...' . "\n";
            foreach (array(STREAMS_TMP_PATH, USER_TMP_PATH, SERIES_TMP_PATH) as $rTmpPath) {
                foreach (scandir($rTmpPath) as $rFile) {
                    if (is_file($rTmpPath . $rFile)) {
                        unlink($rTmpPath . $rFile);
                    }
                }
            }
            file_put_contents(CACHE_TMP_PATH . 'cache_complete', time());
            exit();
        }
    } else {
        echo 'XC_VM not running...' . "\n";
        exit();
    }
}
function generateUsers($rStart = null, $rCount = null, $cacheLockMechanism = array()) {
    global $ipTV_db;
    global $rSplit;
    if (is_null($rCount)) {
        $rCount = count($cacheLockMechanism);
    }
    if ($rCount > 0) {
        $rSteps = [];
        if (!is_null($rStart)) {
            $rEnd = $rStart + $rCount - 1;
            for ($i = $rStart; $i <= $rEnd; $i += $rSplit) {
                $rSteps[] = $i;
            }
        } else {
            $rSteps = [null];
        }

        $rExists = array();
        foreach ($rSteps as $rStep) {
            if (!is_null($rStep)) {
                if ($rStart + $rCount < $rStep + $rSplit) {
                    $rMax = ($rStart + $rCount) - $rStep;
                } else {
                    $rMax = $rSplit;
                }
                $ipTV_db->query('SELECT `id`, `username`, `password`, `exp_date`, `created_at`, `admin_enabled`, `enabled`, `bouquet`, `max_connections`, `is_trial`, `is_restreamer`, `is_stalker`, `is_mag`, `is_e2`, `is_isplock`, `allowed_ips`, `allowed_ua`, `pair_id`, `force_server_id`, `isp_desc`, `forced_country`, `bypass_ua`, `last_expiration_video`, `access_token`,`allowed_outputs`, `mag_devices`.`token` AS `mag_token`, `admin_notes`, `reseller_notes` FROM `lines` LEFT JOIN `mag_devices` ON `mag_devices`.`user_id` = `lines`.`id` LIMIT ' . $rStep . ', ' . $rMax . ';');
            } else {
                $ipTV_db->query('SELECT `id`, `username`, `password`, `exp_date`, `created_at`, `admin_enabled`, `enabled`, `bouquet`, `max_connections`, `is_trial`, `is_restreamer`, `is_stalker`, `is_mag`, `is_e2`, `is_isplock`, `allowed_ips`, `allowed_ua`, `pair_id`, `force_server_id`, `isp_desc`, `forced_country`, `bypass_ua`, `last_expiration_video`, `access_token`,`allowed_outputs`, `mag_devices`.`token` AS `mag_token`, `admin_notes`, `reseller_notes` FROM `lines` LEFT JOIN `mag_devices` ON `mag_devices`.`user_id` = `lines`.`id` WHERE `id` IN (' . implode(',', $cacheLockMechanism) . ');');
            }
            if ($ipTV_db->result) {
                if ($ipTV_db->num_rows() > 0) {
                    foreach ($ipTV_db->get_rows() as $rUserInfo) {
                        $rExists[] = $rUserInfo['id'];
                        file_put_contents(USER_TMP_PATH . 'user_i_' . $rUserInfo['id'], igbinary_serialize($rUserInfo));
                        $rKey = (ipTV_lib::$settings['case_sensitive_line'] ? $rUserInfo['username'] . '_' . $rUserInfo['password'] : strtolower($rUserInfo['username'] . '_' . $rUserInfo['password']));
                        file_put_contents(USER_TMP_PATH . 'user_c_' . $rKey, $rUserInfo['id']);
                        if (!empty($rUserInfo['access_token'])) {
                            file_put_contents(USER_TMP_PATH . 'user_t_' . $rUserInfo['access_token'], $rUserInfo['id']);
                        }
                    }
                }
                $ipTV_db->result = null;
            }
        }
        if (count($cacheLockMechanism) > 0) {
            foreach ($cacheLockMechanism as $rForceID) {
                if (!in_array($rForceID, $rExists) || file_exists(USER_TMP_PATH . 'user_i_' . $rForceID)) {
                    unlink(USER_TMP_PATH . 'user_i_' . $rForceID);
                }
            }
        }
    }
}
function generateStreams($rStart = null, $rCount = null, $cacheLockMechanism = array()) {
    global $ipTV_db;
    global $rSplit;
    if (is_null($rCount)) {
        $rCount = count($cacheLockMechanism);
    }
    if ($rCount > 0) {
        $rBouquetMap = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'bouquet_map'));

        if (!is_null($rStart)) {
            $rEnd = $rStart + $rCount - 1;
            for ($i = $rStart; $i <= $rEnd; $i += $rSplit) {
                $rSteps[] = $i;
            }
        } else {
            $rSteps = [null];
        }
        $rExists = array();
        foreach ($rSteps as $rStep) {
            if (!is_null($rStep)) {
                if ($rStart + $rCount < $rStep + $rSplit) {
                    $rMax = ($rStart + $rCount) - $rStep;
                } else {
                    $rMax = $rSplit;
                }
                $ipTV_db->query('SELECT t1.id,t1.epg_id,t1.added,t1.allow_record,t1.channel_id,t1.movie_properties,t1.stream_source,t1.tv_archive_server_id,t1.tv_archive_duration,t1.stream_icon,t1.custom_sid,t1.category_id,t1.stream_display_name,t1.series_no,t1.direct_source,t2.type_output,t1.target_container,t2.live,t1.rtmp_output,t1.order,t2.type_key FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type LIMIT ' . $rStep . ', ' . $rMax . ';');
            } else {
                $ipTV_db->query('SELECT t1.id,t1.epg_id,t1.added,t1.allow_record,t1.channel_id,t1.movie_properties,t1.stream_source,t1.tv_archive_server_id,t1.tv_archive_duration,t1.stream_icon,t1.custom_sid,t1.category_id,t1.stream_display_name,t1.series_no,t1.direct_source,t2.type_output,t1.target_container,t2.live,t1.rtmp_output,t1.order,t2.type_key FROM `streams` t1 INNER JOIN `streams_types` t2 ON t2.type_id = t1.type WHERE `t1`.`id` IN (' . implode(',', $cacheLockMechanism) . ');');
            }
            if ($ipTV_db->result) {
                if ($ipTV_db->num_rows() > 0) {
                    $rRows = $ipTV_db->get_rows();
                    $rStreamMap = $rStreamIDs = array();
                    foreach ($rRows as $rRow) {
                        $rStreamIDs[] = $rRow['id'];
                    }
                    if (count($rStreamIDs) > 0) {
                        $ipTV_db->query('SELECT `stream_id`, `server_id`, `pid`, `to_analyze`, `stream_status`, `monitor_pid`, `on_demand`, `delay_available_at`, `bitrate`, `parent_id`, `on_demand`, `stream_info` FROM `streams_servers` WHERE `stream_id` IN (' . implode(',', $rStreamIDs) . ')');
                        if ($ipTV_db->result) {
                            if ($ipTV_db->num_rows() > 0) {
                                foreach ($ipTV_db->get_rows() as $rRow) {
                                    $rStreamMap[intval($rRow['stream_id'])][intval($rRow['server_id'])] = $rRow;
                                }
                            }
                            $ipTV_db->result = null;
                        }
                    }
                    foreach ($rRows as $rStreamInfo) {
                        $rExists[] = $rStreamInfo['id'];
                        if (!$rStreamInfo['direct_source']) {
                            unset($rStreamInfo['stream_source']);
                        }
                        $rOutput = array('info' => $rStreamInfo, 'bouquets' => ($rBouquetMap[intval($rStreamInfo['id'])] ?: array()), 'servers' => (isset($rStreamMap[intval($rStreamInfo['id'])]) ? $rStreamMap[intval($rStreamInfo['id'])] : array()));
                        file_put_contents(STREAMS_TMP_PATH . 'stream_' . $rStreamInfo['id'], igbinary_serialize($rOutput));
                    }
                    unset($rRows, $rStreamMap, $rStreamIDs);
                }
                $ipTV_db->result = null;
            }
        }
        if (count($cacheLockMechanism) > 0) {
            foreach ($cacheLockMechanism as $rForceID) {
                if (!in_array($rForceID, $rExists) || file_exists(STREAMS_TMP_PATH . 'stream_' . $rForceID)) {
                    unlink(STREAMS_TMP_PATH . 'stream_' . $rForceID);
                }
            }
        }
    }
}
function generateSeries($rStart, $rCount) {
    global $ipTV_db;
    global $rSplit;
    $rSeriesMap = array();
    $rSeriesEpisodes = array();
    if ($rCount > 0) {
        $rSteps = range($rStart, ($rStart + $rCount) - 1, $rSplit);
        if (!$rSteps) {
            $rSteps = array($rStart);
        }
        foreach ($rSteps as $rStep) {
            if ($rStart + $rCount < $rStep + $rSplit) {
                $rMax = ($rStart + $rCount) - $rStep;
            } else {
                $rMax = $rSplit;
            }
            $ipTV_db->query('SELECT `stream_id`, `series_id`, `season_num`, `episode_num` FROM `streams_episodes` WHERE `stream_id` IN (SELECT `id` FROM `streams` WHERE `type` = 5) ORDER BY `series_id` ASC, `season_num` ASC, `episode_num` ASC LIMIT ' . $rStep . ', ' . $rMax . ';');
            foreach ($ipTV_db->get_rows() as $rRow) {
                if ($rRow['stream_id'] && $rRow['series_id']) {
                    $rSeriesMap[intval($rRow['stream_id'])] = intval($rRow['series_id']);
                    if (!isset($rSeriesEpisodes[$rRow['series_id']])) {
                        $rSeriesEpisodes[$rRow['series_id']] = array();
                    }
                    $rSeriesEpisodes[$rRow['series_id']][$rRow['season_num']][] = array('episode_num' => $rRow['episode_num'], 'stream_id' => $rRow['stream_id']);
                }
            }
        }
    }
    file_put_contents(SERIES_TMP_PATH . 'series_episodes_' . $rStart, igbinary_serialize($rSeriesEpisodes));
    file_put_contents(SERIES_TMP_PATH . 'series_map_' . $rStart, igbinary_serialize($rSeriesMap));
    unset($rSeriesMap);
}
function generateGroups() {
    global $ipTV_db;
    $ipTV_db->query('SELECT `group_id` FROM `member_groups`;');
    foreach ($ipTV_db->get_rows() as $rGroup) {
        $rBouquets = $rReturn = array();
        $ipTV_db->query("SELECT * FROM `packages` WHERE JSON_CONTAINS(`groups`, ?, '\$');", $rGroup['group_id']);
        foreach ($ipTV_db->get_rows() as $rRow) {
            foreach (json_decode($rRow['bouquets'], true) as $rID) {
                if (!in_array($rID, $rBouquets)) {
                    $rBouquets[] = $rID;
                }
            }
            if ($rRow['is_line']) {
                $rReturn['create_line'] = true;
            }
            if ($rRow['is_mag']) {
                $rReturn['create_mag'] = true;
            }
            if ($rRow['is_e2']) {
                $rReturn['create_enigma'] = true;
            }
        }
        if (count($rBouquets) > 0) {
            $ipTV_db->query('SELECT * FROM `bouquets` WHERE `id` IN (' . implode(',', array_map('intval', $rBouquets)) . ');');
            $rSeriesIDs = array();
            $rStreamIDs = array();
            foreach ($ipTV_db->get_rows() as $rRow) {
                if ($rRow['bouquet_channels']) {
                    $rStreamIDs = array_merge($rStreamIDs, json_decode($rRow['bouquet_channels'], true));
                }
                if ($rRow['bouquet_movies']) {
                    $rStreamIDs = array_merge($rStreamIDs, json_decode($rRow['bouquet_movies'], true));
                }
                if ($rRow['bouquet_radios']) {
                    $rStreamIDs = array_merge($rStreamIDs, json_decode($rRow['bouquet_radios'], true));
                }
                foreach (json_decode($rRow['bouquet_series'], true) as $rSeriesID) {
                    $rSeriesIDs[] = $rSeriesID;
                    $ipTV_db->query('SELECT `stream_id` FROM `streams_episodes` WHERE `series_id` = ?;', $rSeriesID);
                    foreach ($ipTV_db->get_rows() as $rEpisode) {
                        $rStreamIDs[] = $rEpisode['stream_id'];
                    }
                }
            }
            $rReturn['stream_ids'] = array_unique($rStreamIDs);
            $rReturn['series_ids'] = array_unique($rSeriesIDs);
            $rCategories = array();
            if (count($rReturn['stream_ids']) > 0) {
                $ipTV_db->query('SELECT DISTINCT(`category_id`) AS `category_id` FROM `streams` WHERE `id` IN (' . implode(',', array_map('intval', $rReturn['stream_ids'])) . ');');
                foreach ($ipTV_db->get_rows() as $rRow) {
                    if ($rRow['category_id']) {
                        $rCategories = array_merge($rCategories, json_decode($rRow['category_id'], true));
                    }
                }
            }
            if (count($rReturn['series_ids']) > 0) {
                $ipTV_db->query('SELECT DISTINCT(`category_id`) AS `category_id` FROM `streams_series` WHERE `id` IN (' . implode(',', array_map('intval', $rReturn['series_ids'])) . ');');
                foreach ($ipTV_db->get_rows() as $rRow) {
                    if ($rRow['category_id']) {
                        $rCategories = array_merge($rCategories, json_decode($rRow['category_id'], true));
                    }
                }
            }
            $rReturn['category_ids'] = array_unique($rCategories);
        }
        file_put_contents(CACHE_TMP_PATH . 'permissions_' . intval($rGroup['group_id']), igbinary_serialize($rReturn));
    }
}
function generateUsersPerIP() {
    global $ipTV_db;
    $rUsersPerIP = array(3600 => array(), 86400 => array(), 604800 => array(), 0 => array());
    foreach (array_keys($rUsersPerIP) as $rTime) {
        if (0 < $rTime) {
            $ipTV_db->query('SELECT `user_activity`.`user_id`, COUNT(DISTINCT(`user_activity`.`user_ip`)) AS `ip_count`, `lines`.`username` FROM `user_activity` LEFT JOIN `lines` ON `lines`.`id` = `user_activity`.`user_id` WHERE `date_start` >= ? AND `lines`.`is_mag` = 0 AND `lines`.`is_e2` = 0 AND `lines`.`is_restreamer` = 0 GROUP BY `user_activity`.`user_id` ORDER BY `ip_count` DESC LIMIT 1000;', time() - $rTime);
        } else {
            $ipTV_db->query('SELECT `user_activity`.`user_id`, COUNT(DISTINCT(`user_activity`.`user_ip`)) AS `ip_count`, `lines`.`username` FROM `user_activity` LEFT JOIN `lines` ON `lines`.`id` = `user_activity`.`user_id` WHERE `lines`.`is_mag` = 0 AND `lines`.`is_e2` = 0 AND `lines`.`is_restreamer` = 0 GROUP BY `user_activity`.`user_id` ORDER BY `ip_count` DESC LIMIT 1000;');
        }
        foreach ($ipTV_db->get_rows() as $rRow) {
            $rUsersPerIP[$rTime][] = $rRow;
        }
    }
    file_put_contents(CACHE_TMP_PATH . 'users_per_ip', igbinary_serialize($rUsersPerIP));
}
function generateTheftDetection() {
    global $ipTV_db;
    $rTheftDetection = array(3600 => array(), 86400 => array(), 604800 => array(), 0 => array());
    foreach (array_keys($rTheftDetection) as $rTime) {
        if (0 < $rTime) {
            $ipTV_db->query('SELECT `user_activity`.`user_id`, COUNT(DISTINCT(`user_activity`.`stream_id`)) AS `vod_count`, `lines`.`username` FROM `user_activity` LEFT JOIN `lines` ON `lines`.`id` = `user_activity`.`user_id` WHERE `date_start` >= ? AND `lines`.`is_mag` = 0 AND `lines`.`is_e2` = 0 AND `lines`.`is_restreamer` = 0 AND `stream_id` IN (SELECT `id` FROM `streams` WHERE `type` IN (2,5)) GROUP BY `user_activity`.`user_id` ORDER BY `vod_count` DESC LIMIT 1000;', time() - $rTime);
        } else {
            $ipTV_db->query('SELECT `user_activity`.`user_id`, COUNT(DISTINCT(`user_activity`.`stream_id`)) AS `vod_count`, `lines`.`username` FROM `user_activity` LEFT JOIN `lines` ON `lines`.`id` = `user_activity`.`user_id` WHERE `lines`.`is_mag` = 0 AND `lines`.`is_e2` = 0 AND `lines`.`is_restreamer` = 0 AND `stream_id` IN (SELECT `id` FROM `streams` WHERE `type` IN (2,5)) GROUP BY `user_activity`.`user_id` ORDER BY `vod_count` DESC LIMIT 1000;');
        }
        foreach ($ipTV_db->get_rows() as $rRow) {
            $rTheftDetection[$rTime][] = $rRow;
        }
    }
    file_put_contents(CACHE_TMP_PATH . 'theft_detection', igbinary_serialize($rTheftDetection));
}
function getChangedUsers() {
    global $ipTV_db;
    $rReturn = array('changes' => array(), 'delete_i' => array(), 'delete_c' => array(), 'delete_t' => array());
    $cacheMemoryAllocation = glob(USER_TMP_PATH . 'user_i_*');
    $cacheFailureHandler = glob(USER_TMP_PATH . 'user_c_*');
    $cacheSuccessIndicator = glob(USER_TMP_PATH . 'user_t_*');
    $cacheRevalidationCheck = $cacheDataCompression = $cacheDataDecompression = array();
    $ipTV_db->query('SELECT `id`, `username`, `password`, `access_token`, UNIX_TIMESTAMP(`updated`) AS `updated` FROM `lines`;');
    if ($ipTV_db->dbh && $ipTV_db->result) {
        if ($ipTV_db->num_rows() > 0) {
            foreach ($ipTV_db->get_rows() as $rRow) {
                if (!(file_exists(USER_TMP_PATH . 'user_i_' . $rRow['id']) && ((filemtime(USER_TMP_PATH . 'user_i_' . $rRow['id']) ?: 0)) >= $rRow['updated'])) {
                    $rReturn['changes'][] = $rRow['id'];
                }
                $cacheRevalidationCheck[] = $rRow['id'];
                $cacheDataCompression[] = (ipTV_lib::$settings['case_sensitive_line'] ? $rRow['username'] . '_' . $rRow['password'] : strtolower($rRow['username'] . '_' . $rRow['password']));
                if ($rRow['access_token']) {
                    $cacheDataDecompression[] = $rRow['access_token'];
                }
            }
        }
    }
    $cacheRevalidationCheck = array_flip($cacheRevalidationCheck);
    foreach ($cacheMemoryAllocation as $rFile) {
        $rUserID = (intval(explode('user_i_', $rFile, 2)[1]) ?: null);
        if ($rUserID || !isset($cacheRevalidationCheck[$rUserID])) {
            $rReturn['delete_i'][] = $rUserID;
        }
    }
    $cacheDataCompression = array_flip($cacheDataCompression);
    foreach ($cacheFailureHandler as $rFile) {
        $cacheExpirationTime = (explode('user_c_', $rFile, 2)[1] ?: null);
        if ($cacheExpirationTime || !isset($cacheDataCompression[$cacheExpirationTime])) {
            $rReturn['delete_c'][] = $cacheExpirationTime;
        }
    }
    $cacheDataDecompression = array_flip($cacheDataDecompression);
    foreach ($cacheSuccessIndicator as $rFile) {
        $rToken = (explode('user_t_', $rFile, 2)[1] ?: null);
        if ($rToken || !isset($cacheDataDecompression[$rToken])) {
            $rReturn['delete_t'][] = $rToken;
        }
    }
    return $rReturn;
}
function shutdown() {
    global $ipTV_db;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
