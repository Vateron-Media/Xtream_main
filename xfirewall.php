<?php

define("MAX_API_REQ_INTERVAL", 1);
define("MAX_TRIES", 30);
define("BLACKLIST", IPTV_PANEL_DIR . "tmp/blacklist");
if (!(!file_exists(TMP_DIR . "firewall_off") && file_exists(TMP_DIR . "firewall_on"))) {
    goto B7659fdb52f2416e910c60aaf9c6b08a;
}
if (!(file_exists(IPTV_PANEL_DIR . "watchdog_optimize") && isset($_REQUEST["action"]) && isset($_REQUEST["type"]) && $_REQUEST["type"] == "watchdog" && $_REQUEST["action"] == "get_events")) {
    if (!empty($_REQUEST["username"]) && !empty($_REQUEST["password"])) {
        $hash_value = md5(strtolower($_REQUEST["username"] . $_REQUEST["password"]));
    }
    if (!(isset($hash_value) && !file_exists("/home/xtreamcodes/iptv_xtream_codes/tmp/" . $hash_value))) {
        if (!(file_exists(BLACKLIST) && isset($hash_value))) {
            goto caf8a07a8b347b093ebd50f47a60e153;
        }
        $user_blacklist = json_decode(file_get_contents(BLACKLIST), true);
        if (!in_array($hash_value, $user_blacklist)) {
            caf8a07a8b347b093ebd50f47a60e153:
            $script_name = array("/panel_api.php", "/player_api.php", "/xmltv.php", "/get.php");
            if (!in_array($_SERVER["SCRIPT_NAME"], $script_name)) {
                goto f09f131eb705a566de0eeafe6daed185;
            }
            if (isset($hash_value)) {
                $user_flood = TMP_DIR . $hash_value . "_pr0.flood";
                if (file_exists($user_flood)) {
                    $user_data = json_decode(file_get_contents($user_flood), true);
                    if (time() - $user_data["last_request"] <= MAX_API_REQ_INTERVAL) {
                        ++$user_data["requests"];
                        if (!($user_data["requests"] >= MAX_TRIES)) {
                            $user_data["last_request"] = time();
                            file_put_contents($user_flood, json_encode($user_data), LOCK_EX);
                            goto fc1498b1023a0e3daaa3487e2740cd76;
                        }
                        $user_blacklist[] = $hash_value;
                        file_put_contents(BLACKLIST, json_encode(array_filter(array_unique($user_blacklist))), LOCK_EX);
                        unlink($user_flood);
                        return;
                    }
                    $user_data["requests"] = 0;
                    $user_data["last_request"] = time();
                    file_put_contents($user_flood, json_encode($user_data), LOCK_EX);
                    fc1498b1023a0e3daaa3487e2740cd76:
                    goto Eb37932aba8bc967ed93721266cceab7;
                }
                file_put_contents($user_flood, json_encode(array("requests" => 0, "last_request" => time())), LOCK_EX);
                Eb37932aba8bc967ed93721266cceab7:
                f09f131eb705a566de0eeafe6daed185:
                B7659fdb52f2416e910c60aaf9c6b08a:
                // [PHPDeobfuscator] Implied script end
                return;
            }
            http_response_code(401);
            die;
        }
        http_response_code(404);
        die;
    }
    http_response_code(404);
    die;
}
$data = array("data" => array("msgs" => 0, "additional_services_on" => 1));
die(json_encode(array("js" => $data), JSON_PARTIAL_OUTPUT_ON_ERROR));
