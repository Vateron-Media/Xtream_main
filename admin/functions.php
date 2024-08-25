<?php
include_once("/home/xtreamcodes/admin/HTMLPurifier.standalone.php");

$rTimeout = 60;             // Seconds Timeout for Functions & Requests
$rSQLTimeout = 5;           // Max execution time for MySQL queries.
$rDebug = False;
$rPurifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());

function getScriptVer() {
    global $db;
    $rVersion = $db->query("SELECT `script_version` FROM `streaming_servers` WHERE `is_main` = '1'")->fetch_assoc()["script_version"];
    return $rVersion;
}

/**
 * Determines if an update is needed based on the current version and the required version.
 *
 * This function compares two version strings, `currentVersion` and `requiredVersion`, 
 * which are expected to be in a dot-separated format (e.g., "1.0.0"). It converts these 
 * version strings into arrays of integers, then compares each part of the version numbers 
 * to determine if the current version is less than the required version. 
 * 
 * If any part of the current version is less than the corresponding part of the required 
 * version, the function returns true, indicating that an update is needed. If any part 
 * of the current version is greater, it returns false, indicating that no update is needed. 
 * If both versions are equal, it also returns false.
 *
 * @param string $currentVersion The current version string.
 * @param string $requiredVersion The required version string to compare against.
 * @return bool Returns true if an update is needed, false otherwise.
 */
function isUpdateNeeded($currentVersion, $requiredVersion) {
    // Convert version strings to arrays of integers
    $currentVersionArray = array_map('intval', explode('.', $currentVersion));
    $requiredVersionArray = array_map('intval', explode('.', $requiredVersion));

    // Compare each part of the version numbers
    $length = max(count($currentVersionArray), count($requiredVersionArray));
    for ($i = 0; $i < $length; $i++) {
        $currentPart = $currentVersionArray[$i] ?? 0;
        $requiredPart = $requiredVersionArray[$i] ?? 0;

        if ($currentPart < $requiredPart) {
            return true;
        } elseif ($currentPart > $requiredPart) {
            return false;
        }
    }

    // Versions are equal
    return false;
}

function XSS($rString, $rSQL = False) {
    global $rPurifier, $db;
    if ((is_null($rString)) or (strtoupper($rString) == 'NULL')) {
        return null;
    } else if (is_array($rString)) {
        return XSSRow($rString, $rSQL);
    } else if ($rSQL) {
        return $db->real_escape_string(str_replace("&quot;", '"', str_replace("&amp;", "&", $rPurifier->purify($rString))));
    } else {
        return str_replace("&quot;", '"', str_replace("&amp;", "&", $rPurifier->purify($rString)));
    }
}

function XSSRow($rRow, $rSQL = False) {
    foreach ($rRow as $rKey => $rValue) {
        if (is_array($rValue)) {
            $rRow[$rKey] = XSSRow($rValue, $rSQL);
        } else {
            $rRow[$rKey] = XSS($rValue, $rSQL);
        }
    }
    return $rRow;
}

function ESC($rString) {
    return XSS($rString, True);
}

function sortArrayByArray(array $rArray, array $rSort) {
    $rOrdered = array();
    foreach ($rSort as $rValue) {
        if (($rKey = array_search($rValue, $rArray)) !== false) {
            $rOrdered[] = $rValue;
            unset($rArray[$rKey]);
        }
    }
    return $rOrdered + $rArray;
}



function updateGeoLite2() {
    global $rAdminSettings;
    $rURL = "https://raw.githubusercontent.com/Vateron-Media/Xtream_Update/main/status.json";
    $rData = json_decode(file_get_contents($rURL), True);
    if ($rData["version"]) {
        $fileNames = ["GeoLite2-City.mmdb", "GeoLite2-Country.mmdb", "GeoLite2-ASN.mmdb"];
        $checker = [false, false, false];
        foreach ($fileNames as $key => $value) {
            $rFileData = file_get_contents("https://github.com/Vateron-Media/Xtream_Update/raw/main/{$value}");
            if (stripos($rFileData, "MaxMind.com") !== false) {
                $rFilePath = "/home/xtreamcodes/bin/maxmind/{$value}";
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
            $rAdminSettings["geolite2_version"] = $rData["version"];
            writeAdminSettings();
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function mapmap() {
    global $db;
    $rQuery = "SELECT geoip_country_code, count(geoip_country_code) AS total FROM lines_live GROUP BY geoip_country_code";
    if ($rResult = $db->query($rQuery)) {
        while ($row = $rResult->fetch_assoc()) {
            $gggrr = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
            echo $gggrr;
        }
    }
}
function resetSTB($rID) {
    global $db;
    $db->query("UPDATE `mag_devices` SET `ip` = NULL, `ver` = NULL, `image_version` = NULL, `stb_type` = NULL, `sn` = NULL, `device_id` = NULL, `device_id2` = NULL, `hw_version` = NULL, `token` = NULL WHERE `mag_id` = " . intval($rID) . ";");
}
//isp lock
function resetispnames($rID) {
    global $db;
    $db->query("UPDATE `users` SET `isp_desc` = NULL WHERE `id` = " . intval($rID) . ";");
}
//isp lock
function getAdminSettings() {
    global $db;
    $return = array();
    $result = $db->query("SELECT `type`, `value` FROM `admin_settings`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[$row["type"]] = $row["value"];
        }
    }
    return $return;
}

function getSettings() {
    global $db;
    $result = $db->query("SELECT * FROM `settings` LIMIT 1;");
    return $result->fetch_assoc();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($rDebug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

set_time_limit($rTimeout);
ini_set('mysql.connect_timeout', $rSQLTimeout);
ini_set('max_execution_time', $rTimeout);
ini_set('default_socket_timeout', $rTimeout);

define("MAIN_DIR", "/home/xtreamcodes/");
define("CONFIG_CRYPT_KEY", "5709650b0d7806074842c6de575025b1");

require_once realpath(dirname(__FILE__)) . "/mobiledetect.php";
require_once realpath(dirname(__FILE__)) . "/gauth.php";

function getTimezone() {
    global $db;
    $result = $db->query("SELECT `default_timezone` FROM `settings`;");
    if ((isset($result)) && ($result->num_rows == 1)) {
        return XSS($result->fetch_assoc()["default_timezone"]);
    } else {
        return "Europe/London";
    }
}

function xor_parse($data, $key) {
    $i = 0;
    $output = '';
    foreach (str_split($data) as $char) {
        $output .= chr(ord($char) ^ ord($key[$i++ % strlen($key)]));
    }
    return $output;
}

$_INFO = json_decode(xor_parse(base64_decode(file_get_contents(MAIN_DIR . "config")), CONFIG_CRYPT_KEY), True);
if (!$db = new mysqli($_INFO["host"], $_INFO["db_user"], $_INFO["db_pass"], $_INFO["db_name"], $_INFO["db_port"])) {
    exit("No MySQL connection!");
}
$db->set_charset("utf8");
$db->query("SET GLOBAL MAX_EXECUTION_TIME=" . ($rSQLTimeout * 1000) . ";");
date_default_timezone_set(getTimezone());

$rAdminSettings = getAdminSettings();
$rSettings = getSettings();
$nabilos = getRegisteredUserHash($_SESSION['hash']);

if ((strlen($nabilos["default_lang"]) > 0) && (file_exists("./lang/" . $nabilos["default_lang"] . ".php"))) {
    include "./lang/" . $nabilos["default_lang"] . ".php";
} else {
    include "/home/xtreamcodes/admin/lang/en.php";
}

$detect = new Mobile_Detect;
$rClientFilters = array(
    "NOT_IN_BOUQUET" => "Not in Bouquet",
    "CON_SVP" => "Connection Issue",
    "ISP_LOCK_FAILED" => "ISP Lock Failed",
    "USER_DISALLOW_EXT" => "Extension Disallowed",
    "AUTH_FAILED" => "Authentication Failed",
    "USER_EXPIRED" => "User Expired",
    "USER_DISABLED" => "User Disabled",
    "USER_BAN" => "User Banned"
);

function APIRequest($rData) {
    global $rAdminSettings, $rServers, $_INFO;
    ini_set('default_socket_timeout', 5);
    if ($rAdminSettings["local_api"]) {
        $rAPI = "http://127.0.0.1:" . $rServers[$_INFO["server_id"]]["http_broadcast_port"] . "/api.php";
    } else {
        $rAPI = "http://" . $rServers[$_INFO["server_id"]]["server_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"] . "/api.php";
    }
    $rPost = http_build_query($rData);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rAPI);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rPost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $rData = curl_exec($ch);
    return $rData;
}

function SystemAPIRequest($rServerID, $rData) {
    global $rServers, $rSettings;
    ini_set('default_socket_timeout', 5);
    $rAPI = "http://" . $rServers[intval($rServerID)]["server_ip"] . ":" . $rServers[intval($rServerID)]["http_broadcast_port"] . "/system_api.php";
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
//network interface 1
function multiexplode($delimiters, $data) {
    $MakeReady = str_replace($delimiters, $delimiters[0], $data);
    $Return    = array_filter(explode($delimiters[0], $MakeReady));
    return  $Return;
}
//network interface 1		 
function sexec($rServerID, $rCommand) {
    global $_INFO;
    if ($rServerID <> $_INFO["server_id"]) {
        return SystemAPIRequest($rServerID, array("action" => "BackgroundCLI", "cmds" => array($rCommand)));
    } else {
        return exec($rCommand);
    }
}
//network interface 2
function sexec2($rServerID, $rCommand) {
    $loool = SystemAPIRequest($rServerID, array("action" => "BackgroundCLI", "cmds" => array($rCommand)));
    return  $loool;
}
function loadnginx($rServerID) {
    sexec($rServerID, "sudo /home/xtreamcodes/bin/nginx/sbin/nginx -s reload");
    sexec($rServerID, "sudo /home/xtreamcodes/bin/nginx_rtmp/sbin/nginx_rtmp -s reload");
}
function netnet($rServerID) {
    $ccc = sexec2($rServerID, "ls -1 /sys/class/net");
    $ttt = multiexplode(array('[', '"', '\n', ']'), $ccc);
    array_push($ttt, "");
    return $ttt;
}
//network interface 2	

function changePort($rServerID, $rType, $rOldPort, $rNewPort) {
    if ($rType == 0) {
        // HTTP
        sexec($rServerID, "sed -i 's/listen " . intval($rOldPort) . ";/listen " . intval($rNewPort) . ";/g' /home/xtreamcodes/bin/nginx/conf/nginx.conf");
        sexec($rServerID, "sed -i 's/:" . intval($rOldPort) . "/:" . intval($rNewPort) . "/g' /home/xtreamcodes/bin/nginx_rtmp/conf/nginx.conf");
    } else if ($rType == 1) {
        // SSL
        sexec($rServerID, "sed -i 's/listen " . intval($rOldPort) . " ssl;/listen " . intval($rNewPort) . " ssl;/g' /home/xtreamcodes/bin/nginx/conf/nginx.conf");
    } else if ($rType == 2) {
        // RTMP
        sexec($rServerID, "sed -i 's/listen " . intval($rOldPort) . ";/listen " . intval($rNewPort) . ";/g' /home/xtreamcodes/bin/nginx_rtmp/conf/nginx.conf");
    } else if ($rType == 3) {
        // ISP
        sexec($rServerID, "sed -i 's/listen " . intval($rOldPort) . ";/listen " . intval($rNewPort) . ";/g' /home/xtreamcodes/bin/nginx/conf/nginx.conf");
        sexec($rServerID, "sed -i 's|:" . intval($rOldPort) . "/api.php|:" . intval($rNewPort) . "/api.php|g' /home/xtreamcodes/wwwdir/includes/streaming.php");
    }
    loadnginx($rServerID);
}

function getPIDs($rServerID) {
    global $rAdminSettings;
    $rReturn = array();
    $rFilename = tempnam(MAIN_DIR . 'tmp/', 'proc_');
    $rCommand = "ps aux >> " . $rFilename;
    sexec($rServerID, $rCommand);
    $rData = "";
    $rI = 3;
    while (strlen($rData) == 0) {
        $rData = SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
        $rI--;
        if (($rI == 0) or (strlen($rData) > 0)) {
            break;
        }
        sleep(1);
    }
    $rProcesses = explode("\n", $rData);
    array_shift($rProcesses);
    foreach ($rProcesses as $rProcess) {
        $rSplit = explode(" ", preg_replace('!\s+!', ' ', trim($rProcess)));
        if (strlen($rSplit[0]) > 0) {
            $rReturn[] = array("user" => $rSplit[0], "pid" => $rSplit[1], "cpu" => $rSplit[2], "mem" => $rSplit[3], "vsz" => $rSplit[4], "rss" => $rSplit[5], "tty" => $rSplit[6], "stat" => $rSplit[7], "start" => $rSplit[8], "time" => $rSplit[9], "command" => join(" ", array_splice($rSplit, 10, count($rSplit) - 10)));
        }
    }
    return $rReturn;
}

function getFreeSpace($rServerID) {
    $rReturn = array();
    $rFilename = tempnam(MAIN_DIR . 'tmp/', 'fs_');
    $rCommand = "df -h >> " . $rFilename;
    sexec($rServerID, $rCommand);
    $rData = SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
    $rLines = explode("\n", $rData);
    array_shift($rLines);
    foreach ($rLines as $rLine) {
        $rSplit = explode(" ", preg_replace('!\s+!', ' ', trim($rLine)));
        if ((strlen($rSplit[0]) > 0) && (strpos($rSplit[5], "xtreamcodes") !== false)) {
            $rReturn[] = array("filesystem" => $rSplit[0], "size" => $rSplit[1], "used" => $rSplit[2], "avail" => $rSplit[3], "percentage" => $rSplit[4], "mount" => join(" ", array_slice($rSplit, 5, count($rSplit) - 5)));
        }
    }
    return $rReturn;
}

function remoteCMD($rServerID, $rCommand) {
    $rReturn = array();
    $rFilename = tempnam(MAIN_DIR . 'tmp/', 'cmd_');
    sexec($rServerID, $rCommand . " >> " . $rFilename);
    $rData = "";
    $rI = 3;
    while (strlen($rData) == 0) {
        $rData = SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
        $rI--;
        if (($rI == 0) or (strlen($rData) > 0)) {
            break;
        }
        sleep(1);
    }
    unset($rFilename);
    return $rData;
}

function freeTemp($rServerID) {
    sexec($rServerID, "rm " . MAIN_DIR . "tmp/*");
}

function freeStreams($rServerID) {
    sexec($rServerID, "rm " . MAIN_DIR . "streams/*");
}

function getStreamPIDs($rServerID) {
    global $db;
    $return = array();
    $result = $db->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `streams`.`type`, `streams_servers`.`pid`, `streams_servers`.`monitor_pid`, `streams_servers`.`delay_pid` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE `streams_servers`.`server_id` = " . intval($rServerID) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            foreach (array("pid", "monitor_pid", "delay_pid") as $rPIDType) {
                if ($row[$rPIDType]) {
                    $return[$row[$rPIDType]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => $rPIDType);
                }
            }
        }
    }
    $result = $db->query("SELECT `id`, `stream_display_name`, `type`, `tv_archive_pid` FROM `streams` WHERE `tv_archive_server_id` = " . intval($rServerID) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if ($row["pid"]) {
                $return[$row["pid"]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => "timeshift");
            }
        }
    }
    $result = $db->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `streams`.`type`, `lines_live`.`pid` FROM `lines_live` LEFT JOIN `streams` ON `streams`.`id` = `lines_live`.`stream_id` WHERE `lines_live`.`server_id` = " . intval($rServerID) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if ($row["pid"]) {
                $return[$row["pid"]] = array("id" => $row["id"], "title" => $row["stream_display_name"], "type" => $row["type"], "pid_type" => "activity");
            }
        }
    }
    return $return;
}

function roundUpToAny($n, $x = 5) {
    return round(($n + $x / 2) / $x) * $x;
}

function checkSource($rServerID, $rFilename) {
    global $rServers, $rSettings;
    $rAPI = "http://" . $rServers[intval($rServerID)]["server_ip"] . ":" . $rServers[intval($rServerID)]["http_broadcast_port"] . "/system_api.php?password=" . $rSettings["live_streaming_pass"] . "&action=getFile&filename=" . urlencode(escapeshellcmd($rFilename));
    $rCommand = 'timeout 5 ' . MAIN_DIR . 'bin/ffprobe -show_streams -v quiet "' . $rAPI . '" -of json';
    return json_decode(shell_exec($rCommand), True);
}

function getSelections($rSources) {
    global $db;
    $return = array();
    foreach ($rSources as $rSource) {
        $result = $db->query("SELECT `id` FROM `streams` WHERE `type` IN (2,5) AND `stream_source` LIKE '%" . ESC(str_replace("/", "\/", $rSource)) . "\"%' ESCAPE '|' LIMIT 1;");
        if (($result) && ($result->num_rows == 1)) {
            $return[] = intval($result->fetch_assoc()["id"]);
        }
    }
    return $return;
}

function getBackups() {
    $rBackups = array();

    # create directory backups
    if (!is_dir(MAIN_DIR . "adtools/backups/")) {
        mkdir(MAIN_DIR . "adtools/backups/");
    }

    foreach (scandir(MAIN_DIR . "adtools/backups/") as $rBackup) {
        $rInfo = pathinfo(MAIN_DIR . "adtools/backups/" . $rBackup);
        if ($rInfo["extension"] == "sql") {
            $rBackups[] = array("filename" => $rBackup, "timestamp" => filemtime(MAIN_DIR . "adtools/backups/" . $rBackup), "date" => date("Y-m-d H:i:s", filemtime(MAIN_DIR . "adtools/backups/" . $rBackup)), "filesize" => filesize(MAIN_DIR . "adtools/backups/" . $rBackup));
        }
    }
    usort($rBackups, function ($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });
    return $rBackups;
}

function startcmd() {
    echo shell_exec("nohup /usr/bin/python /home/xtreamcodes/pytools/balancer.py 2>&1");
}

function tmdbParseRelease($Release) {
    $rCommand = "/usr/bin/python " . MAIN_DIR . "pytools2/release.py \"" . escapeshellcmd($Release) . "\"";
    return json_decode(shell_exec($rCommand), True);
}

function listDir($rServerID, $rDirectory, $rAllowed = null) {
    global $rServers, $_INFO, $rSettings, $rAdminSettings;
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
        if ($rAdminSettings["alternate_scandir"]) {
            $rFilename = tempnam(MAIN_DIR . 'tmp/', 'ls_');
            $rCommand = "ls -cm -f --group-directories-first --indicator-style=slash \"" . escapeshellcmd($rDirectory) . "\" >> " . $rFilename;
            sexec($rServerID, $rCommand);
            $rData = "";
            $rI = 2;
            while (strlen($rData) == 0) {
                $rData = SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
                $rI--;
                if (($rI == 0) or (strlen($rData) > 0)) {
                    break;
                }
                sleep(1);
            }
            if (strlen($rData) > 0) {
                $rFiles = explode(",", $rData);
                sort($rFiles);
                foreach ($rFiles as $rFile) {
                    $rFile = trim($rFile);
                    if (substr($rFile, -1) == "/") {
                        if ((substr($rFile, 0, -1) <> "..") && (substr($rFile, 0, -1) <> ".")) {
                            $rReturn["dirs"][] = substr($rFile, 0, -1);
                        }
                    } else {
                        $rExt = strtolower(pathinfo($rFile)["extension"]);
                        if (((is_array($rAllowed)) && (in_array($rExt, $rAllowed))) or (!$rAllowed)) {
                            $rReturn["files"][] = $rFile;
                        }
                    }
                }
            }
        } else {
            $rData = SystemAPIRequest($rServerID, array('action' => 'viewDir', 'dir' => $rDirectory));
            $rDocument = new DOMDocument();
            $rDocument->loadHTML($rData);
            $rFiles = $rDocument->getElementsByTagName('li');
            foreach ($rFiles as $rFile) {
                if (stripos($rFile->getAttribute('class'), "directory") !== false) {
                    $rReturn["dirs"][] = $rFile->nodeValue;
                } else if (stripos($rFile->getAttribute('class'), "file") !== false) {
                    $rExt = strtolower(pathinfo($rFile->nodeValue)["extension"]);
                    if (((is_array($rAllowed)) && (in_array($rExt, $rAllowed))) or (!$rAllowed)) {
                        $rReturn["files"][] = $rFile->nodeValue;
                    }
                }
            }
        }
    }
    return $rReturn;
}

function scanRecursive($rServerID, $rDirectory, $rAllowed = null) {
    $result = [];
    $rFiles = listDir($rServerID, $rDirectory, $rAllowed);
    foreach ($rFiles["files"] as $rFile) {
        $rFilePath = rtrim($rDirectory, "/") . '/' . $rFile;
        $result[] = $rFilePath;
    }
    foreach ($rFiles["dirs"] as $rDir) {
        foreach (scanRecursive($rServerID, rtrim($rDirectory, "/") . "/" . $rDir . "/", $rAllowed) as $rFile) {
            $result[] = $rFile;
        }
    }
    return $result;
}

function getEncodeErrors($rID) {
    global $rSettings;
    $rServers = getStreamingServers(true);
    ini_set('default_socket_timeout', 3);
    $rErrors = array();
    $rStreamSys = getStreamSys($rID);
    foreach ($rStreamSys as $rServer) {
        $rServerID = $rServer["server_id"];
        if (isset($rServers[$rServerID])) {
            if (!($rServer["pid"] > 0 && $rServer["to_analyze"] == 0 && $rServer["stream_status"] <> 1)) {
                $rFilename = MAIN_DIR . "movies/" . intval($rID) . ".errors";
                $rError = SystemAPIRequest($rServerID, array('action' => 'getFile', 'filename' => $rFilename));
                if (strlen($rError) > 0) {
                    $rErrors[$rServerID] = $rError;
                }
            }
        }
    }
    return $rErrors;
}

function getTimeDifference($rServerID) {
    global $rServers, $rSettings;
    ini_set('default_socket_timeout', 3);
    $rError = SystemAPIRequest($rServerID, array('action' => 'getDiff', 'main_time' => intval(time())));
    return (is_file($rAPI)) ? intval(file_get_contents($rAPI)) : '';
}

function deleteMovieFile($rServerID, $rID) {
    global $rServers, $rSettings;
    ini_set('default_socket_timeout', 3);
    $rCommand = "rm " . MAIN_DIR . "movies/" . $rID . ".*";
    return SystemAPIRequest($rServerID, array('action' => 'BackgroundCLI', 'action' => array($rCommand)));
}

function generateString($strength = 10) {
    $input = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $input_length = strlen($input);
    $random_string = '';
    for ($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }
    return $random_string;
}

function getStreamingServers($rActive = false) {
    global $db, $rPermissions;
    $return = array();
    if ($rActive) {
        $result = $db->query("SELECT * FROM `streaming_servers` WHERE `status` = 1 ORDER BY `id` ASC;");
    } else {
        $result = $db->query("SELECT * FROM `streaming_servers` ORDER BY `id` ASC;");
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($rPermissions["is_reseller"]) {
                $row["server_name"] = "Server #" . $row["id"];
            }
            $return[$row["id"]] = $row;
        }
    }
    return $return;
}

function getStreamingServersByID($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `streaming_servers` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return False;
}

function getStreamList() {
    global $db;
    $return = array();
    $result = $db->query("SELECT `streams`.`id`, `streams`.`stream_display_name`, `stream_categories`.`category_name` FROM `streams` LEFT JOIN `stream_categories` ON `stream_categories`.`id` = `streams`.`category_id` ORDER BY `streams`.`stream_display_name` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getConnections($rServerID) {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `lines_live` WHERE `server_id` = '" . ESC($rServerID) . "';");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getUserConnections($rUserID) {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `lines_live` WHERE `user_id` = '" . ESC($rUserID) . "';");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getEPGSources() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `epg`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[$row["id"]] = $row;
        }
    }
    return $return;
}

function findEPG($rEPGName) {
    global $db;
    $result = $db->query("SELECT `id`, `data` FROM `epg`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            foreach (json_decode($row["data"], True) as $rChannelID => $rChannelData) {
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

function getStreamArguments() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `streams_arguments` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[$row["argument_key"]] = $row;
        }
    }
    return $return;
}

function getTranscodeProfiles() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `transcoding_profiles` ORDER BY `profile_id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getWatchFolders($rType = null) {
    global $db;
    $return = array();
    if ($rType) {
        $result = $db->query("SELECT * FROM `watch_folders` WHERE `type` = '" . ESC($rType) . "' ORDER BY `id` ASC;");
    } else {
        $result = $db->query("SELECT * FROM `watch_folders` ORDER BY `id` ASC;");
    }
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getWatchCategories($rType = null) {
    global $db;
    $return = array();
    if ($rType) {
        $result = $db->query("SELECT * FROM `watch_categories` WHERE `type` = " . intval($rType) . " ORDER BY `genre_id` ASC;");
    } else {
        $result = $db->query("SELECT * FROM `watch_categories` ORDER BY `genre_id` ASC;");
    }
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[$row["genre_id"]] = $row;
        }
    }
    return $return;
}

function getWatchFolder($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `watch_folders` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getSeriesByTMDB($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `series` WHERE `tmdb_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getSeries() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `series` ORDER BY `title` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getSerie($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `series` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getSeriesTrailer($rTMDBID) {
    // Not implemented in TMDB PHP API...
    global $rSettings, $rAdminSettings;
    if (strlen($rAdminSettings["tmdb_language"]) > 0) {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/videos?api_key=" . $rSettings["tmdb_api_key"] . "&language=" . $rAdminSettings["tmdb_language"];
    } else {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/videos?api_key=" . $rSettings["tmdb_api_key"];
    }
    $rJSON = json_decode(file_get_contents($rURL), True);
    foreach ($rJSON["results"] as $rVideo) {
        if ((strtolower($rVideo["type"]) == "trailer") && (strtolower($rVideo["site"]) == "youtube")) {
            return $rVideo["key"];
        }
    }
    return "";
}

function getStills($rTMDBID, $rSeason, $rEpisode) {
    // Not implemented in TMDB PHP API...
    global $rSettings, $rAdminSettings;
    if (strlen($rAdminSettings["tmdb_language"]) > 0) {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/season/" . $rSeason . "/episode/" . $rEpisode . "/images?api_key=" . $rSettings["tmdb_api_key"] . "&language=" . $rAdminSettings["tmdb_language"];
    } else {
        $rURL = "https://api.themoviedb.org/3/tv/" . $rTMDBID . "/season/" . $rSeason . "/episode/" . $rEpisode . "/images?api_key=" . $rSettings["tmdb_api_key"];
    }
    return json_decode(file_get_contents($rURL), True);
}

function getUserAgents() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `blocked_user_agents` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getISPs() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `isp_addon` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getBlockedIPs() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `blocked_ips` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getPanelLogs() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `panel_logs` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getSystemLogs() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `mysql_syslog` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

//##########
function getBlockedLogins() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `login_flood` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

// LEAKED LINES : For Show Restreamers, remove AND is_restreamer <1
function getLeakedLines() {
    global $db;
    $return = array();
    $result = $db->query("SELECT FROM_BASE64(mac), username, user_activity.user_id, user_activity.container, user_activity.geoip_country_code, GROUP_CONCAT(DISTINCT user_ip), GROUP_CONCAT(DISTINCT container), GROUP_CONCAT(DISTINCT geoip_country_code), is_restreamer FROM user_activity
INNER JOIN users ON user_id = users.id AND is_mag = 1
INNER JOIN mag_devices ON users.id = mag_devices.user_id
WHERE 1 GROUP BY user_id HAVING COUNT(DISTINCT user_ip) > 1
AND
is_restreamer < 1
UNION
SELECT '', username, user_activity.user_id, user_activity.container, user_activity.geoip_country_code, GROUP_CONCAT(DISTINCT user_ip), GROUP_CONCAT(DISTINCT container), GROUP_CONCAT(DISTINCT geoip_country_code), is_restreamer FROM user_activity
INNER JOIN users ON user_id = users.id AND is_mag = 0
WHERE 1 GROUP BY user_id HAVING COUNT(DISTINCT user_ip) > 1
AND
is_restreamer < 1;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

// SECURITY CENTER
function getSecurityCenter() {
    global $db;
    $return = array();
    $result = $db->query("SELECT Distinct users.id, users.username, SUBSTR(`streams`.`stream_display_name`, 1, 30) stream_display_name, users.max_connections, (SELECT count(*) FROM `lines_live` WHERE `lines_live`.`stream_id` = `streams`.`id`) AS `active_connections`, (SELECT count(*) FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id`) AS `total_active_connections` FROM lines_live
INNER JOIN `streams` ON `lines_live`.`stream_id` = `streams`.`id`
LEFT JOIN users ON user_id = users.id WHERE (SELECT count(*) FROM `lines_live` WHERE `users`.`id` = `lines_live`.`user_id`) > `users`.`max_connections`
AND
is_restreamer < 1;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}
//############

function getRTMPIPs() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `rtmp_ips` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getStream($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `streams` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getUser($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `users` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getRegisteredUser($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `reg_users` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getRegisteredUserHash($rHash) {
    global $db;
    $result = $db->query("SELECT * FROM `reg_users` WHERE MD5(`username`) = '" . ESC($rHash) . "' LIMIT 1;");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getEPG($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `epg` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getStreamOptions($rID) {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `streams_options` WHERE `stream_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["argument_id"])] = $row;
        }
    }
    return $return;
}

function getStreamSys($rID) {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `streams_servers` WHERE `stream_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["server_id"])] = $row;
        }
    }
    return $return;
}

function getRegisteredUsers($rOwner = null, $rIncludeSelf = true) {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `reg_users` ORDER BY `username` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
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

function hasPermissions($rType, $rID) {
    global $rUserInfo, $db, $rPermissions;
    if ($rType == "user") {
        if (in_array(intval(getUser($rID)["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
            return true;
        }
    } else if ($rType == "pid") {
        $result = $db->query("SELECT `user_id` FROM `lines_live` WHERE `pid` = " . intval($rID) . ";");
        if (($result) && ($result->num_rows > 0)) {
            if (in_array(intval(getUser($result->fetch_assoc()["user_id"])["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                return true;
            }
        }
    } else if ($rType == "reg_user") {
        if ((in_array(intval($rID), array_keys(getRegisteredUsers($rUserInfo["id"])))) && (intval($rID) <> intval($rUserInfo["id"]))) {
            return true;
        }
    } else if ($rType == "ticket") {
        if (in_array(intval(getTicket($rID)["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
            return true;
        }
    } else if ($rType == "mag") {
        $result = $db->query("SELECT `user_id` FROM `mag_devices` WHERE `mag_id` = " . intval($rID) . ";");
        if (($result) && ($result->num_rows > 0)) {
            if (in_array(intval(getUser($result->fetch_assoc()["user_id"])["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                return true;
            }
        }
    } else if ($rType == "e2") {
        $result = $db->query("SELECT `user_id` FROM `enigma2_devices` WHERE `device_id` = " . intval($rID) . ";");
        if (($result) && ($result->num_rows > 0)) {
            if (in_array(intval(getUser($result->fetch_assoc()["user_id"])["member_id"]), array_keys(getRegisteredUsers($rUserInfo["id"])))) {
                return true;
            }
        }
    } else if (($rType == "adv") && ($rPermissions["is_admin"])) {
        if ((count($rPermissions["advanced"]) > 0) && ($rUserInfo["member_group_id"] <> 1)) {
            return in_array($rID, $rPermissions["advanced"]);
        } else {
            return true;
        }
    }
    return false;
}

function getMemberGroups() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `member_groups` ORDER BY `group_id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["group_id"])] = $row;
        }
    }
    return $return;
}

function getMemberGroup($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `member_groups` WHERE `group_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getRegisteredUsernames() {
    global $db;
    $return = array();
    $result = $db->query("SELECT `id`, `username` FROM `reg_users` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row["username"];
        }
    }
    return $return;
}

function getOutputs($rUser = null) {
    global $db;
    $return = array();
    if ($rUser) {
        $result = $db->query("SELECT `access_output_id` FROM `user_output` WHERE `user_id` = " . intval($rUser) . ";");
    } else {
        $result = $db->query("SELECT * FROM `access_output` ORDER BY `access_output_id` ASC;");
    }
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if ($rUser) {
                $return[] = $row["access_output_id"];
            } else {
                $return[] = $row;
            }
        }
    }
    return $return;
}

function getUserBouquets() {
    global $db;
    $return = array();
    $result = $db->query("SELECT `id`, `bouquet` FROM `users` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getBouquets() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getBouquetOrder() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getBouquet($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `bouquets` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getLanguages() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `languages` ORDER BY `key` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function addToBouquet($rType, $rBouquetID, $rID) {
    global $db;
    $rBouquet = getBouquet($rBouquetID);
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
        $rChannels = json_decode($rBouquet[$rColumn], True);
        if (!in_array($rID, $rChannels)) {
            $rChannels[] = $rID;
            if (count($rChannels) > 0) {
                $db->query("UPDATE `bouquets` SET `" . ESC($rColumn) . "` = '" . ESC(json_encode(array_values($rChannels))) . "' WHERE `id` = " . intval($rBouquetID) . ";");
            }
        }
    }
}

function removeFromBouquet($rType, $rBouquetID, $rID) {
    global $db;
    $rBouquet = getBouquet($rBouquetID);
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
        $rChannels = json_decode($rBouquet[$rColumn], True);
        if (($rKey = array_search($rID, $rChannels)) !== false) {
            unset($rChannels[$rKey]);
            $db->query("UPDATE `bouquets` SET `" . ESC($rColumn) . "` = '" . ESC(json_encode(array_values($rChannels))) . "' WHERE `id` = " . intval($rBouquetID) . ";");
        }
    }
}

function getPackages($rGroup = null) {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `packages` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            if ((!isset($rGroup)) or (in_array(intval($rGroup), json_decode($row["groups"], True)))) {
                $return[intval($row["id"])] = $row;
            }
        }
    }
    return $return;
}

function getPackage($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `packages` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getTranscodeProfile($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `transcoding_profiles` WHERE `profile_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getUserAgent($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `blocked_user_agents` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getISP($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `isp_addon` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getBlockedIP($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `blocked_ips` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getRTMPIP($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `rtmp_ips` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getEPGs() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `epg` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getCategories($rType = "live") {
    global $db;
    $return = array();
    if ($rType) {
        $result = $db->query("SELECT * FROM `stream_categories` WHERE `category_type` = '" . ESC($rType) . "' ORDER BY `cat_order` ASC;");
    } else {
        $result = $db->query("SELECT * FROM `stream_categories` ORDER BY `cat_order` ASC;");
    }
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getChannels($rType = "live") {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `stream_categories` WHERE `category_type` = '" . ESC($rType) . "' ORDER BY `cat_order` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getChannelsByID($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `streams` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return False;
}

function getCategory($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `stream_categories` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return False;
}

function getMag($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `mag_devices` WHERE `mag_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        $row = $result->fetch_assoc();
        $result = $db->query("SELECT `pair_id` FROM `users` WHERE `id` = " . intval($row["user_id"]) . ";");
        if (($result) && ($result->num_rows == 1)) {
            $magrow = $result->fetch_assoc();
            $row["paired_user"] = $magrow["pair_id"];
            $row["username"] = getUser($row["paired_user"])["username"];
        }
        return $row;
    }
    return array();
}

function getEnigma($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `enigma2_devices` WHERE `device_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        $row = $result->fetch_assoc();
        $result = $db->query("SELECT `pair_id` FROM `users` WHERE `id` = " . intval($row["user_id"]) . ";");
        if (($result) && ($result->num_rows == 1)) {
            $e2row = $result->fetch_assoc();
            $row["paired_user"] = $e2row["pair_id"];
            $row["username"] = getUser($row["paired_user"])["username"];
        }
        return $row;
    }
    return array();
}

function getMAGUser($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `mag_devices` WHERE `user_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return "";
}

function getMAGLockDevice($rID) {
    global $db;
    $result = $db->query("SELECT `lock_device` FROM `mag_devices` WHERE `user_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc()["lock_device"];
    }
    return "";
}

function getE2User($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `enigma2_devices` WHERE `user_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return "";
}

function getTicket($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `tickets` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows > 0)) {
        $row = $result->fetch_assoc();
        $row["replies"] = array();
        $row["title"] = htmlspecialchars($row["title"]);
        $result = $db->query("SELECT * FROM `tickets_replies` WHERE `ticket_id` = " . intval($rID) . " ORDER BY `date` ASC;");
        while ($reply = $result->fetch_assoc()) {
            // Hack to fix display issues on short text.
            $reply["message"] = htmlspecialchars($reply["message"]);
            if (strlen($reply["message"]) < 80) {
                $reply["message"] .= str_repeat("&nbsp; ", 80 - strlen($reply["message"]));
            }
            $row["replies"][] = $reply;
        }
        $row["user"] = getRegisteredUser($row["member_id"]);
        return $row;
    }
    return null;
}

function getExpiring($rID) {
    global $db;
    $rAvailableMembers = array_keys(getRegisteredUsers($rID));
    $return = array();
    $result = $db->query("SELECT `id`, `member_id`, `username`, `password`, `exp_date` FROM `users` WHERE `member_id` IN (" . ESC(join(",", $rAvailableMembers)) . ") AND `exp_date` >= UNIX_TIMESTAMP() ORDER BY `exp_date` ASC LIMIT 100;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
    }
    return $return;
}

function getTickets($rID = null) {
    global $db;
    $return = array();
    if ($rID) {
        $result = $db->query("SELECT `tickets`.`id`, `tickets`.`member_id`, `tickets`.`title`, `tickets`.`status`, `tickets`.`admin_read`, `tickets`.`user_read`, `reg_users`.`username` FROM `tickets`, `reg_users` WHERE `member_id` = " . intval($rID) . " AND `reg_users`.`id` = `tickets`.`member_id` ORDER BY `id` DESC;");
    } else {
        $result = $db->query("SELECT `tickets`.`id`, `tickets`.`member_id`, `tickets`.`title`, `tickets`.`status`, `tickets`.`admin_read`, `tickets`.`user_read`, `reg_users`.`username` FROM `tickets`, `reg_users` WHERE `reg_users`.`id` = `tickets`.`member_id` ORDER BY `id` DESC;");
    }
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $dateresult = $db->query("SELECT MIN(`date`) AS `date` FROM `tickets_replies` WHERE `ticket_id` = " . intval($row["id"]) . " AND `admin_reply` = 0;");
            if ($rDate = $dateresult->fetch_assoc()["date"]) {
                $row["created"] = date("Y-m-d H:i", $rDate);
            } else {
                $row["created"] = "";
            }
            $dateresult = $db->query("SELECT MAX(`date`) AS `date` FROM `tickets_replies` WHERE `ticket_id` = " . intval($row["id"]) . " AND `admin_reply` = 1;");
            if ($rDate = $dateresult->fetch_assoc()["date"]) {
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

function checkTrials() {
    global $db, $rPermissions, $rUserInfo;
    $rTotal = $rPermissions["total_allowed_gen_trials"];
    if ($rTotal > 0) {
        $rTotalIn = $rPermissions["total_allowed_gen_in"];
        if ($rTotalIn == "hours") {
            $rTime = time() - (intval($rTotal) * 3600);
        } else {
            $rTime = time() - (intval($rTotal) * 3600 * 24);
        }
        $result = $db->query("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `member_id` = " . intval($rUserInfo["id"]) . " AND `created_at` >= " . $rTime . " AND `is_trial` = 1;");
        return $result->fetch_assoc()["count"] < $rTotal;
    }
    return false;
}

function cryptPassword($password, $salt = "xtreamcodes", $rounds = 20000) {
    if ($salt == "") {
        $salt = substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 16);
    }
    $hash = crypt($password, sprintf('$6$rounds=%d$%s$', $rounds, $salt));
    return $hash;
}

function getIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getID() {
    if (file_exists(MAIN_DIR . "adtools/settings.json")) {
        return json_decode(file_get_contents(MAIN_DIR . "adtools/settings.json"), True)["rid"];
    }
    return 0;
}

function getPermissions($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `member_groups` WHERE `group_id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function doLogin($rUsername, $rPassword) {
    global $db;
    $result = $db->query("SELECT `id`, `username`, `password`, `member_group_id`, `google_2fa_sec`, `status` FROM `reg_users` WHERE `username` = '" . ESC($rUsername) . "' LIMIT 1;");
    if (($result) && ($result->num_rows == 1)) {
        $rRow = $result->fetch_assoc();
        if (cryptPassword($rPassword) == $rRow["password"]) {
            return $rRow;
        }
    }
    return null;
}

function getSubresellerSetups() {
    global $db;
    $return = array();
    $result = $db->query("SELECT * FROM `subreseller_setup` ORDER BY `id` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function getSubresellerSetup($rID) {
    global $db;
    $result = $db->query("SELECT * FROM `subreseller_setup` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function getEpisodeParents() {
    global $db;
    $return = array();
    $result = $db->query("SELECT `series_episodes`.`stream_id`, `series`.`id`, `series`.`title` FROM `series_episodes` LEFT JOIN `series` ON `series`.`id` = `series_episodes`.`series_id`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["stream_id"])] = $row;
        }
    }
    return $return;
}

function getSeriesList() {
    global $db;
    $return = array();
    $result = $db->query("SELECT `id`, `title` FROM `series` ORDER BY `title` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $return[intval($row["id"])] = $row;
        }
    }
    return $return;
}

function checkTable($rTable) {
    global $db;
    $rTableQuery = array(
        "languages" => array("CREATE TABLE `languages` (`key` varchar(128) NOT NULL DEFAULT '', `language` varchar(4096) NOT NULL DEFAULT '', PRIMARY KEY (`key`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;", "INSERT INTO `languages`(`key`, `language`) VALUES('en', 'English');"),

    );
    if ((!$db->query("DESCRIBE `" . ESC($rTable) . "`;")) && (isset($rTableQuery[$rTable]))) {
        // Doesn't exist! Create it.
        foreach ($rTableQuery[$rTable] as $rQuery) {
            $db->query($rQuery);
        }
    }
}

function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;
    $days = floor($inputSeconds / $secondsInADay);
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);
    $obj = array(
        'd' => (int) $days,
        'h' => (int) $hours,
        'm' => (int) $minutes,
        's' => (int) $seconds,
    );
    return $obj;
}

function getWorldMapLive() {
    global $db;
    $rQuery = "SELECT geoip_country_code, count(geoip_country_code) AS total FROM lines_live GROUP BY geoip_country_code";
    if ($rResult = $db->query($rQuery)) {
        while ($row = $rResult->fetch_assoc()) {
            $WorldMapLive = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
            echo $WorldMapLive;
        }
    }
}

function getWorldMapActivity() {
    global $db;
    $rQuery = "SELECT DISTINCT geoip_country_code, COUNT(DISTINCT user_id) AS total FROM user_activity GROUP BY geoip_country_code";
    if ($rResult = $db->query($rQuery)) {
        while ($row = $rResult->fetch_assoc()) {
            $WorldMapActivity = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
            echo $WorldMapActivity;
        }
    }
}

function getWorldMapTotalActivity() {
    global $db;
    $rQuery = "SELECT geoip_country_code, count(geoip_country_code) AS total FROM user_activity GROUP BY geoip_country_code";
    if ($rResult = $db->query($rQuery)) {
        while ($row = $rResult->fetch_assoc()) {
            $WorldMapTotalActivity = "{\"code\":" . json_encode($row["geoip_country_code"]) . ",\"value\":" . json_encode($row["total"]) . "},";
            echo $WorldMapTotalActivity;
        }
    }
}
function writeAdminSettings() {
    global $rAdminSettings, $db;
    foreach ($rAdminSettings as $rKey => $rValue) {
        if (strlen($rKey) > 0) {
            $db->query("REPLACE INTO `admin_settings`(`type`, `value`) VALUES('" . ESC($rKey) . "', '" . ESC($rValue) . "');");
        }
    }
}

function downloadImage($rImage) {
    if ((strlen($rImage) > 0) && (substr(strtolower($rImage), 0, 4) == "http")) {
        $rPathInfo = pathinfo($rImage);
        $rExt = $rPathInfo["extension"];
        if (in_array(strtolower($rExt), array("jpg", "jpeg", "png"))) {
            $rPrevPath = MAIN_DIR . "wwwdir/images/" . $rPathInfo["filename"] . "." . $rExt;
            if (file_exists($rPrevPath)) {
                return getURL() . "/images/" . $rPathInfo["filename"] . "." . $rExt;
            } else {
                $rCont = stream_context_create(array('http' => array('timeout' => 10, 'method' => "GET")));
                $rData = file_get_contents($rImage, false, $rCont);
                if (strlen($rData) > 0) {
                    $rFilename = md5($rPathInfo["filename"]);
                    $rPath = MAIN_DIR . "wwwdir/images/" . $rFilename . "." . $rExt;
                    file_put_contents($rPath, $rData);
                    if (strlen(file_get_contents($rPath)) == strlen($rData)) {
                        return getURL() . "/images/" . $rFilename . "." . $rExt;
                    }
                }
            }
        }
    }
    return $rImage;
}

function updateSeries($rID) {
    global $db, $rSettings, $rAdminSettings;
    require_once("tmdb.php");
    $result = $db->query("SELECT `tmdb_id` FROM `series` WHERE `id` = " . intval($rID) . ";");
    if (($result) && ($result->num_rows == 1)) {
        $rTMDBID = $result->fetch_assoc()["tmdb_id"];
        if (strlen($rTMDBID) > 0) {
            if (strlen($rAdminSettings["tmdb_language"]) > 0) {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
            } else {
                $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
            }
            $rReturn = array();
            $rSeasons = json_decode($rTMDB->getTVShow($rTMDBID)->getJSON(), True)["seasons"];
            foreach ($rSeasons as $rSeason) {
                if ($rAdminSettings["download_images"]) {
                    $rSeason["cover"] = downloadImage("https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rSeason["poster_path"]);
                } else {
                    $rSeason["cover"] = "https://image.tmdb.org/t/p/w600_and_h900_bestv2" . $rSeason["poster_path"];
                }
                $rSeason["cover_big"] = $rSeason["cover"];
                unset($rSeason["poster_path"]);
                $rReturn[] = $rSeason;
            }
            $db->query("UPDATE `series` SET `seasons` = '" . ESC(json_encode($rReturn)) . "', `last_modified` = " . intval(time()) . " WHERE `id` = " . intval($rID) . ";");
        }
    }
}

function getFooter() {
    // Don't be a dick. Leave it.
    global $rAdminSettings, $rPermissions, $rSettings, $_;
    if ($rPermissions["is_admin"]) {
        return $_["copyright"] . " &copy; 2023 - " . date("Y") . " - <a href=\"https://github.com/Vateron-Media/Xtream_main\">Xtream UI</a> " . getScriptVer() . " - " . $_["free_forever"];
    } else {
        return $rSettings["copyrights_text"];
    }
}

function getURL() {
    global $rServers, $_INFO;
    if (strlen($rServers[$_INFO["server_id"]]["domain_name"]) > 0) {
        return "http://" . $rServers[$_INFO["server_id"]]["domain_name"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
    } else if (strlen($rServers[$_INFO["server_id"]]["vpn_ip"]) > 0) {
        return "http://" . $rServers[$_INFO["server_id"]]["vpn_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
    } else {
        return "http://" . $rServers[$_INFO["server_id"]]["server_ip"] . ":" . $rServers[$_INFO["server_id"]]["http_broadcast_port"];
    }
}

function scanBouquets() {
    global $db;
    $rStreamIDs = array(0 => array(), 1 => array());
    $result = $db->query("SELECT `id` FROM `streams`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rStreamIDs[0][] = intval($row["id"]);
        }
    }
    $result = $db->query("SELECT `id` FROM `series`;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $rStreamIDs[1][] = intval($row["id"]);
        }
    }
    foreach (getBouquets() as $rID => $rBouquet) {
        $rUpdate = array(0 => array(), 1 => array());
        foreach (json_decode($rBouquet["bouquet_channels"], True) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[0])) {
                $rUpdate[0][] = intval($rID);
            }
        }
        foreach (json_decode($rBouquet["bouquet_series"], True) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[1])) {
                $rUpdate[1][] = intval($rID);
            }
        }
        $db->query("UPDATE `bouquets` SET `bouquet_channels` = '" . ESC(json_encode($rUpdate[0])) . "', `bouquet_series` = '" . ESC(json_encode($rUpdate[1])) . "' WHERE `id` = " . intval($rBouquet["id"]) . ";");
    }
}

function scanBouquet($rID) {
    global $db;
    $rBouquet = getBouquet($rID);
    if ($rBouquet) {
        $rStreamIDs = array();
        $result = $db->query("SELECT `id` FROM `streams`;");
        if (($result) && ($result->num_rows > 0)) {
            while ($row = $result->fetch_assoc()) {
                $rStreamIDs[0][] = intval($row["id"]);
            }
        }
        $result = $db->query("SELECT `id` FROM `series`;");
        if (($result) && ($result->num_rows > 0)) {
            while ($row = $result->fetch_assoc()) {
                $rStreamIDs[1][] = intval($row["id"]);
            }
        }
        $rUpdate = array(0 => array(), 1 => array());
        foreach (json_decode($rBouquet["bouquet_channels"], True) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[0])) {
                $rUpdate[0][] = intval($rID);
            }
        }
        foreach (json_decode($rBouquet["bouquet_series"], True) as $rID) {
            if (in_array(intval($rID), $rStreamIDs[1])) {
                $rUpdate[1][] = intval($rID);
            }
        }
        $db->query("UPDATE `bouquets` SET `bouquet_channels` = '" . ESC(json_encode($rUpdate[0])) . "', `bouquet_series` = '" . ESC(json_encode($rUpdate[1])) . "' WHERE `id` = " . intval($rBouquet["id"]) . ";");
    }
}

function getNextOrder() {
    global $db;
    $result = $db->query("SELECT MAX(`order`) AS `order` FROM `streams`;");
    if (($result) && ($result->num_rows == 1)) {
        return intval($result->fetch_assoc()["order"]) + 1;
    }
    return 0;
}

function generateSeriesPlaylist($rSeriesNo) {
    global $db, $rServers, $rSettings;
    $rReturn = array("success" => false, "sources" => array(), "server_id" => 0);
    $result = $db->query("SELECT `stream_id` FROM `series_episodes` WHERE `series_id` = " . intval($rSeriesNo) . " ORDER BY `season_num` ASC, `sort` ASC;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            $resultB = $db->query("SELECT `stream_source` FROM `streams` WHERE `id` = " . intval($row["stream_id"]) . ";");
            if (($resultB) && ($resultB->num_rows > 0)) {
                $rSource = json_decode($resultB->fetch_assoc()["stream_source"], True)[0];
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

function flushIPs() {
    global $db, $rServers;
    $rCommand = "sudo /sbin/iptables -P INPUT ACCEPT && sudo /sbin/iptables -P OUTPUT ACCEPT && sudo /sbin/iptables -P FORWARD ACCEPT && sudo /sbin/iptables -F";
    foreach ($rServers as $rServer) {
        sexec($rServer["id"], $rCommand);
    }
    $db->query("DELETE FROM `blocked_ips`;");
}

function flushLogins() {
    global $db, $rServers;
    foreach ($rServers as $rServer) {
        sexec($rServer["id"], $rCommand);
    }
    $db->query("DELETE FROM `login_flood`;");
}

function flushEvents() {
    global $db, $rServers;
    foreach ($rServers as $rServer) {
        sexec($rServer["id"], $rCommand);
    }
    $db->query("DELETE FROM `mag_events`;");
}

function updateTables() {
    global $db;
    // Update table settings etc.
    //checkTable("languages");
    //priority backup
    //$db->query("UPDATE settings SET priority_backup = 1;");

    // Update Categories
    updateTMDbCategories();
}

function updateTMDbCategories() {
    global $db, $rAdminSettings, $rSettings;
    include "tmdb.php";
    if (strlen($rAdminSettings["tmdb_language"]) > 0) {
        $rTMDB = new TMDB($rSettings["tmdb_api_key"], $rAdminSettings["tmdb_language"]);
    } else {
        $rTMDB = new TMDB($rSettings["tmdb_api_key"]);
    }
    $rCurrentCats = array(1 => array(), 2 => array());
    $rResult = $db->query("SELECT `id`, `type`, `genre_id` FROM `watch_categories`;");
    if (($rResult) && ($rResult->num_rows > 0)) {
        while ($rRow = $rResult->fetch_assoc()) {
            if (in_array($rRow["genre_id"], $rCurrentCats[$rRow["type"]])) {
                $db->query("DELETE FROM `watch_categories` WHERE `id` = " . intval($rRow["id"]) . ";");
            }
            $rCurrentCats[$rRow["type"]][] = $rRow["genre_id"];
        }
    }
    $rMovieGenres = $rTMDB->getMovieGenres();
    foreach ($rMovieGenres as $rMovieGenre) {
        if (!in_array($rMovieGenre->getID(), $rCurrentCats[1])) {
            $db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(1, " . intval($rMovieGenre->getID()) . ", '" . ESC($rMovieGenre->getName()) . "', 0, '[]');");
        }
        if (!in_array($rMovieGenre->getID(), $rCurrentCats[2])) {
            $db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(2, " . intval($rMovieGenre->getID()) . ", '" . ESC($rMovieGenre->getName()) . "', 0, '[]');");
        }
    }
    $rTVGenres = $rTMDB->getTVGenres();
    foreach ($rTVGenres as $rTVGenre) {
        if (!in_array($rTVGenre->getID(), $rCurrentCats[1])) {
            $db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(1, " . intval($rTVGenre->getID()) . ", '" . ESC($rTVGenre->getName()) . "', 0, '[]');");
        }
        if (!in_array($rTVGenre->getID(), $rCurrentCats[2])) {
            $db->query("INSERT INTO `watch_categories`(`type`, `genre_id`, `genre`, `category_id`, `bouquets`) VALUES(2, " . intval($rTVGenre->getID()) . ", '" . ESC($rTVGenre->getName()) . "', 0, '[]');");
        }
    }
}

function forceSecurity() {
    global $db;
    $db->query("UPDATE `settings` SET `double_auth` = 1, `mag_security` = 1;");
    $db->query("UPDATE `admin_settings` SET `pass_length` = 8 WHERE `pass_length` < 8;");
    $db->query("UPDATE `settings` SET `double_auth` = 1, `mag_security` = 1;");
}

if (file_exists("/home/xtreamcodes/admin/.update")) {
    unlink("/home/xtreamcodes/admin/.update");
    if (!file_exists("/home/xtreamcodes/admin/.update")) {
        updateTables();
        forceSecurity();
    }
}

$rTableSearch = strtolower(basename($_SERVER["SCRIPT_FILENAME"], '.php')) === "table_search";
$_GET = XSSRow($_GET, $rTableSearch);
$_POST = XSSRow($_POST, $rTableSearch); // Parse user input.

if (isset($_SESSION['hash'])) {
    $rUserInfo = getRegisteredUserHash($_SESSION['hash']);
    $rAdminSettings["dark_mode"] = $rUserInfo["dark_mode"];
    $rAdminSettings["expanded_sidebar"] = $rUserInfo["expanded_sidebar"];
    $rSettings["sidebar"] = $rUserInfo["sidebar"];
    $rPermissions = getPermissions($rUserInfo['member_group_id']);
    if ($rPermissions["is_admin"]) {
        $rPermissions["is_reseller"] = 0;
    }
    $rPermissions["advanced"] = json_decode($rPermissions["allowed_pages"], True);
    if ((!$rUserInfo) or (!$rPermissions) or ((!$rPermissions["is_admin"]) && (!$rPermissions["is_reseller"])) or (($_SESSION['ip'] <> getIP()) && ($rAdminSettings["ip_logout"]))) {
        unset($rUserInfo);
        unset($rPermissions);
        session_unset();
        session_destroy();
        header("Location: ./index.php");
    }
    $rCategories = getCategories();
    $rServers = getStreamingServers();
    $rServerError = False;
    foreach ($rServers as $rServer) {
        if (((((time() - $rServer["last_check_ago"]) > 360)) or ($rServer["status"] == 2)) and ($rServer["can_delete"] == 1) and ($rServer["status"] <> 3)) {
            $rServerError = True;
        }
        if (($rServer["status"] == 3) && ($rServer["last_check_ago"] > 0)) {
            $db->query("UPDATE `streaming_servers` SET `status` = 1 WHERE `id` = " . intval($rServer["id"]) . ";");
            $rServers[intval($rServer["id"])]["status"] = 1;
        }
    }
}
function getServerStatus() {
    global $db;
    $rResult = $db->query("SELECT `status` FROM `streaming_servers` WHERE `is_main` = 1;");
    if ($rResult->num_rows > 0) {
        return $rResult->fetch_assoc()['status'];
    }
}

/**
 * Recursively removes a directory and all of its contents.
 *
 * This function takes a directory path as an argument and deletes all files and 
 * subdirectories within that directory. It first checks if the specified path 
 * is a directory. If it is, it scans the directory for its contents and iterates 
 * through each item. For each item, it checks if it is a directory or a file. 
 * If it is a directory, the function calls itself recursively to remove the 
 * subdirectory. If it is a file, it deletes the file. Once all contents are 
 * removed, the function deletes the original directory.
 *
 * @param string $dir The path to the directory to be removed.
 * 
 * @return void
 *
 * @throws ErrorException If the specified path is not a directory or if an 
 *                        error occurs during file or directory deletion.
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir")
                    rrmdir($dir . "/" . $object);
                else unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
