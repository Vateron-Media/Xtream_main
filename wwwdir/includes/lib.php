<?php
class ipTV_lib {
    public static $request = array();
    public static $ipTV_db;
    public static $settings = array();
    public static $Bouquets = array();
    public static $StreamingServers = array();
    public static $SegmentsSettings = array();
    public static $blockedUA = array();
    public static $customISP = array();
    public static $blockedISP = array();
    public static $blockedIPs = array();
    public static $categories = array();

    public static function init() {
        global $_INFO;

        if (!empty($_GET)) {
            self::cleanGlobals($_GET);
        }
        if (!empty($_POST)) {
            self::cleanGlobals($_POST);
        }
        if (!empty($_SESSION)) {
            self::cleanGlobals($_SESSION);
        }
        if (!empty($_COOKIE)) {
            self::cleanGlobals($_COOKIE);
        }

        $input = @self::parseIncomingRecursively($_GET, array());
        self::$request = @self::parseIncomingRecursively($_POST, $input);
        self::$settings = self::getSettings();
        date_default_timezone_set(self::$settings["default_timezone"]);
        self::$StreamingServers = self::getServers();
        self::$blockedISP = self::getBlockedISP();
        self::$blockedIPs = self::getBlockedIPs();
        self::$categories = self::getCategories();

        if (FETCH_BOUQUETS) {
            self::$Bouquets = self::getBouquets();
        }
        self::$blockedUA = self::GetBlockedUserAgents();
        self::$customISP = self::GetIspAddon();

        if (!isset($_INFO["pconnect"])) {
            $_INFO["pconnect"] = null;
        }

        if (self::$StreamingServers[SERVER_ID]["persistent_connections"] != $_INFO["pconnect"]) {
            $_INFO["pconnect"] = self::$StreamingServers[SERVER_ID]["persistent_connections"];
            if (!empty($_INFO) && is_array($_INFO) && !empty($_INFO["db_user"])) {
                file_put_contents(IPTV_PANEL_DIR . "config", base64_encode(decrypt_config(json_encode($_INFO), CONFIG_CRYPT_KEY)), LOCK_EX);
            }
        }

        self::$SegmentsSettings = self::calculateSegNumbers();
        crontab_refresh();
    }

    public static function getDiffTimezone($rTimezone) {
        $rServerTZ = new DateTime('UTC', new DateTimeZone(date_default_timezone_get()));
        $rUserTZ = new DateTime('UTC', new DateTimeZone($rTimezone));
        return $rUserTZ->getTimestamp() - $rServerTZ->getTimestamp();
    }

    public static function calculateSegNumbers() {
        $segments_settings = array();
        $segments_settings["seg_time"] = 10;
        $segments_settings["seg_list_size"] = 6;
        return $segments_settings;
    }

    public static function GetIspAddon() {
        $cache = self::getCache('customisp', 60);
        if (!empty($cache)) {
            return $cache;
        }
        $output = array();
        self::$ipTV_db->query("SELECT id,isp,blocked FROM `isp_addon`");
        $output = self::$ipTV_db->get_rows();
        return $output;
    }

    public static function GetBlockedUserAgents() {
        $cache = self::getCache('uagents', 60);
        if (!empty($cache)) {
            return $cache;
        }
        $output = array();
        self::$ipTV_db->query("SELECT id,exact_match,LOWER(user_agent) as blocked_ua FROM `blocked_user_agents`");
        $output = self::$ipTV_db->get_rows(true, "id");
        return $output;
    }
    /** 
     * Retrieves the list of bouquets with their associated streams, series, channels, movies, and radios. 
     * 
     * @return array An array containing the bouquets with their respective streams, series, channels, movies, and radios. 
     */
    public static function getBouquets() {
        $rCache = self::getCache('bouquets', 60);
        if (!empty($rCache)) {
            return $rCache;
        }
        $output = array();
        self::$ipTV_db->query('SELECT *, IF(`bouquet_order` > 0, `bouquet_order`, 999) AS `order` FROM `bouquets` ORDER BY `order` ASC;');
        foreach (self::$ipTV_db->get_rows(true, 'id') as $rID => $rChannels) {
            $output[$rID]['streams'] = array_merge(json_decode($rChannels['bouquet_channels'], true), json_decode($rChannels['bouquet_movies'], true), json_decode($rChannels['bouquet_radios'], true));
            $output[$rID]['series'] = json_decode($rChannels['bouquet_series'], true);
            $output[$rID]['channels'] = json_decode($rChannels['bouquet_channels'], true);
            $output[$rID]['movies'] = json_decode($rChannels['bouquet_movies'], true);
            $output[$rID]['radios'] = json_decode($rChannels['bouquet_radios'], true);
        }
        self::setCache('bouquets', $output);
        return $output;
    }
    public static function getCategories($rType = null) {
        if (is_string($rType)) {
            self::$ipTV_db->query('SELECT t1.* FROM `stream_categories` t1 WHERE t1.category_type = ? GROUP BY t1.id ORDER BY t1.cat_order ASC', $rType);
            return (0 < self::$ipTV_db->num_rows() ? self::$ipTV_db->get_rows(true, 'id') : array());
        }
        $rCache = self::getCache('categories', 20);
        if (!empty($rCache)) {
            return $rCache;
        }

        self::$ipTV_db->query('SELECT t1.* FROM `stream_categories` t1 ORDER BY t1.cat_order ASC');
        $rCategories = (0 < self::$ipTV_db->num_rows() ? self::$ipTV_db->get_rows(true, 'id') : array());
        self::setCache('categories', $rCategories);
        return $rCategories;
    }
    public static function getBlockedIPs() {
        $cache = self::getCache('blocked_ips', 20);
        if (!empty($cache)) {
            return $cache;
        }

        $output = array();
        self::$ipTV_db->query('SELECT `ip` FROM `blocked_ips`');
        foreach (self::$ipTV_db->get_rows() as $row) {
            $output[] = $row['ip'];
        }
        self::setCache('blocked_ips', $output);
        return $output;
    }
    /** 
     * Retrieves the list of blocked ISPs from the cache or database. 
     * 
     * @return array The list of blocked ISPs with their IDs, ISP names, and blocked status. 
     */
    public static function getBlockedISP() {
        $cache = self::getCache('blocked_isp', 20);
        if (!empty($cache)) {
            return $cache;
        }

        self::$ipTV_db->query('SELECT id,isp,blocked FROM `blocked_isps`');
        $output = self::$ipTV_db->get_rows();
        self::setCache('blocked_isp', $output);
        return $output;
    }
    public static function getSettings() {
        $cache = self::getCache('settings', 20);
        if (!empty($cache)) {
            return $cache;
        }
        $output = array();
        self::$ipTV_db->query("SELECT * FROM `settings`");
        $rows = self::$ipTV_db->get_row();
        foreach ($rows as $key => $val) {
            $output[$key] = $val;
        }
        $output["allow_countries"] = json_decode($output["allow_countries"], true);
        $output["allowed_stb_types"] = @array_map("strtolower", json_decode($output["allowed_stb_types"], true));
        $output["stalker_lock_images"] = json_decode($output["stalker_lock_images"], true);
        $output["use_https"] = json_decode($output["use_https"], true);
        $output["stalker_container_priority"] = json_decode($output["stalker_container_priority"], true);
        $output["gen_container_priority"] = json_decode($output["gen_container_priority"], true);
        if (array_key_exists("bouquet_name", $output)) {
            $output["bouquet_name"] = str_replace(" ", "_", $output["bouquet_name"]);
        }
        $output["api_ips"] = explode(",", $output["api_ips"]);
        return $output;
    }
    public static function seriesData() {
        $output = array();
        if (file_exists(TMP_DIR . "series_data.php")) {
            include TMP_DIR . "series_data.php";
        }
        return $output;
    }
    public static function sortChannels($rChannels) {
        if (0 < count($rChannels) && file_exists(CACHE_TMP_PATH . 'channel_order') && self::$settings['channel_number_type'] != 'bouquet') {
            $order = unserialize(file_get_contents(CACHE_TMP_PATH . 'channel_order'));
            $rChannels = array_flip($rChannels);
            $rNewOrder = array();
            foreach ($order as $rID) {
                if (isset($rChannels[$rID])) {
                    $rNewOrder[] = $rID;
                }
            }
            if (count($rNewOrder) > 0) {
                return $rNewOrder;
            }
        }
        return $rChannels;
    }
    public static function movieProperties($stream_id) {
        $movie_properties = array();
        if (file_exists(TMP_DIR . $stream_id . "_cache_properties")) {
            $movie_properties = unserialize(file_get_contents(TMP_DIR . $stream_id . "_cache_properties"));
        }
        return isset($movie_properties) && is_array($movie_properties) ? $movie_properties : array();
    }
    /** 
     * Sets the cache data for a given cache key. 
     * 
     * @param string $cache The cache key. 
     * @param mixed $data The data to be cached. 
     * @return void 
     */
    public static function setCache($cache, $data) {
        $serializedData = serialize($data);
        if (!file_exists(CACHE_TMP_PATH)) {
            mkdir(CACHE_TMP_PATH);
        }
        file_put_contents(CACHE_TMP_PATH . $cache, $serializedData, LOCK_EX);
    }
    /** 
     * Retrieves the cached data for a given cache key if it exists and is not expired. 
     * 
     * @param string $cache The cache key. 
     * @param int|null $rSeconds The expiration time in seconds. 
     * @return mixed|null The cached data if it exists and is not expired, null otherwise. 
     */
    public static function getCache($cache, $rSeconds = null) {
        if (file_exists(CACHE_TMP_PATH . $cache)) {
            if (!$rSeconds && time() - filemtime(CACHE_TMP_PATH . $cache) > $rSeconds) {
                $data = file_get_contents(CACHE_TMP_PATH . $cache);
                return unserialize($data);
            }
        }
        return null;
    }
    public static function getServers() {
        $cache = self::getCache('servers', 20);
        if (!empty($cache)) {
            return $cache;
        }
        if (empty($_SERVER["REQUEST_SCHEME"])) {
            $_SERVER["REQUEST_SCHEME"] = "http";
        }
        self::$ipTV_db->query("SELECT * FROM `streaming_servers`");
        $servers = array();
        $server_status = array(1, 3);
        foreach (self::$ipTV_db->get_rows() as $row) {
            if ((!empty($row["vpn_ip"]) && inet_pton($row["vpn_ip"]) !== false)) {
                $url = $row["vpn_ip"];
            } else if (!empty($row["domain_name"])) {
                $url = str_replace(array("http://", "/", "https://"), '', $row["domain_name"]);
            } else {
                $url = $row["server_ip"];
            }
            $server_protocol = is_array(self::$settings["use_https"]) && in_array($row["id"], self::$settings["use_https"]) ? "https" : "http";
            $http_port = ($server_protocol == 'http' ? intval($row['http_broadcast_port']) : intval($row['https_broadcast_port']));
            $row["server_protocol"] = $server_protocol;
            $row["request_port"] = $http_port;
            $row["api_url"] = $server_protocol . "://" . $url . ":" . $http_port . "/system_api.php?password=" . ipTV_lib::$settings["live_streaming_pass"];
            $row["site_url"] = $server_protocol . "://" . $url . ":" . $http_port . "/";
            $row["rtmp_server"] = "rtmp://" . $url . ":" . $row["rtmp_port"] . "/live/";
            $row["rtmp_mport_url"] = "http://127.0.0.1:31210/";
            $row["api_url_ip"] = $server_protocol . "://" . $row["server_ip"] . ":" . $http_port . "/system_api.php?password=" . ipTV_lib::$settings["live_streaming_pass"];
            $row["site_url_ip"] = $server_protocol . "://" . $row["server_ip"] . ":" . $http_port . "/";
            $row["geoip_countries"] = empty($row["geoip_countries"]) ? array() : json_decode($row["geoip_countries"], true);
            $row["isp_names"] = empty($row["isp_names"]) ? array() : json_decode($row["isp_names"], true);
            $row["server_online"] = in_array($row["status"], $server_status) && time() - $row["last_check_ago"] <= 90 || SERVER_ID == $row["id"] ? true : false;
            $row['domains'] = array('protocol' => $server_protocol, 'port' => $http_port, 'urls' => array_filter(array_map('escapeshellcmd', explode(',', $row['domain_name']))));
            unset($row["ssh_password"], $row["watchdog_data"], $row["last_check_ago"]);
            $servers[intval($row["id"])] = $row;
        }
        return $servers;
    }

    public static function mc_decrypt($data, $key) {
        $data = explode("|", $data . "|");
        $decoded = base64_decode($data[0]);
        $iv = base64_decode($data[1]);
        if (strlen($iv) === mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
            $key = pack('H*', $key);
            $rDecrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
            $rMAC = substr($rDecrypted, -64);
            $rDecrypted = substr($rDecrypted, 0, -64);
            $rCalcHMAC = hash_hmac('sha256', $rDecrypted, substr(bin2hex($key), -32));
            if ($rCalcHMAC === $rMAC) {
                $rDecrypted = unserialize($rDecrypted);
                return $rDecrypted;
            }
            return false;
        }
        return false;
    }
    /**
     * Makes multiple cURL requests to the specified URLs and returns the results.
     *
     * @param array $urls An array of URLs to make cURL requests to.
     * @param callable|null $callback Optional callback function to process the cURL response.
     * @param int $timeout The timeout value for each cURL request (default is 5 seconds).
     * @return array An array of results from the cURL requests.
     */
    public static function curlMultiRequest(array $urls, $callback = null, int $timeout = 5) {
        if (empty($urls)) {
            return array();
        }
        $cmrKeys = array();
        $ch = array();
        $results = array();
        $mh = curl_multi_init();
        foreach ($urls as $key => $val) {
            if (ipTV_lib::$StreamingServers[$key]["server_online"]) {
                $ch[$key] = curl_init();
                curl_setopt($ch[$key], CURLOPT_URL, $val["url"]);
                curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch[$key], CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch[$key], CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch[$key], CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch[$key], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch[$key], CURLOPT_SSL_VERIFYPEER, 0);
                if ($val["postdata"] != null) {
                    curl_setopt($ch[$key], CURLOPT_POST, true);
                    curl_setopt($ch[$key], CURLOPT_POSTFIELDS, http_build_query($val["postdata"]));
                }
                curl_multi_add_handle($mh, $ch[$key]);
            } else {
                $cmrKeys[] = $key;
            }
        }

        $running = null;

        do {
            $mrc = curl_multi_exec($mh, $running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($running && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) == -1) {
                usleep(50000);
            }
            do {
                $mrc = curl_multi_exec($mh, $running);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        foreach ($ch as $key => $val) {
            $results[$key] = curl_multi_getcontent($val);
            if ($callback != null) {
                $results[$key] = call_user_func($callback, $results[$key], true);
            }
            curl_multi_remove_handle($mh, $val);
        }
        foreach ($cmrKeys as $key) {
            $results[$key] = false;
        }
        curl_multi_close($mh);
        return $results;
    }

    public static function cleanGlobals(&$data, $iteration = 0) {
        if ($iteration >= 10) {
            return;
        }
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                self::cleanGlobals($data[$k], ++$iteration);
            } else {
                $v = str_replace(chr('0'), '', $v);
                $v = str_replace('', '', $v);
                $v = str_replace('', '', $v);
                $v = str_replace('../', '&#46;&#46;/', $v);
                $v = str_replace('&#8238;', '', $v);
                $data[$k] = $v;
            }
        }
    }

    public static function parseIncomingRecursively(&$data, $input = array(), $iteration = 0) {
        if ($iteration >= 20) {
            return $input;
        }
        if (!is_array($data)) {
            return $input;
        }
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $input[$k] = self::parseIncomingRecursively($data[$k], array(), $iteration + 1);
            } else {
                $k = self::parseCleanKey($k);
                $v = self::parseCleanValue($v);
                $input[$k] = $v;
            }
        }
        return $input;
    }

    public static function parseCleanKey($key) {
        if ($key === '') {
            return '';
        }
        $key = htmlspecialchars(urldecode($key));
        $key = str_replace('..', '', $key);
        $key = preg_replace('/\\_\\_(.+?)\\_\\_/', '', $key);
        $key = preg_replace('/^([\\w\\.\\-\\_]+)$/', '$1', $key);
        return $key;
    }

    public static function parseCleanValue($val) {
        if ($val == "") {
            return "";
        }
        $val = str_replace("&#032;", " ", stripslashes($val));
        $val = str_replace(array("\r\n", "\n\r", "\r"), "\n", $val);
        $val = str_replace("<!--", "&#60;&#33;--", $val);
        $val = str_replace("-->", "--&#62;", $val);
        $val = str_ireplace("<script", "&#60;script", $val);
        $val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val);
        $val = preg_replace("/&#(\\d+?)([^\\d;])/i", "&#\\1;\\2", $val);
        return trim($val);
    }

    public static function SaveLog($msg) {
        self::$ipTV_db->query('INSERT INTO `panel_logs` (`log_message`,`date`) VALUES(\'%s\',\'%d\')', $msg, time());
    }

    public static function GenerateString($length = 10) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789qwertyuiopasdfghjklzxcvbnm";
        $str = '';
        $max = strlen($chars) - 1;
        $index = 0;
        while ($index < $length) {
            $str .= $chars[rand(0, $max)];
            $index++;
        }
        return $str;
    }

    public static function array_values_recursive($array) {
        if (!is_array($array)) {
            return $array;
        }
        $arrayValues = array();
        foreach ($array as $value) {
            if ((is_scalar($value) or is_resource($value))) {
                $arrayValues[] = $value;
            } else if (is_array($value)) {
                $arrayValues = array_merge($arrayValues, self::array_values_recursive($value));
            }
        }
        return $arrayValues;
    }
    public static function check_cron($rFilename, $rTime = 1800) {
        if (file_exists($rFilename)) {
            $PID = trim(file_get_contents($rFilename));
            if (file_exists('/proc/' . $PID)) {
                if (time() - filemtime($rFilename) >= $rTime) {
                    if (is_numeric($PID) && 0 < $PID) {
                        posix_kill($PID, 9);
                    }
                } else {
                    exit('Running...');
                }
            }
        }
        file_put_contents($rFilename, getmypid());
        return false;
    }
    public static function confirmIDs($rIDs) {
        $rReturn = array();

        foreach ($rIDs as $rID) {
            if (intval($rID) > 0) {
                $rReturn[] = $rID;
            }
        }

        return $rReturn;
    }

    /**
     * Deletes a file from the filesystem if it exists.
     *
     * This function checks if the specified file exists at the given file path. 
     * If the file exists, it attempts to delete it using the `unlink` function.
     *
     * @param string $filePath The path to the file that needs to be deleted.
     * @return void This function does not return a value. It performs the deletion 
     *              operation and will not raise an error if the file does not exist.
     */
    public static function unlink_file($filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
