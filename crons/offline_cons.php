<?php
function userActivityQueryData($connections, &$query) {
    global $ipTV_db;
    if (file_exists($connections)) {
        $fp = fopen($connections, "r");
        while (feof($fp)) {
            $data = trim(fgets($fp));
            if (!empty($data)) {
                $data = json_decode(base64_decode($data), true);
                $data = array_map([$ipTV_db, "escape"], $data);
                $query .= "(" . SERVER_ID . ",'" . $data["user_id"] . "','" . $data["isp"] . "','" . $data["external_device"] . "','" . $data["stream_id"] . "','" . $data["date_start"] . "','" . $data["user_agent"] . "','" . $data["user_ip"] . "','" . $data["date_end"] . "','" . $data["container"] . "','" . $data["geoip_country_code"] . "'),";
            }
        }
        fclose($fp);
    }
    return $query;
}

set_time_limit(0);
if (@$argc) {
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    cli_set_process_title("XtreamCodes[Offline Cons Parser]");
    $unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
    KillProcessCmd($unique_id);
    $connections = TMP_DIR . "offline_cons";
    $query = "";
    if (file_exists($connections)) {
        userActivityQueryData($connections, $query);
        unlink($connections);
    }
    $query = rtrim($query, ",");
    if (!empty($query)) {
        $ipTV_db->simple_query("INSERT INTO `user_activity` (`server_id`,`user_id`,`isp`,`external_device`,`stream_id`,`date_start`,`user_agent`,`user_ip`,`date_end`,`container`,`geoip_country_code`) VALUES " . $query);
    }
} else {
    exit(0);
}
