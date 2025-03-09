<?php

class UIController {
    public static $ipTV_db = null;

    /**
     * Generates a list of all time zones with their UTC/GMT offset.
     *
     * This function retrieves all available time zones, sets each as the default timezone, 
     * and calculates the UTC/GMT offset for the current timestamp.
     *
     * @return array An associative array where each entry contains:
     *               - 'zone' (string): The timezone identifier.
     *               - 'diff_from_GMT' (string): The UTC/GMT offset.
     */
    public static function tz_list() {
        $zones_array = array(); // Array to store time zone data
        $timestamp = time(); // Current timestamp

        // Loop through all available time zones
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone); // Set the default timezone

            // Store the timezone identifier and its UTC offset
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }

        return $zones_array; // Return the list of time zones with offsets
    }

    public static function getRegisteredUser($rID) {
        self::$ipTV_db->query("SELECT * FROM `reg_users` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getRegisteredUserHash($rHash) {
        self::$ipTV_db->query("SELECT * FROM `reg_users` WHERE MD5(`username`) = ? LIMIT 1;", $rHash);
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function doLogin($rUsername, $rPassword) {
        self::$ipTV_db->query("SELECT `id`, `username`, `password`, `member_group_id`,`status` FROM `reg_users` WHERE `username` = ? LIMIT 1;", $rUsername);
        if (self::$ipTV_db->num_rows() == 1) {
            $rRow = self::$ipTV_db->get_row();

            if (self::cryptPassword($rPassword) == $rRow["password"]) {
                return $rRow;
            }
        }
        return null;
    }

    public static function cryptPassword($password, $salt = "xc_vm", $rounds = 20000) {
        if ($salt == "") {
            $salt = substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 16);
        }
        $hash = crypt($password, sprintf('$6$rounds=%d$%s$', $rounds, $salt));
        return $hash;
    }

    public static function getUser($rID) {
        self::$ipTV_db->query("SELECT * FROM `lines` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getPermissions($rID) {
        self::$ipTV_db->query("SELECT * FROM `member_groups` WHERE `group_id` = ?;", intval($rID));
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getCategories_admin($rType = "live") {
        $return = array();
        if ($rType) {
            self::$ipTV_db->query("SELECT * FROM `stream_categories` WHERE `category_type` = '" . $rType . "' ORDER BY `cat_order` ASC;");
        } else {
            self::$ipTV_db->query("SELECT * FROM `stream_categories` ORDER BY `cat_order` ASC;");
        }
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $rRow) {
                $return[intval($rRow['id'])] = $rRow;
            }
        }
        return $return;
    }

    public static function getStreamingServers($online = false) {
        global $rPermissions;
        $return = array();
        if ($online) {
            self::$ipTV_db->query("SELECT * FROM `servers` WHERE `status` = 1 ORDER BY `id` ASC;");
        } else {
            self::$ipTV_db->query("SELECT * FROM `servers` ORDER BY `id` ASC;");
        }
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if (isset($rPermissions["is_reseller"]) && $rPermissions["is_reseller"]) {
                    $row["server_name"] = "Server #" . $row["id"];
                }
                $return[intval($row['id'])] = $row;
            }
        }
        return $return;
    }

    public static function isSecure() {
        // Check for HTTPS in a web server environment
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        // Check for secure port
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }

        return false;
    }

    public static function getProtocol() {
        if (self::isSecure()) {
            return 'https';
        }
        return 'http';
    }

    public static function getScriptVer() {
        self::$ipTV_db->query("SELECT `script_version` FROM `servers` WHERE `is_main` = '1'");
        $version = self::$ipTV_db->get_row()["script_version"];
        return $version;
    }

    public static function getFooter() {
        // Don't be a dick. Leave it.
        global $_, $rPermissions, $rSettings;
        if ($rPermissions["is_admin"]) {
            return $_["copyright"] . " &copy; 2023 - " . date("Y") . " - <a href=\"https://github.com/Vateron-Media/Xtream_main\">XC_VM</a> " . self::getScriptVer() . " - " . $_["free_forever"];
        } else {
            return $rSettings["copyrights_text"];
        }
    }

    public static function getIP() {
        $ip = null;

        // Check IP in order of priority
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // May contain a list of IPs, take the first one (original IP)
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        // Check if the IP address is correct
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            // XSS protection (if IP is displayed on the page)
            return htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');
        }

        // If IP is incorrect, return null
        return null;
    }

    public static function changePort($rServerID, $rType, $rPort, $rReload = false) {
        self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServerID, time(), json_encode(array('action' => 'set_port', 'type' => intval($rType), 'port' => $rPort, 'reload' => $rReload)));
    }

    public static function setServices($rServerID, $rNumServices, $rReload = true) {
        self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServerID, time(), json_encode(array('action' => 'set_services', 'count' => intval($rNumServices), 'reload' => $rReload)));
    }

    public static function setSysctl($rServerID, $rSysCtl) {
        self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServerID, time(), json_encode(array('action' => 'set_sysctl', 'data' => $rSysCtl)));
    }

    /**
     * Queue deletion signals for a VOD file across specified streaming servers
     * 
     * Inserts asynchronous deletion tasks into the signals table for external processing.
     * Handles both single server ID and server ID arrays for batch operations.
     *
     * @param int|int[] $rServerIDs Single server ID or array of server IDs to notify
     * @param int $rID Database ID of the VOD file to be removed
     * @return bool Always returns true (does not verify database insertion success)
     *
     */
    public static function deleteMovieFile($rServerIDs, $rID) {
        if (!is_array($rServerIDs)) {
            $rServerIDs = array($rServerIDs);
        }
        foreach ($rServerIDs as $rServerID) {
            self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`, `cache`) VALUES(?, ?, ?, 1);', $rServerID, time(), json_encode(array('type' => 'delete_vod', 'id' => $rID)));
        }
        return true;
    }

    /**
     * Flushes blocked IP addresses and sends a flush signal to all servers and proxy servers.
     *
     * @return bool Returns `true` after successfully flushing the IPs.
     */
    public static function flushIPs() {
        global $rServers, $rProxyServers;
        self::$ipTV_db->query('TRUNCATE `blocked_ips`;');
        shell_exec('rm ' . FLOOD_TMP_PATH . 'block_*');

        foreach ($rServers as $rServer) {
            self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServer['id'], time(), json_encode(array('action' => 'flush')));
        }

        foreach ($rProxyServers as $rServer) {
            self::$ipTV_db->query('INSERT INTO `signals`(`server_id`, `time`, `custom_data`) VALUES(?, ?, ?);', $rServer['id'], time(), json_encode(array('action' => 'flush')));
        }

        return true;
    }

    public static function scanBouquets() {
        shell_exec(PHP_BIN . ' ' . CLI_PATH . 'tools.php "bouquets" > /dev/null 2>/dev/null &');
    }

    public static function processEPGAPI($rStreamID, $rChannelID) {
        shell_exec(PHP_BIN . ' ' . CRON_PATH . 'epg.php ' . intval($rStreamID) . ' ' . escapeshellarg($rChannelID) . ' > /dev/null 2>/dev/null &');
        return true;
    }

    public static function scanBouquet($rID) {
        $rBouquet = self::getBouquet($rID);
        if ($rBouquet) {
            $rStreamIDs = array();
            self::$ipTV_db->query("SELECT `id` FROM `streams`;");
            if (self::$ipTV_db->num_rows() > 0) {
                foreach (self::$ipTV_db->get_rows() as $row) {
                    $rStreamIDs[0][] = intval($row['id']);
                }
            }
            self::$ipTV_db->query("SELECT `id` FROM `series`;");
            if (self::$ipTV_db->num_rows() > 0) {
                foreach (self::$ipTV_db->get_rows() as $row) {
                    $rStreamIDs[1][] = intval($row['id']);
                }
            }
            $rUpdate = array(array(), array(), array(), array());
            foreach (json_decode($rBouquet['bouquet_channels'], true) as $rID) {
                if (in_array(intval($rID), $rStreamIDs[0])) {
                    $rUpdate[0][] = intval($rID);
                }
            }
            foreach (json_decode($rBouquet['bouquet_movies'], true) as $rID) {
                if (in_array(intval($rID), $rStreamIDs[0])) {
                    $rUpdate[1][] = intval($rID);
                }
            }
            foreach (json_decode($rBouquet['bouquet_radios'], true) as $rID) {
                if (in_array(intval($rID), $rStreamIDs[0])) {
                    $rUpdate[2][] = intval($rID);
                }
            }
            foreach (json_decode($rBouquet['bouquet_series'], true) as $rID) {
                if (in_array(intval($rID), $rStreamIDs[1])) {
                    $rUpdate[3][] = intval($rID);
                }
            }
            self::$ipTV_db->query("UPDATE `bouquets` SET `bouquet_channels` = '" . json_encode($rUpdate[0]) . "', `bouquet_movies` = '" . json_encode($rUpdate[1]) . "', `bouquet_radios` = '" . json_encode($rUpdate[2]) . "', `bouquet_series` = '" . json_encode($rUpdate[3]) . "' WHERE `id` = " . intval($rBouquet["id"]) . ";");
        }
    }

    public static function getBackups() {
        $rBackups = array();

        # create directory backups
        if (!is_dir(MAIN_DIR . "backups/")) {
            mkdir(MAIN_DIR . "backups/");
        }

        foreach (scandir(MAIN_DIR . "backups/") as $rBackup) {
            $rInfo = pathinfo(MAIN_DIR . "backups/" . $rBackup);
            if ($rInfo["extension"] == "sql") {
                $rBackups[] = array("filename" => $rBackup, "timestamp" => filemtime(MAIN_DIR . "backups/" . $rBackup), "date" => date("Y-m-d H:i:s", filemtime(MAIN_DIR . "backups/" . $rBackup)), "filesize" => filesize(MAIN_DIR . "backups/" . $rBackup));
            }
        }
        usort($rBackups, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
        return $rBackups;
    }

    public static function hasPermissions($rType, $rID) {
        global $rUserInfo, $rPermissions;
        if (isset($rUserInfo) && isset($rPermissions)) {
            if ($rType == "user") {
                if (in_array(intval(self::getUser($rID)["member_id"]), array_keys(self::getRegisteredUsers($rUserInfo["id"])))) {
                    return true;
                }
            } elseif ($rType == "pid") {
                self::$ipTV_db->query("SELECT `user_id` FROM `lines_live` WHERE `pid` = " . intval($rID) . ";");
                if (self::$ipTV_db->num_rows() > 0) {
                    if (in_array(intval(self::getUser(self::$ipTV_db->get_row()["user_id"])["member_id"]), array_keys(self::getRegisteredUsers($rUserInfo["id"])))) {
                        return true;
                    }
                }
            } elseif ($rType == "reg_user") {
                if ((in_array(intval($rID), array_keys(self::getRegisteredUsers($rUserInfo["id"])))) && (intval($rID) <> intval($rUserInfo["id"]))) {
                    return true;
                }
            } elseif ($rType == "ticket") {
                if (in_array(intval(self::getTicket($rID)["member_id"]), array_keys(self::getRegisteredUsers($rUserInfo["id"])))) {
                    return true;
                }
            } elseif ($rType == "mag") {
                self::$ipTV_db->query("SELECT `user_id` FROM `mag_devices` WHERE `mag_id` = " . intval($rID) . ";");
                if (self::$ipTV_db->num_rows() > 0) {
                    if (in_array(intval(self::getUser(self::$ipTV_db->get_row()["user_id"])["member_id"]), array_keys(self::getRegisteredUsers($rUserInfo["id"])))) {
                        return true;
                    }
                }
            } elseif ($rType == "e2") {
                self::$ipTV_db->query("SELECT `user_id` FROM `enigma2_devices` WHERE `device_id` = " . intval($rID) . ";");
                if (self::$ipTV_db->num_rows() > 0) {
                    if (in_array(intval(self::getUser(self::$ipTV_db->get_row()["user_id"])["member_id"]), array_keys(self::getRegisteredUsers($rUserInfo["id"])))) {
                        return true;
                    }
                }
            }
            if (!($rType == 'adv' && $rPermissions['is_admin'])) {
                return false;
            }

            if (0 < count($rPermissions['advanced']) && $rUserInfo['member_group_id'] != 1) {
                return in_array($rID, ($rPermissions['advanced'] ?: array()));
            }
            return true;
        }
        return false;
    }

    public static function getPageName() {
        return strtolower(basename(get_included_files()[0], '.php'));
    }

    public static function checkPermissions($rPage = null) {
        if (!$rPage) {
            $rPage = strtolower(basename($_SERVER['SCRIPT_FILENAME'], '.php'));
        }
        switch ($rPage) {
            case 'server_install':
                return self::hasPermissions('adv', 'add_server');
            case 'backups':
            case 'cache':
            case 'setup':
                return self::hasPermissions('adv', 'database');
            case 'server':
                if (isset(CoreUtilities::$request['id']) && self::hasPermissions('adv', 'edit_server')) {
                    return true;
                }
                if (isset(CoreUtilities::$request['id']) || !self::hasPermissions('adv', 'add_server')) {
                    break;
                }
                return true;
            case 'reg_user':
                if (isset(CoreUtilities::$request['id']) && self::hasPermissions('adv', 'edit_reguser')) {
                    return true;
                }

                if (isset(CoreUtilities::$request['id']) || !self::hasPermissions('adv', 'add_reguser')) {
                    break;
                }
                return true;
            default:
                return true;
        }
    }

    public static function goHome() {
        header('Location: dashboard');
        exit();
    }

    public static function verifyPostTable($rTable, $rData = array(), $rOnlyExisting = false) {
        $rReturn = array();
        self::$ipTV_db->query('SELECT `column_name`, `column_default`, `is_nullable`, `data_type` FROM `information_schema`.`columns` WHERE `table_schema` = (SELECT DATABASE()) AND `table_name` = ? ORDER BY `ordinal_position`;', $rTable);

        foreach (self::$ipTV_db->get_rows() as $rRow) {
            if ($rRow['column_default'] == 'NULL') {
                $rRow['column_default'] = null;
            }
            $rForceDefault = false;
            if ($rRow['is_nullable'] == 'NO' || !$rRow['column_default']) {
                if (in_array($rRow['data_type'], array('int', 'float', 'tinyint', 'double', 'decimal', 'smallint', 'mediumint', 'bigint', 'bit'))) {
                    $rRow['column_default'] = 0;
                } else {
                    $rRow['column_default'] = '';
                }
                $rForceDefault = true;
            }
            if (array_key_exists($rRow['column_name'], $rData)) {
                if (empty($rData[$rRow['column_name']]) && !is_numeric($rData[$rRow['column_name']]) && is_null($rRow['column_default'])) {
                    $rReturn[$rRow['column_name']] = ($rForceDefault ? $rRow['column_default'] : null);
                } else {
                    $rReturn[$rRow['column_name']] = $rData[$rRow['column_name']];
                }
            } else {
                if (!$rOnlyExisting) {
                    $rReturn[$rRow['column_name']] = $rRow['column_default'];
                }
            }
        }
        return $rReturn;
    }

    public static function preparecolumn($rValue) {
        return strtolower(preg_replace('/[^a-z0-9_]+/i', '', $rValue));
    }

    /**
     * Prepares an array for SQL operations by formatting its keys and values.
     *
     * This function takes an associative array and prepares it for use in SQL queries.
     * It handles column names, placeholders for prepared statements, and data formatting.
     *
     * @param array $rArray The input associative array to be processed.
     *
     * @return array An array with the following keys:
     *               - 'placeholder': A string of comma-separated question marks for prepared statements.
     *               - 'columns': A string of comma-separated, backtick-enclosed column names.
     *               - 'data': An array of values, with arrays JSON-encoded and 'null' values set to null.
     *               - 'update': A string formatted for SQL UPDATE statements (column = ?).
     */
    public static function prepareArray($rArray) {
        $rUpdate = $rColumns = $rPlaceholder = $rData = array();
        foreach (array_keys($rArray) as $rKey) {
            $rColumns[] = '`' . self::preparecolumn($rKey) . '`';
            $rUpdate[] = '`' . self::preparecolumn($rKey) . '` = ?';
        }
        foreach (array_values($rArray) as $rValue) {
            if (is_array($rValue)) {
                $rValue = json_encode($rValue, JSON_UNESCAPED_UNICODE);
            } else {
                if (!(is_null($rValue) || strtolower($rValue) == 'null')) {
                } else {
                    $rValue = null;
                }
            }
            $rPlaceholder[] = '?';
            $rData[] = $rValue;
        }
        return array('placeholder' => implode(',', $rPlaceholder), 'columns' => implode(',', $rColumns), 'data' => $rData, 'update' => implode(',', $rUpdate));
    }

    public static function resetSTB($rID) {
        self::$ipTV_db->query("UPDATE `mag_devices` SET `ip` = NULL, `ver` = NULL, `image_version` = NULL, `stb_type` = NULL, `sn` = NULL, `device_id` = NULL, `device_id2` = NULL, `hw_version` = NULL, `token` = NULL WHERE `mag_id` = ?;", $rID);
    }

    public static function getSettings() {
        self::$ipTV_db->query("SELECT * FROM `settings`;");
        foreach (self::$ipTV_db->get_rows() as $row) {
            $return[$row["name"]] = $row["value"];
        }
        return $return;
    }

    public static function getPanelLogs() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `panel_logs` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getStreamingServersByID($rID) {
        self::$ipTV_db->query("SELECT * FROM `servers` WHERE `id` = ?;", $rID);
        if (self::$ipTV_db->num_rows() > 0) {
            return self::$ipTV_db->get_row();
        }
        return false;
    }

    public static function getStreamList() {
        $return = array();
        self::$ipTV_db->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` ORDER BY `streams`.`stream_display_name` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getConnections($rServerID) {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `lines_live` WHERE `server_id` = '" . $rServerID . "';");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getEPGSources() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `epg`;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[$row["id"]] = $row;
            }
        }
        return $return;
    }

    public static function findEPG($rEPGName) {
        self::$ipTV_db->query("SELECT `id`, `data` FROM `epg`;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                foreach (json_decode($row["data"], true) as $rChannelID => $rChannelData) {
                    if ($rChannelID == $rEPGName) {
                        if (count($rChannelData["langs"]) > 0) {
                            $rEPGLang = $rChannelData["langs"][0];
                        } else {
                            $rEPGLang = "";
                        }
                        return array("channel_id" => $rChannelID, "epg_lang" => $rEPGLang, "epg_id" => intval($row["id"]));
                    }
                }
            }
        }
        return null;
    }

    public static function getStreamArguments() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `streams_arguments` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[$row["argument_key"]] = $row;
            }
        }
        return $return;
    }

    public static function getTranscodeProfiles() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `transcoding_profiles` ORDER BY `profile_id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getWatchFolders($rType = null) {
        $return = array();
        if ($rType) {
            self::$ipTV_db->query("SELECT * FROM `watch_folders` WHERE `type` = '" . $rType . "' ORDER BY `id` ASC;");
        } else {
            self::$ipTV_db->query("SELECT * FROM `watch_folders` ORDER BY `id` ASC;");
        }
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getWatchCategories($rType = null) {
        $return = array();
        if ($rType) {
            self::$ipTV_db->query("SELECT * FROM `watch_categories` WHERE `type` = " . intval($rType) . " ORDER BY `genre_id` ASC;");
        } else {
            self::$ipTV_db->query("SELECT * FROM `watch_categories` ORDER BY `genre_id` ASC;");
        }
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[$row["genre_id"]] = $row;
            }
        }
        return $return;
    }

    public static function getWatchFolder($rID) {
        self::$ipTV_db->query("SELECT * FROM `watch_folders` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getSeriesByTMDB($rID) {
        self::$ipTV_db->query("SELECT * FROM `series` WHERE `tmdb_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getSeries() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `series` ORDER BY `title` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getSerie($rID) {
        self::$ipTV_db->query("SELECT * FROM `series` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getUserAgents() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `blocked_user_agents` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getISPs() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `isp_addon` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getBlockedIPs() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `blocked_ips` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getSystemLogs() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `mysql_syslog` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getBlockedLogins() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `login_flood` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getRTMPIPs() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `rtmp_ips` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getStream($rID) {
        self::$ipTV_db->query("SELECT * FROM `streams` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getEPG($rID) {
        self::$ipTV_db->query("SELECT * FROM `epg` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getStreamOptions($rID) {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `streams_options` WHERE `stream_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["argument_id"])] = $row;
            }
        }
        return $return;
    }

    public static function getStreamSys($rID) {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `streams_servers` WHERE `stream_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["server_id"])] = $row;
            }
        }
        return $return;
    }

    public static function getRegisteredUsers($rOwner = null, $rIncludeSelf = true) {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `reg_users` ORDER BY `username` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if ((!$rOwner) or ($row["owner_id"] == $rOwner) or (($row["id"] == $rOwner) && ($rIncludeSelf))) {
                    $return[intval($row["id"])] = $row;
                }
            }
        }
        if (count($return) == 0) {
            $return[-1] = array();
        }
        return $return;
    }

    public static function getMemberGroups() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `member_groups` ORDER BY `group_id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["group_id"])] = $row;
            }
        }
        return $return;
    }

    public static function getMemberGroup($rID) {
        self::$ipTV_db->query("SELECT * FROM `member_groups` WHERE `group_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getOutputs($rUser = null) {
        $return = array();
        if ($rUser) {
            self::$ipTV_db->query("SELECT `allowed_outputs` FROM `lines` WHERE `id` = " . intval($rUser) . ";");
        } else {
            self::$ipTV_db->query("SELECT * FROM `access_output` ORDER BY `access_output_id` ASC;");
        }
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if ($rUser) {
                    $return = json_decode($row["allowed_outputs"]);
                } else {
                    $return[] = $row;
                }
            }
        }
        return $return;
    }

    public static function getUserBouquets() {
        $return = array();
        self::$ipTV_db->query("SELECT `id`, `bouquet` FROM `lines` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["id"])] = $row;
            }
        }
        return $return;
    }

    public static function getBouquets() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["id"])] = $row;
            }
        }
        return $return;
    }

    public static function getBouquetOrder() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["id"])] = $row;
            }
        }
        return $return;
    }

    public static function getBouquet($rID) {
        self::$ipTV_db->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function addToBouquet($rType, $rBouquetID, $rID) {
        $rBouquet = self::getBouquet($rBouquetID);
        if ($rBouquet) {
            if ($rType == "stream") {
                $rColumn = "bouquet_channels";
            } elseif ($rType == "movie") {
                $rColumn = "bouquet_movies";
            } elseif ($rType == "radio") {
                $rColumn = "bouquet_radios";
            } else {
                $rColumn = "bouquet_series";
            }
            $rChannels = json_decode($rBouquet[$rColumn], true);
            if (!in_array($rID, $rChannels)) {
                $rChannels[] = $rID;
                if (count($rChannels) > 0) {
                    self::$ipTV_db->query("UPDATE `bouquets` SET `" . $rColumn . "` = '" . json_encode(array_values($rChannels)) . "' WHERE `id` = " . intval($rBouquetID) . ";");
                }
            }
        }
    }

    public static function removeFromBouquet($rType, $rBouquetID, $rID) {
        $rBouquet = self::getBouquet($rBouquetID);
        if ($rBouquet) {
            if ($rType == "stream") {
                $rColumn = "bouquet_channels";
            } elseif ($rType == "movie") {
                $rColumn = "bouquet_movies";
            } elseif ($rType == "radio") {
                $rColumn = "bouquet_radios";
            } else {
                $rColumn = "bouquet_series";
            }
            $rChannels = json_decode($rBouquet[$rColumn], true);
            if (($rKey = array_search($rID, $rChannels)) !== false) {
                unset($rChannels[$rKey]);
                self::$ipTV_db->query("UPDATE `bouquets` SET `" . $rColumn . "` = '" . json_encode(array_values($rChannels)) . "' WHERE `id` = " . intval($rBouquetID) . ";");
            }
        }
    }

    public static function getPackages($rGroup = null) {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `packages` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if ((!isset($rGroup)) or (in_array(intval($rGroup), json_decode($row["groups"], true)))) {
                    $return[intval($row["id"])] = $row;
                }
            }
        }
        return $return;
    }

    public static function getPackage($rID) {
        self::$ipTV_db->query("SELECT * FROM `packages` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getTranscodeProfile($rID) {
        self::$ipTV_db->query("SELECT * FROM `transcoding_profiles` WHERE `profile_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getUserAgent($rID) {
        self::$ipTV_db->query("SELECT * FROM `blocked_user_agents` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getISP($rID) {
        self::$ipTV_db->query("SELECT * FROM `isp_addon` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getRTMPIP($rID) {
        self::$ipTV_db->query("SELECT * FROM `rtmp_ips` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getEPGs() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `epg` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["id"])] = $row;
            }
        }
        return $return;
    }

    public static function getCategory($rID) {
        self::$ipTV_db->query("SELECT * FROM `stream_categories` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return false;
    }

    public static function getMag($rID) {
        self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `mag_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            $row = self::$ipTV_db->get_row();
            self::$ipTV_db->query("SELECT `pair_id` FROM `lines` WHERE `id` = " . intval($row["user_id"]) . ";");
            if (self::$ipTV_db->num_rows() == 1) {
                $magrow = self::$ipTV_db->get_row();
                $row["paired_user"] = $magrow["pair_id"];
                $row["username"] = self::getUser($row["paired_user"])["username"];
            }
            return $row;
        }
        return array();
    }

    public static function getEnigma($rID) {
        self::$ipTV_db->query("SELECT * FROM `enigma2_devices` WHERE `device_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            $row = self::$ipTV_db->get_row();
            self::$ipTV_db->query("SELECT `pair_id` FROM `lines` WHERE `id` = " . intval($row["user_id"]) . ";");
            if (self::$ipTV_db->num_rows() == 1) {
                $e2row = self::$ipTV_db->get_row();
                $row["paired_user"] = $e2row["pair_id"];
                $row["username"] = self::getUser($row["paired_user"])["username"];
            }
            return $row;
        }
        return array();
    }

    public static function getMAGUser($rID) {
        self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `user_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return "";
    }

    public static function getE2User($rID) {
        self::$ipTV_db->query("SELECT * FROM `enigma2_devices` WHERE `user_id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return "";
    }

    public static function getTicket($rID) {
        self::$ipTV_db->query("SELECT * FROM `tickets` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() > 0) {
            $row = self::$ipTV_db->get_row();
            $row["replies"] = array();
            $row["title"] = htmlspecialchars($row["title"]);
            self::$ipTV_db->query("SELECT * FROM `tickets_replies` WHERE `ticket_id` = " . intval($rID) . " ORDER BY `date` ASC;");
            while ($reply = self::$ipTV_db->get_row()) {
                // Hack to fix display issues on short text.
                $reply["message"] = htmlspecialchars($reply["message"]);
                if (strlen($reply["message"]) < 80) {
                    $reply["message"] .= str_repeat("&nbsp; ", 80 - strlen($reply["message"]));
                }
                $row["replies"][] = $reply;
            }
            $row["user"] = self::getRegisteredUser($row["member_id"]);
            return $row;
        }
        return null;
    }

    public static function getExpiring($rID) {
        $rAvailableMembers = array_keys(self::getRegisteredUsers($rID));
        $return = array();
        self::$ipTV_db->query("SELECT `id`, `member_id`, `username`, `password`, `exp_date` FROM `lines` WHERE `member_id` IN (" . join(",", $rAvailableMembers) . ") AND `exp_date` >= UNIX_TIMESTAMP() ORDER BY `exp_date` ASC LIMIT 100;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function getTickets($rID = null) {
        $return = array();
        if ($rID) {
            self::$ipTV_db->query("SELECT `tickets`.`id`, `tickets`.`member_id`, `tickets`.`title`, `tickets`.`status`, `tickets`.`admin_read`, `tickets`.`user_read`, `reg_users`.`username` FROM `tickets`, `reg_users` WHERE `member_id` = " . intval($rID) . " AND `reg_users`.`id` = `tickets`.`member_id` ORDER BY `id` DESC;");
        } else {
            self::$ipTV_db->query("SELECT `tickets`.`id`, `tickets`.`member_id`, `tickets`.`title`, `tickets`.`status`, `tickets`.`admin_read`, `tickets`.`user_read`, `reg_users`.`username` FROM `tickets`, `reg_users` WHERE `reg_users`.`id` = `tickets`.`member_id` ORDER BY `id` DESC;");
        }
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                self::$ipTV_db->query("SELECT MIN(`date`) AS `date` FROM `tickets_replies` WHERE `ticket_id` = " . intval($row["id"]) . " AND `admin_reply` = 0;");
                if ($rDate = self::$ipTV_db->get_row()["date"]) {
                    $row["created"] = date("Y-m-d H:i", $rDate);
                } else {
                    $row["created"] = "";
                }
                self::$ipTV_db->query("SELECT MAX(`date`) AS `date` FROM `tickets_replies` WHERE `ticket_id` = " . intval($row["id"]) . " AND `admin_reply` = 1;");
                if ($rDate = self::$ipTV_db->get_row()["date"]) {
                    $row["last_reply"] = date("Y-m-d H:i", $rDate);
                } else {
                    $row["last_reply"] = "";
                }
                if ($row["status"] <> 0) {
                    if ($row["user_read"] == 0) {
                        $row["status"] = 2;
                    }
                    if ($row["admin_read"] == 1) {
                        $row["status"] = 3;
                    }
                }
                $return[] = $row;
            }
        }
        return $return;
    }

    public static function checkTrglobalials() {
        global $rUserInfo, $rPermissions;
        $rTotal = $rPermissions["total_allowed_gen_trials"];
        if ($rTotal > 0) {
            $rTotalIn = $rPermissions["total_allowed_gen_in"];
            if ($rTotalIn == "hours") {
                $rTime = time() - (intval($rTotal) * 3600);
            } else {
                $rTime = time() - (intval($rTotal) * 3600 * 24);
            }
            self::$ipTV_db->query("SELECT COUNT(`id`) AS `count` FROM `lines` WHERE `member_id` = " . intval($rUserInfo["id"]) . " AND `created_at` >= " . $rTime . " AND `is_trial` = 1;");
            return self::$ipTV_db->get_row()["count"] < $rTotal;
        }
        return false;
    }

    public static function getSubresellerSetups() {
        $return = array();
        self::$ipTV_db->query("SELECT * FROM `subreseller_setup` ORDER BY `id` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["id"])] = $row;
            }
        }
        return $return;
    }

    public static function getSubresellerSetup($rID) {
        self::$ipTV_db->query("SELECT * FROM `subreseller_setup` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            return self::$ipTV_db->get_row();
        }
        return null;
    }

    public static function getEpisodeParents() {
        $return = array();
        self::$ipTV_db->query("SELECT `series_episodes`.`stream_id`, `series`.`id`, `series`.`title` FROM `series_episodes` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id`;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["stream_id"])] = $row;
            }
        }
        return $return;
    }

    public static function getSeriesList() {
        $return = array();
        self::$ipTV_db->query("SELECT `id`, `title` FROM `series` ORDER BY `title` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[intval($row["id"])] = $row;
            }
        }
        return $return;
    }

    public static function getWorldMapLive() {
        $rQuery = "SELECT geoip_country_code, count(geoip_country_code) AS total FROM lines_live GROUP BY geoip_country_code";
        if (self::$ipTV_db->query($rQuery)) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $WorldMapLive = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
                echo $WorldMapLive;
            }
        }
    }

    public static function getSelections($rSources) {
        $return = array();
        foreach ($rSources as $rSource) {
            self::$ipTV_db->query("SELECT `id` FROM `streams` WHERE `type` IN (2,5) AND `stream_source` LIKE '%" . str_replace("/", "\/", $rSource) . "\"%' ESCAPE '|' LIMIT 1;");
            if (self::$ipTV_db->num_rows() == 1) {
                $return[] = intval(self::$ipTV_db->get_row()["id"]);
            }
        }
        return $return;
    }

    public static function getWorldMapActivity() {
        $rQuery = "SELECT DISTINCT geoip_country_code, COUNT(DISTINCT user_id) AS total FROM user_activity GROUP BY geoip_country_code";
        if (self::$ipTV_db->query($rQuery)) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $WorldMapActivity = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
                echo $WorldMapActivity;
            }
        }
    }

    public static function getNextOrder() {
        self::$ipTV_db->query("SELECT MAX(`order`) AS `order` FROM `streams`;");
        if (self::$ipTV_db->num_rows() == 1) {
            return intval(self::$ipTV_db->get_row()["order"]) + 1;
        }
        return 0;
    }

    public static function generateSeriesPlaylist($rSeriesNo) {
        $rReturn = array("success" => false, "sources" => array(), "server_id" => 0);
        self::$ipTV_db->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesNo) . " ORDER BY `season_num` ASC, `sort` ASC;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                self::$ipTV_db->query("SELECT `stream_source` FROM `streams` WHERE `id` = " . intval($row["stream_id"]) . ";");
                if (self::$ipTV_db->num_rows() > 0) {
                    $rSource = json_decode(self::$ipTV_db->get_row()["stream_source"], true)[0];
                    $rSplit = explode(":", $rSource);
                    $rFilename = join(":", array_slice($rSplit, 2, count($rSplit) - 2));
                    $rServerID = intval($rSplit[1]);
                    if ($rReturn["server_id"] == 0) {
                        $rReturn["server_id"] = $rServerID;
                        $rReturn["success"] = true;
                    }
                    if ($rReturn["server_id"] <> $rServerID) {
                        $rReturn["success"] = false;
                        break;
                    }
                    $rReturn["sources"][] = $rFilename;
                }
            }
        }
        return $rReturn;
    }

    public static function flushLogins() {
        self::$ipTV_db->query("DELETE FROM `login_flood`;");
    }

    public static function updateTMDbCategories() {
        include INCLUDES_PATH . 'libs/tmdb.php';
        if (strlen($rSettings["tmdb_language"]) > 0) {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
        } else {
            $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
        }
        $rCurrentCats = array(1 => array(), 2 => array());
        self::$ipTV_db->query("SELECT `id`, `type`, `genre_id` FROM `watch_categories`;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if (in_array($row["genre_id"], $rCurrentCats[$row["type"]])) {
                    self::$ipTV_db->query("DELETE FROM `watch_categories` WHERE `id` = " . intval($row["id"]) . ";");
                }
                $rCurrentCats[$row["type"]][] = $row["genre_id"];
            }
        }
        $rMovieGenres = $rTMDB->getMovieGenres();
        foreach ($rMovieGenres as $rMovieGenre) {
            if (!in_array($rMovieGenre->getID(), $rCurrentCats[1])) {
                self::$ipTV_db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(1, " . intval($rMovieGenre->getID()) . ", '" . $rMovieGenre->getName() . "', 0, '[]');");
            }
            if (!in_array($rMovieGenre->getID(), $rCurrentCats[2])) {
                self::$ipTV_db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(2, " . intval($rMovieGenre->getID()) . ", '" . $rMovieGenre->getName() . "', 0, '[]');");
            }
        }
        $rTVGenres = $rTMDB->getTVGenres();
        foreach ($rTVGenres as $rTVGenre) {
            if (!in_array($rTVGenre->getID(), $rCurrentCats[1])) {
                self::$ipTV_db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(1, " . intval($rTVGenre->getID()) . ", '" . $rTVGenre->getName() . "', 0, '[]');");
            }
            if (!in_array($rTVGenre->getID(), $rCurrentCats[2])) {
                self::$ipTV_db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(2, " . intval($rTVGenre->getID()) . ", '" . $rTVGenre->getName() . "', 0, '[]');");
            }
        }
    }

    /**
     * Fetches the latest release and pre-release information from a GitHub repository
     *
     * @param string $repo The repository name in format "owner/repository"
     *
     * @return array{
     *     latest_release?: string|null,
     *     latest_prerelease?: string|null,
     *     error?: string
     * } Returns an array containing:
     *           - latest_release: The tag name of the latest stable release (null if none found)
     *           - latest_prerelease: The tag name of the latest pre-release (null if none found)
     *           - error: Error message if the request fails
     *
     * @throws Exception When the GitHub API request fails or returns invalid data
     */
    public static function getGithubReleases(string $repo): array {
        $url = "https://api.github.com/repos/$repo/releases";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: PHP-Request'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => 'Request error: ' . curl_error($ch)];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => "GitHub API returned HTTP code $httpCode"];
        }

        $releases = json_decode($response, true);
        if (empty($releases)) {
            return ['error' => 'No releases found'];
        }

        $latestRelease = null;
        $latestPrerelease = null;

        foreach ($releases as $release) {
            if (!$release['prerelease'] && !$latestRelease) {
                $latestRelease = $release['tag_name'];
            }
            if ($release['prerelease'] && !$latestPrerelease) {
                $latestPrerelease = $release['tag_name'];
            }

            if ($latestRelease && $latestPrerelease) {
                break;
            }
        }

        return [
            'latest_release' => $latestRelease,
            'latest_prerelease' => $latestPrerelease
        ];
    }

    /**
     * Retrieves the most recent stable release version from a given URL.
     *
     * This function sends a HEAD request to the provided URL, follows any redirects,
     * and attempts to extract the version number from the final URL's basename.
     * It assumes the version is the basename of the URL, minus the first character.
     *
     * @param string $url The URL to check for the latest stable release.
     *
     * @return string|false The extracted version number as a string, or false on failure.
     *                      The returned version string does not include the first character
     *                      of the basename (typically removing a 'v' prefix if present).
     *
     * @throws Exception If there's an issue with the cURL request or version extraction.
     *                   The exception message will be logged using error_log().
     *
     */
    public static function get_recent_stable_release(string $url) {
        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        // Execute cURL request
        $result = curl_exec($ch);

        if ($result === false) {
            error_log("cURL Error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        // Get the effective URL after following redirects
        $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        // Close cURL session
        curl_close($ch);

        // Extract the version from the URL
        $version = basename($effective_url);

        if (empty($version)) {
            error_log("Error: Could not extract version from URL");
            return false;
        }

        return $version;
    }

    public static function sortArrayByArray(array $rArray, array $rSort) {
        $rOrdered = array();
        foreach ($rSort as $rValue) {
            if (($rKey = array_search($rValue, $rArray)) !== false) {
                $rOrdered[] = $rValue;
                unset($rArray[$rKey]);
            }
        }
        return $rOrdered + $rArray;
    }

    public static function updateGeoLite2() {
        $rGeoLite2Latest = self::get_recent_stable_release("https://github.com/Vateron-Media/Xtream_Update/releases/latest");
        $rGeoLite2Curent = json_decode(file_get_contents("/home/xc_vm/bin/maxmind/version.json"), true)["geolite2_version"];
        if ($rGeoLite2Latest == $rGeoLite2Curent) {
            return true;
        }
        if ($rGeoLite2Latest) {
            $fileNames = ["GeoLite2-City.mmdb", "GeoLite2-Country.mmdb", "GeoLite2-ASN.mmdb"];
            $checker = [false, false, false];
            foreach ($fileNames as $key => $value) {
                $rFileData = file_get_contents("https://github.com/Vateron-Media/Xtream_Update/releases/download/{$rGeoLite2Latest}/{$value}");
                if (stripos($rFileData, "MaxMind.com") !== false) {
                    $rFilePath = "/home/xc_vm/bin/maxmind/{$value}";
                    exec("sudo chattr -i {$rFilePath}");
                    unlink($rFilePath);
                    file_put_contents($rFilePath, $rFileData);
                    exec("sudo chmod 644 {$rFilePath}");
                    exec("sudo chattr +i {$rFilePath}");
                    if (file_get_contents($rFilePath) == $rFileData) {
                        $checker[$key] = true;
                    }
                }
            }
            if ($checker[0] && $checker[1] && $checker[2]) {
                # create json version file and write version geolite
                $versionFile = "/home/xc_vm/bin/maxmind/version.json";
                $data = ["geolite2_version" => $rGeoLite2Latest];
                $json = json_encode($data);
                unlink($versionFile);
                file_put_contents($versionFile, $json);
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public static function APIRequest($rData, $rTimeout = 5) {
        ini_set('default_socket_timeout', $rTimeout);
        $rAPI = 'http://127.0.0.1:' . intval(CoreUtilities::$Servers[SERVER_ID]['http_broadcast_port']) . '/admin/api';

        if (!empty(CoreUtilities::$settings['api_pass'])) {
            $rData['api_pass'] = CoreUtilities::$settings['api_pass'];
        }

        $rPost = http_build_query($rData);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rAPI);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rPost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $rTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $rTimeout);

        return curl_exec($ch);
    }

    public static function SystemAPIRequest($rServerID, $rData) {
        global $rServers, $rSettings;
        ini_set('default_socket_timeout', 5);
        $rAPI = "http://" . $rServers[intval($rServerID)]["server_ip"] . ":" . $rServers[intval($rServerID)]["http_broadcast_port"] . "/api.php";
        $rData["password"] = $rSettings["live_streaming_pass"];
        $rPost = http_build_query($rData);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rAPI);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rPost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rData = curl_exec($ch);
        return $rData;
    }

    /**
     * Retrieves a list of running processes (PIDs) from a remote server.
     *
     * @param int $rServerID The ID of the server to retrieve the process list from.
     *
     * @return array An array of processes, each containing:
     *               - `user` (string): The user who owns the process.
     *               - `pid` (int): The process ID.
     *               - `cpu` (float): CPU usage percentage.
     *               - `mem` (float): Memory usage percentage.
     *               - `vsz` (int): Virtual memory size.
     *               - `rss` (int): Resident set size (physical memory used).
     *               - `tty` (string): Terminal associated with the process.
     *               - `stat` (string): Process state.
     *               - `start` (string): Start time of the process.
     *               - `time` (string): Total CPU time used.
     *               - `command` (string): Command that started the process.
     */
    public static function getPIDs($rServerID) {
        $rReturn = array();
        $rProcesses = json_decode(self::SystemAPIRequest($rServerID, array('action' => 'get_pids')), true);
        array_shift($rProcesses);
        foreach ($rProcesses as $rProcess) {
            $rSplit = explode(" ", preg_replace('!\s+!', ' ', trim($rProcess)));
            if (strlen($rSplit[0]) > 0) {
                $rReturn[] = array("user" => $rSplit[0], "pid" => $rSplit[1], "cpu" => $rSplit[2], "mem" => $rSplit[3], "vsz" => $rSplit[4], "rss" => $rSplit[5], "tty" => $rSplit[6], "stat" => $rSplit[7], "start" => $rSplit[8], "time" => $rSplit[9], "command" => join(" ", array_splice($rSplit, 10, count($rSplit) - 10)));
            }
        }
        return $rReturn;
    }

    /**
     * Retrieves free disk space information from a remote server.
     *
     * @param int $rServerID The ID of the server to check for free space.
     *
     * @return array An array of disk space details, where each entry contains:
     *               - `filesystem` (string): The name of the filesystem.
     *               - `size` (string): The total size of the filesystem.
     *               - `used` (string): The amount of space used.
     *               - `avail` (string): The amount of available space.
     *               - `percentage` (string): The percentage of space used.
     *               - `mount` (string): The mount point of the filesystem.
     */
    public static function getFreeSpace($rServerID) {
        $rReturn = array();
        $rLines = json_decode(self::SystemAPIRequest($rServerID, array('action' => 'get_free_space')), true);
        array_shift($rLines);

        foreach ($rLines as $rLine) {
            $rSplit = explode(' ', preg_replace('!\\s+!', ' ', trim($rLine)));
            if (0 < strlen($rSplit[0]) && strpos($rSplit[5], 'xc_vm') !== false || $rSplit[5] == '/') {
                $rReturn[] = array('filesystem' => $rSplit[0], 'size' => $rSplit[1], 'used' => $rSplit[2], 'avail' => $rSplit[3], 'percentage' => $rSplit[4], 'mount' => implode(' ', array_slice($rSplit, 5, count($rSplit) - 5)));
            }
        }

        return $rReturn;
    }

    public static function freeTemp($rServerID) {
        self::SystemAPIRequest($rServerID, array('action' => 'free_temp'));
    }

    public static function freeStreams($rServerID) {
        self::SystemAPIRequest($rServerID, array('action' => 'free_streams'));
    }


    public static function getStreamPIDs($rServerID) {
        $return = array();
        self::$ipTV_db->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `streams`.`type`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`delay_pid` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `streams_servers`.`server_id` = " . intval($rServerID) . ";");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                foreach (array("pid", "monitor_pid", "delay_pid") as $rPIDType) {
                    if ($row[$rPIDType]) {
                        $return[$row[$rPIDType]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => $rPIDType);
                    }
                }
            }
        }
        self::$ipTV_db->query("SELECT `id`, `stream_display_name`, `type`, `tv_archive_pid` FROM `streams` WHERE `tv_archive_server_id` = " . intval($rServerID) . ";");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if ($row["pid"]) {
                    $return[$row["pid"]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => "timeshift");
                }
            }
        }
        self::$ipTV_db->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `streams`.`type`, `lines_live`.`pid` FROM `lines_live` LEFT JOIN `streams` ON `streams`.`id` = `lines_live`.`stream_id` WHERE `lines_live`.`server_id` = " . intval($rServerID) . ";");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if ($row["pid"]) {
                    $return[$row["pid"]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => "activity");
                }
            }
        }
        return $return;
    }

    public static function roundUpToAny($n, $x = 5) {
        return round(($n + $x / 2) / $x) * $x;
    }

    public static function checkSource($rServerID, $rFilename) {
        global $rServers, $rSettings;
        $rAPI = "http://" . $rServers[intval($rServerID)]["server_ip"] . ":" . $rServers[intval($rServerID)]["http_broadcast_port"] . "/api.php?password=" . $rSettings["live_streaming_pass"] . "&action=getFile&filename=" . urlencode(escapeshellcmd($rFilename));
        $rCommand = 'timeout 5 ' . MAIN_DIR . 'bin/ffprobe -show_streams -v quiet "' . $rAPI . '" -of json';
        return json_decode(shell_exec($rCommand), true);
    }

    public static function tmdbParseRelease($Release) {
        $rCommand = "/usr/bin/python " . INCLUDES_PATH . "python/release.py \"" . escapeshellcmd($Release) . "\"";
        return json_decode(shell_exec($rCommand), true);
    }

    public static function listDir($rServerID, $rDirectory, $rAllowed = null) {
        global $_INFO;
        set_time_limit(60);
        ini_set('max_execution_time', 60);
        $rReturn = array("dirs" => array(), "files" => array());
        if ($rServerID == $_INFO["server_id"]) {
            $rFiles = scanDir($rDirectory);
            foreach ($rFiles as $rKey => $rValue) {
                if (!in_array($rValue, array(".", ".."))) {
                    if (is_dir($rDirectory . "/" . $rValue)) {
                        $rReturn["dirs"][] = $rValue;
                    } else {
                        $rExt = strtolower(pathinfo($rValue)["extension"]);
                        if (((is_array($rAllowed)) && (in_array($rExt, $rAllowed))) or (!$rAllowed)) {
                            $rReturn["files"][] = $rValue;
                        }
                    }
                }
            }
        } else {
            $rData = self::SystemAPIRequest($rServerID, array('action' => 'viewDir', 'dir' => $rDirectory));
            $rDocument = new DOMDocument();
            $rDocument->loadHTML($rData);
            $rFiles = $rDocument->getElementsByTagName('li');
            foreach ($rFiles as $rFile) {
                if (stripos($rFile->getAttribute('class'), "directory") !== false) {
                    $rReturn["dirs"][] = $rFile->nodeValue;
                } elseif (stripos($rFile->getAttribute('class'), "file") !== false) {
                    $rExt = strtolower(pathinfo($rFile->nodeValue)["extension"]);
                    if (((is_array($rAllowed)) && (in_array($rExt, $rAllowed))) or (!$rAllowed)) {
                        $rReturn["files"][] = $rFile->nodeValue;
                    }
                }
            }
        }
        return $rReturn;
    }

    public static function scanRecursive($rServerID, $rDirectory, $rAllowed = null) {
        $result = [];
        $rFiles = self::listDir($rServerID, $rDirectory, $rAllowed);
        foreach ($rFiles["files"] as $rFile) {
            $rFilePath = rtrim($rDirectory, "/") . '/' . $rFile;
            $result[] = $rFilePath;
        }
        foreach ($rFiles["dirs"] as $rDir) {
            foreach (self::scanRecursive($rServerID, rtrim($rDirectory, "/") . "/" . $rDir . "/", $rAllowed) as $rFile) {
                $result[] = $rFile;
            }
        }
        return $result;
    }

    public static function getEncodeErrors($rID) {
        $rServers = self::getStreamingServers(true);
        ini_set('default_socket_timeout', 3);
        $rErrors = array();
        $rStreamSys = self::getStreamSys($rID);
        foreach ($rStreamSys as $rServer) {
            $rServerID = $rServer["server_id"];
            if (isset($rServers[$rServerID])) {
                if (!($rServer["pid"] > 0 && $rServer["to_analyze"] == 0 && $rServer["stream_status"] <> 1)) {
                    $rFilename = CONTENT_PATH . "vod/" . intval($rID) . ".errors";
                    $rError = self::SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
                    if (strlen($rError) > 0) {
                        $rErrors[$rServerID] = $rError;
                    }
                }
            }
        }
        return $rErrors;
    }


    public static function getSeriesTrailer($rTMDBID) {
        global $rSettings;
        // Not implemented in TMDB PHP API...
        if (strlen($rSettings["tmdb_language"]) > 0) {
            $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/videos?api_key=" . $rSettings["tmdb_api_key"] . "&language=" . $rSettings["tmdb_language"];
        } else {
            $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/videos?api_key=" . $rSettings["tmdb_api_key"];
        }
        $rJSON = json_decode(file_get_contents($rURL), true);
        foreach ($rJSON["results"] as $rVideo) {
            if ((strtolower($rVideo["type"]) == "trailer") && (strtolower($rVideo["site"]) == "youtube")) {
                return $rVideo["key"];
            }
        }
        return "";
    }

    public static function getStills($rTMDBID, $rSeason, $rEpisode) {
        global $rSettings;

        // Not implemented in TMDB PHP API...
        if (strlen($rSettings["tmdb_language"]) > 0) {
            $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/season/" . $rSeason . "/episode/" . $rEpisode . "/images?api_key=" . $rSettings["tmdb_api_key"] . "&language=" . $rSettings["tmdb_language"];
        } else {
            $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/season/" . $rSeason . "/episode/" . $rEpisode . "/images?api_key=" . $rSettings["tmdb_api_key"];
        }
        return json_decode(file_get_contents($rURL), true);
    }

    // LEAKED LINES : For Show Restreamers, remove AND is_restreamer <1
    public static function getLeakedLines() {
        $return = array();
        self::$ipTV_db->query("SELECT FROM_BASE64(mac), username, user_activity.user_id, user_activity.container, user_activity.geoip_country_code, GROUP_CONCAT(DISTINCT user_ip), GROUP_CONCAT(DISTINCT container), GROUP_CONCAT(DISTINCT geoip_country_code), is_restreamer FROM user_activity
    INNER JOIN lines ON user_id = lines.id AND is_mag = 1
    INNER JOIN mag_devices ON lines.id = mag_devices.user_id
    WHERE 1 GROUP BY user_id HAVING COUNT(DISTINCT user_ip) > 1
    AND
    is_restreamer < 1
    UNION
    SELECT '', username, user_activity.user_id, user_activity.container, user_activity.geoip_country_code, GROUP_CONCAT(DISTINCT user_ip), GROUP_CONCAT(DISTINCT container), GROUP_CONCAT(DISTINCT geoip_country_code), is_restreamer FROM user_activity
    INNER JOIN lines ON user_id = lines.id AND is_mag = 0
    WHERE 1 GROUP BY user_id HAVING COUNT(DISTINCT user_ip) > 1
    AND
    is_restreamer < 1;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }

    // SECURITY CENTER
    public static function getSecurityCenter() {
        $return = array();
        self::$ipTV_db->query("SELECT Distinct lines.id, lines.username, SUBSTR(`streams`.`stream_display_name`, 1, 30) stream_display_name, lines.max_connections, (SELECT count(*) FROM `lines_live` WHERE `lines_live`.`stream_id` = `streams`.`id`) AS `active_connections`, (SELECT count(*) FROM `lines_live` WHERE `lines`.`id` = `lines_live`.`user_id`) AS `total_active_connections` FROM lines_live
    INNER JOIN `streams` ON `lines_live`.`stream_id` = `streams`.`id`
    LEFT JOIN lines ON user_id = lines.id WHERE (SELECT count(*) FROM `lines_live` WHERE `lines`.`id` = `lines_live`.`user_id`) > `lines`.`max_connections`
    AND
    is_restreamer < 1;");
        if (self::$ipTV_db->num_rows() > 0) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                $return[] = $row;
            }
        }
        return $return;
    }
    //############

    public static function downloadImage($rImage, $rType = null) {
        if (0 < strlen($rImage) && substr(strtolower($rImage), 0, 4) == 'http') {
            $rPathInfo = pathinfo($rImage);
            $rExt = $rPathInfo['extension'];
            if (!$rExt) {
                $rImageInfo = getimagesize($rImage);
                if ($rImageInfo['mime']) {
                    list(, $rExt) = explode('/', $rImageInfo['mime']);
                }
            }
            if (in_array(strtolower($rExt), array('jpg', 'jpeg', 'png'))) {
                $rFilename = ipTV_streaming::encryptData($rImage, CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);
                $rPrevPath = IMAGES_PATH . $rFilename . '.' . $rExt;
                if (file_exists($rPrevPath)) {
                    return 's:' . SERVER_ID . ':/images/' . $rFilename . '.' . $rExt;
                }
                $rCurl = curl_init();
                curl_setopt($rCurl, CURLOPT_URL, $rImage);
                curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($rCurl, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($rCurl, CURLOPT_TIMEOUT, 5);
                $rData = curl_exec($rCurl);
                if (strlen($rData) > 0) {
                    $rPath = IMAGES_PATH . $rFilename . '.' . $rExt;
                    file_put_contents($rPath, $rData);
                    if (file_exists($rPath)) {
                        return 's:' . SERVER_ID . ':/images/' . $rFilename . '.' . $rExt;
                    }
                }
            }
        }
        return $rImage;
    }

    public static function updateSeries($rID) {
        require_once INCLUDES_PATH . 'libs/tmdb.php';
        self::$ipTV_db->query("SELECT `tmdb_id` FROM `series` WHERE `id` = " . intval($rID) . ";");
        if (self::$ipTV_db->num_rows() == 1) {
            $rTMDBID = self::$ipTV_db->get_row()["tmdb_id"];
            if (strlen($rTMDBID) > 0) {
                if (strlen($rSettings["tmdb_language"]) > 0) {
                    $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rSettings["tmdb_language"]);
                } else {
                    $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
                }
                $rReturn = array();
                $rSeasons = json_decode($rTMDB->getTVShow($rTMDBID)->getJSON(), true)["seasons"];
                foreach ($rSeasons as $rSeason) {
                    if ($rSettings["download_images"]) {
                        $rSeason["cover"] = self::downloadImage("https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rSeason["poster_path"]);
                    } else {
                        $rSeason["cover"] = "https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rSeason["poster_path"];
                    }
                    $rSeason["cover_big"] = $rSeason["cover"];
                    unset($rSeason["poster_path"]);
                    $rReturn[] = $rSeason;
                }
                self::$ipTV_db->query("UPDATE `series` SET `seasons` = '" . json_encode($rReturn) . "', `last_modified` = " . intval(time()) . " WHERE `id` = " . intval($rID) . ";");
            }
        }
    }

    public static function getURL() {
        global $rServers, $_INFO;
        if (strlen($rServers[$_INFO["server_id"]]["domain_name"]) > 0) {
            return "http://" . $rServers[$_INFO["server_id"]]["domain_name"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
        } elseif (strlen($rServers[$_INFO["server_id"]]["private_ip"]) > 0) {
            return "http://" . $rServers[$_INFO["server_id"]]["private_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
        } else {
            return "http://" . $rServers[$_INFO["server_id"]]["server_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
        }
    }

    public static function clearSettingsCache() {
        unlink(CACHE_TMP_PATH . 'settings');
    }

    /**
     * Deletes a blocked IP entry from database and associated flood control file
     * 
     * Performs atomic removal of IP blocking record and its corresponding temp file.
     * First verifies existence before deletion to prevent errors.
     *
     * @param int $rID Database ID of the blocked IP record to remove
     * @return bool True if deletion succeeded, false if record not found
     *
     */
    public static function rdeleteBlockedIP($rID) {
        self::$ipTV_db->query('SELECT `id`, `ip` FROM `blocked_ips` WHERE `id` = ?;', $rID);

        if (0 >= self::$ipTV_db->num_rows()) {
            return false;
        }

        $rRow = self::$ipTV_db->get_row();
        self::$ipTV_db->query('DELETE FROM `blocked_ips` WHERE `id` = ?;', $rID);

        if (file_exists(FLOOD_TMP_PATH . 'block_' . $rRow['ip'])) {
            unlink(FLOOD_TMP_PATH . 'block_' . $rRow['ip']);
        }

        return true;
    }

    /**
     * Overwrites values in an existing data array with values from another array.
     * 
     * This function replaces values in `$data` with corresponding values from `$overwrite`,
     * except for keys listed in `$skip` or those that do not exist in `$data`.
     * 
     * @param array $data The original array that will be modified.
     * @param array $overwrite The array containing new values to overwrite in `$data`.
     * @param array $skip Optional. Keys that should not be overwritten.
     * @return array The modified `$data` array with overwritten values.
     */
    public static function overwriteData(array $data, array $overwrite, array $skip = []): array {
        foreach ($overwrite as $key => $value) {
            // Skip keys that do not exist in $data or are in the $skip list
            if (!array_key_exists($key, $data) || in_array($key, $skip, true)) {
                continue;
            }

            // If the new value is empty and the current value is null, keep it as null
            if (empty($value) && is_null($data[$key])) {
                $data[$key] = null;
                continue;
            }

            // Overwrite the value
            $data[$key] = $value;
        }
        return $data;
    }

    public static function deleteStream($rID, $rServerID = -1, $rDeleteFiles = true, $f2d619cb38696890 = true) {
        self::$ipTV_db->query('SELECT `id`, `type` FROM `streams` WHERE `id` = ?;', $rID);

        if (0 >= self::$ipTV_db->num_rows()) {
            return false;
        }

        $rType = self::$ipTV_db->get_row()['type'];
        $rRemaining = 0;

        if ($rServerID != -1) {
            self::$ipTV_db->query('SELECT `server_stream_id` FROM `streams_servers` WHERE `stream_id` = ? AND `server_id` <> ?;', $rID, $rServerID);
            $rRemaining = self::$ipTV_db->num_rows();
        }

        if ($rRemaining == 0 && $f2d619cb38696890) {
            self::$ipTV_db->query('DELETE FROM `lines_logs` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `mag_claims` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `streams` WHERE `id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `streams_episodes` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `streams_errors` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `streams_logs` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `streams_options` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `streams_stats` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `watch_refresh` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `watch_logs` WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('DELETE FROM `recordings` WHERE `created_id` = ? OR `stream_id` = ?;', $rID, $rID);
            self::$ipTV_db->query('UPDATE `lines_activity` SET `stream_id` = 0 WHERE `stream_id` = ?;', $rID);
            self::$ipTV_db->query('SELECT `server_id` FROM `streams_servers` WHERE `stream_id` = ?;', $rID);
            $rServerIDs = array();

            foreach (self::$ipTV_db->get_rows() as $rRow) {
                $rServerIDs[] = $rRow['server_id'];
            }

            if ($rDeleteFiles && 0 < count($rServerIDs) && in_array($rType, array(2, 5))) {
                self::deleteMovieFile($rServerIDs, $rID);
            }

            self::$ipTV_db->query('DELETE FROM `streams_servers` WHERE `stream_id` = ?;', $rID);
        } else {
            $rServerIDs = array($rServerID);
            self::$ipTV_db->query('DELETE FROM `streams_servers` WHERE `stream_id` = ? AND `server_id` = ?;', $rID, $rServerID);

            if ($rDeleteFiles && in_array($rType, array(2, 5))) {
                self::deleteMovieFile(array($rServerID), $rID);
            }
        }

        self::$ipTV_db->query('DELETE FROM `streams_servers` WHERE `parent_id` IS NOT NULL AND `parent_id` > 0 AND `parent_id` NOT IN (SELECT `id` FROM `servers` WHERE `server_type` = 0);');
        ipTV_streaming::updateStream($rID);
        self::scanBouquets();

        return true;
    }

    public static function parseM3U($rData, $rFile = true) {
        require_once INCLUDES_PATH . 'libs/m3u.php';
        $rParser = new M3uParser();
        $rParser->addDefaultTags();

        if ($rFile) {
            return $rParser->parseFile($rData);
        }

        $data = $rParser->parse($rData);
        return $data;
    }

    public static function checkTrials() {
        global $ipTV_db_admin, $rPermissions, $rUserInfo;
        $rTotal = $rPermissions["total_allowed_gen_trials"];
        if ($rTotal > 0) {
            $rTotalIn = $rPermissions["total_allowed_gen_in"];
            if ($rTotalIn == "hours") {
                $rTime = time() - (intval($rTotal) * 3600);
            } else {
                $rTime = time() - (intval($rTotal) * 3600 * 24);
            }
            $ipTV_db_admin->query("SELECT COUNT(`id`) AS `count` FROM `lines` WHERE `member_id` = " . intval($rUserInfo["id"]) . " AND `created_at` >= " . $rTime . " AND `is_trial` = 1;");
            return $ipTV_db_admin->get_row()["count"] < $rTotal;
        }
        return false;
    }
}
