<?php

define("MAX_API_REQ_INTERVAL", 1);
define("MAX_TRIES", 30);
define("BLACKLIST", "IPTV_PANEL_DIRtmp/blacklist");
if (!(!file_exists("TMP_DIRd52d7d1df4f329bda8b2d9f67fa5d846") && file_exists("TMP_DIR5a9ccab64e61d9af12baa7d4011acc1a"))) {
    goto B7659fdb52f2416e910c60aaf9c6b08a;
}
if (!(file_exists("IPTV_PANEL_DIRwatchdog_optimize") && isset($_REQUEST["action"]) && isset($_REQUEST["type"]) && $_REQUEST["type"] == "watchdog" && $_REQUEST["action"] == "get_events")) {
    if (!(!empty($_REQUEST["username"]) && !empty($_REQUEST["password"]))) {
        goto Bbf3210aaaa65beef8d46a20ab02b50d;
    }
    $dae7248d7a25b2f92332941e311beb6d = md5(strtolower($_REQUEST["username"] . $_REQUEST["password"]));
    Bbf3210aaaa65beef8d46a20ab02b50d:
    if (!(isset($dae7248d7a25b2f92332941e311beb6d) && !file_exists("/home/xtreamcodes/iptv_xtream_codes/tmp/" . $dae7248d7a25b2f92332941e311beb6d))) {
        if (!(file_exists(BLACKLIST) && isset($dae7248d7a25b2f92332941e311beb6d))) {
            goto caf8a07a8b347b093ebd50f47a60e153;
        }
        $Afa23c629e157f4e55943a2d45bc7bce = json_decode(file_get_contents(BLACKLIST), true);
        if (!in_array($dae7248d7a25b2f92332941e311beb6d, $Afa23c629e157f4e55943a2d45bc7bce)) {
            caf8a07a8b347b093ebd50f47a60e153:
            $A83940e5378a0c0353bdedf250915dcc = array("/panel_api.php", "/player_api.php", "/xmltv.php", "/get.php");
            if (!in_array($_SERVER["SCRIPT_NAME"], $A83940e5378a0c0353bdedf250915dcc)) {
                goto f09f131eb705a566de0eeafe6daed185;
            }
            if (isset($dae7248d7a25b2f92332941e311beb6d)) {
                $b63b894b2f9b5aabe135ef4a17f2aed8 = TMP_DIR . $dae7248d7a25b2f92332941e311beb6d . "_pr0.flood";
                if (file_exists($b63b894b2f9b5aabe135ef4a17f2aed8)) {
                    $Fa6f56ee50a6331b464fdee0f2d47c94 = json_decode(file_get_contents($b63b894b2f9b5aabe135ef4a17f2aed8), true);
                    if (time() - $Fa6f56ee50a6331b464fdee0f2d47c94["last_request"] <= MAX_API_REQ_INTERVAL) {
                        ++$Fa6f56ee50a6331b464fdee0f2d47c94["requests"];
                        if (!($Fa6f56ee50a6331b464fdee0f2d47c94["requests"] >= MAX_TRIES)) {
                            $Fa6f56ee50a6331b464fdee0f2d47c94["last_request"] = time();
                            file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, json_encode($Fa6f56ee50a6331b464fdee0f2d47c94), LOCK_EX);
                            goto fc1498b1023a0e3daaa3487e2740cd76;
                        }
                        $Afa23c629e157f4e55943a2d45bc7bce[] = $dae7248d7a25b2f92332941e311beb6d;
                        file_put_contents(BLACKLIST, json_encode(array_filter(array_unique($Afa23c629e157f4e55943a2d45bc7bce))), LOCK_EX);
                        unlink($b63b894b2f9b5aabe135ef4a17f2aed8);
                        return;
                    }
                    $Fa6f56ee50a6331b464fdee0f2d47c94["requests"] = 0;
                    $Fa6f56ee50a6331b464fdee0f2d47c94["last_request"] = time();
                    file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, json_encode($Fa6f56ee50a6331b464fdee0f2d47c94), LOCK_EX);
                    fc1498b1023a0e3daaa3487e2740cd76:
                    goto Eb37932aba8bc967ed93721266cceab7;
                }
                file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, json_encode(array("requests" => 0, "last_request" => time())), LOCK_EX);
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
$d76067cf9572f7a6691c85c12faf2a29 = array("data" => array("msgs" => 0, "additional_services_on" => 1));
die(json_encode(array("js" => $d76067cf9572f7a6691c85c12faf2a29), JSON_PARTIAL_OUTPUT_ON_ERROR));
