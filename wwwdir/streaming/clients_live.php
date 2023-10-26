<?php

register_shutdown_function("shutdown");
set_time_limit(0);
require "../init.php";
$f0ac6ad2b40669833242a10c23cad2e0 = true;
if (!isset(a78bf8D35765Be2408c50712Ce7a43ad::$request["qs"])) {
    goto A87f82ad8273650efd2065ecd17280d8;
}
if (!stristr(A78bf8D35765be2408c50712Ce7a43aD::$request["qs"], ":p=")) {
    goto C8756961c117a0c6ad273b77cf847622;
}
$Af236a5462da6c610990628f594f801e = explode(":p=", A78bF8D35765bE2408c50712cE7a43AD::$request["qs"]);
a78bF8d35765Be2408C50712Ce7A43ad::$request["password"] = $Af236a5462da6c610990628f594f801e[1];
A78BF8D35765be2408C50712Ce7a43aD::$request["username"] = substr($Af236a5462da6c610990628f594f801e[0], 2);
C8756961c117a0c6ad273b77cf847622:
A87f82ad8273650efd2065ecd17280d8:
if (!(!isset(A78Bf8D35765bE2408C50712Ce7a43AD::$request["extension"]) || !isset(a78Bf8d35765Be2408C50712cE7A43ad::$request["username"]) || !isset(a78BF8d35765bE2408c50712cE7A43AD::$request["password"]) || !isset(A78Bf8D35765Be2408c50712cE7A43AD::$request["stream"]))) {
    $ded15b7e9c47ec5a3dea3c69332153c8 = new EA991Ba3eC74f0Fb90aCc94c2D2De518(GEOIP2_FILENAME);
    $E821605d1d9382d422040b86d29632d9 = 0;
    $e84deaa90130ae0163381d3f216773e3 = true;
    $Cec7b3525ded5578bb4eaefe5020eb98 = null;
    $f4889efa84e1f2e30e5e9780973f68cb = CD89785224751CcA8017139dAF9E891e::e1f75a50f74a8F4E2129BA474f45D670();
    $userAgent = empty($_SERVER["HTTP_USER_AGENT"]) ? '' : htmlentities(trim($_SERVER["HTTP_USER_AGENT"]));
    $a349f0750f0a814bd31ec4b3da51da95 = null;
    $f6806488699d3315dc5dc1e27a401b3e = a78BF8d35765bE2408c50712CE7A43aD::$request["username"];
    $password = a78bf8D35765bE2408C50712CE7A43Ad::$request["password"];
    $ba85d77d367dcebfcc2a3db9e83bb581 = intval(A78BF8d35765BE2408c50712CE7A43ad::$request["stream"]);
    $F1350a5569e4b73d2f9cb26483f2a0c1 = preg_replace("/[^A-Za-z0-9 ]/", '', trim(A78bf8d35765Be2408C50712cE7A43AD::$request["extension"]));
    $a7e968a4f6d75092e74cdeb1b406041a = time();
    if (!(a78Bf8d35765BE2408C50712ce7A43aD::$settings["use_buffer"] == 0)) {
        goto dc8333563874fe87ee7b2a026fc6faa5;
    }
    header("X-Accel-Buffering: no");
    dc8333563874fe87ee7b2a026fc6faa5:
    header("Access-Control-Allow-Origin: *");
    $Cd2953f76721ad9589ab3d88c42b62b9 = empty(a78bF8d35765Be2408c50712ce7a43ad::$request["play_token"]) ? null : A78bf8d35765bE2408c50712ce7a43ad::$request["play_token"];
    if ($a8df9f055e91a1e9240230b69af85555 = cd89785224751Cca8017139DaF9E891e::E5550592aA298Dd1d5EE59CDCe063A12(null, $f6806488699d3315dc5dc1e27a401b3e, $password, true, false, true, array(), false, $f4889efa84e1f2e30e5e9780973f68cb, $userAgent, array(), $Cd2953f76721ad9589ab3d88c42b62b9, $ba85d77d367dcebfcc2a3db9e83bb581)) {
        if (!isset($a8df9f055e91a1e9240230b69af85555["mag_invalid_token"])) {
            if (!($a8df9f055e91a1e9240230b69af85555["bypass_ua"] == 0)) {
                goto Eed85ed320f4c0016a5097b3b1e0c5fb;
            }
            CD89785224751cCa8017139dAf9E891e::DfBDd0F64A97aC684ce49c689D9636AC($userAgent);
            Eed85ed320f4c0016a5097b3b1e0c5fb:
            if (!($a8df9f055e91a1e9240230b69af85555["is_stalker"] == 1)) {
                goto ab84e3827eba3aef61cb991f492d4cdd;
            }
            if (!(empty(A78bf8d35765bE2408C50712cE7A43AD::$request["stalker_key"]) || $F1350a5569e4b73d2f9cb26483f2a0c1 != "ts")) {
                $fbf4eff77c556a032004b3a1769f3867 = base64_decode(urldecode(A78bf8d35765be2408c50712cE7A43aD::$request["stalker_key"]));
                if ($Bfb290509045e6df161513fd43110b8b = a78BF8d35765bE2408C50712ce7a43aD::ED16f6d550960Eb1Cab1b010B5b676Eb($fbf4eff77c556a032004b3a1769f3867, md5(a78bf8d35765Be2408c50712ce7a43ad::$settings["live_streaming_pass"]))) {
                    $d06cc9287212b4dea88b7a36f83aa576 = explode("=", $Bfb290509045e6df161513fd43110b8b);
                    if (!($d06cc9287212b4dea88b7a36f83aa576[2] != $ba85d77d367dcebfcc2a3db9e83bb581)) {
                        if (!($d06cc9287212b4dea88b7a36f83aa576[1] != $f4889efa84e1f2e30e5e9780973f68cb)) {
                            if (!(time() > $d06cc9287212b4dea88b7a36f83aa576[3])) {
                                $a349f0750f0a814bd31ec4b3da51da95 = $d06cc9287212b4dea88b7a36f83aa576[0];
                                C2c25fbff8c7c414f7df945db92c2952:
                                ab84e3827eba3aef61cb991f492d4cdd:
                                if (!(!is_null($a8df9f055e91a1e9240230b69af85555["exp_date"]) && time() >= $a8df9f055e91a1e9240230b69af85555["exp_date"])) {
                                    if (!($a8df9f055e91a1e9240230b69af85555["admin_enabled"] == 0)) {
                                        if (!($a8df9f055e91a1e9240230b69af85555["enabled"] == 0)) {
                                            if (!(empty($userAgent) && a78BF8d35765BE2408C50712Ce7a43ad::$settings["disallow_empty_user_agents"] == 1)) {
                                                $A75f2436a5614184bfe3442ddd050ec5 = $ded15b7e9c47ec5a3dea3c69332153c8->C6a76952B4CeF18F3C98c0e6a9Dd1274($f4889efa84e1f2e30e5e9780973f68cb)["registered_country"]["iso_code"];
                                                $ded15b7e9c47ec5a3dea3c69332153c8->close();
                                                if (!(!empty($a8df9f055e91a1e9240230b69af85555["allowed_ips"]) && !in_array($f4889efa84e1f2e30e5e9780973f68cb, array_map("gethostbyname", $a8df9f055e91a1e9240230b69af85555["allowed_ips"])))) {
                                                    if (empty($A75f2436a5614184bfe3442ddd050ec5)) {
                                                        goto bef1fd9013398d0b710218789e9475be;
                                                    }
                                                    $ab59908f6050f752836a953eb8bb8e52 = !empty($a8df9f055e91a1e9240230b69af85555["forced_country"]) ? true : false;
                                                    if (!($ab59908f6050f752836a953eb8bb8e52 && $a8df9f055e91a1e9240230b69af85555["forced_country"] != "ALL" && $A75f2436a5614184bfe3442ddd050ec5 != $a8df9f055e91a1e9240230b69af85555["forced_country"])) {
                                                        if (!(!$ab59908f6050f752836a953eb8bb8e52 && !in_array("ALL", A78Bf8d35765BE2408c50712ce7A43AD::$settings["allow_countries"]) && !in_array($A75f2436a5614184bfe3442ddd050ec5, A78Bf8d35765bE2408C50712CE7A43ad::$settings["allow_countries"]))) {
                                                            bef1fd9013398d0b710218789e9475be:
                                                            if (!(!empty($a8df9f055e91a1e9240230b69af85555["allowed_ua"]) && !in_array($userAgent, $a8df9f055e91a1e9240230b69af85555["allowed_ua"]))) {
                                                                if (!CD89785224751cCA8017139DAF9e891E::C57799e5196664cB99139813250673E2($f4889efa84e1f2e30e5e9780973f68cb)) {
                                                                    if (!isset($a8df9f055e91a1e9240230b69af85555["ip_limit_reached"])) {
                                                                        $f0ac6ad2b40669833242a10c23cad2e0 = false;
                                                                        if (array_key_exists($F1350a5569e4b73d2f9cb26483f2a0c1, $a8df9f055e91a1e9240230b69af85555["output_formats"])) {
                                                                            if (in_array($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["channel_ids"])) {
                                                                                if (!($a8df9f055e91a1e9240230b69af85555["isp_violate"] == 1)) {
                                                                                    if (!($a8df9f055e91a1e9240230b69af85555["isp_is_server"] == 1)) {
                                                                                        if (!($a8df9f055e91a1e9240230b69af85555["max_connections"] != 0)) {
                                                                                            goto F255f17f8c5f46c1147bf16cf510b617;
                                                                                        }
                                                                                        if (empty($a8df9f055e91a1e9240230b69af85555["pair_line_info"])) {
                                                                                            goto aa1283c87aa5ac0efd2851249bad45e6;
                                                                                        }
                                                                                        if (!($a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"] != 0)) {
                                                                                            goto f42b88222726df2b9eec5fb033bc5509;
                                                                                        }
                                                                                        if (!($a8df9f055e91a1e9240230b69af85555["pair_line_info"]["active_cons"] >= $a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"])) {
                                                                                            goto B5720156bda395b3e99a762cc7523c5b;
                                                                                        }
                                                                                        CD89785224751cca8017139dAF9e891E::a813cBD1E7EA2bb17742de7Bb2392eBF($a8df9f055e91a1e9240230b69af85555["pair_id"], $a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"]);
                                                                                        B5720156bda395b3e99a762cc7523c5b:
                                                                                        f42b88222726df2b9eec5fb033bc5509:
                                                                                        aa1283c87aa5ac0efd2851249bad45e6:
                                                                                        if (!($a8df9f055e91a1e9240230b69af85555["active_cons"] >= $a8df9f055e91a1e9240230b69af85555["max_connections"] && $F1350a5569e4b73d2f9cb26483f2a0c1 != "m3u8")) {
                                                                                            goto e0a45490dcd596cadbe45c76996c8373;
                                                                                        }
                                                                                        CD89785224751cca8017139daF9E891E::a813CBD1e7ea2BB17742de7bB2392eBF($a8df9f055e91a1e9240230b69af85555["id"], $a8df9f055e91a1e9240230b69af85555["max_connections"]);
                                                                                        e0a45490dcd596cadbe45c76996c8373:
                                                                                        F255f17f8c5f46c1147bf16cf510b617:
                                                                                        if ($ffb1e0970b62b01f46c2e57f2cded6c2 = Cd89785224751cCa8017139dAF9e891e::f3c105BcceD491229d4aEd6937f96a8c($ba85d77d367dcebfcc2a3db9e83bb581, $F1350a5569e4b73d2f9cb26483f2a0c1, $a8df9f055e91a1e9240230b69af85555, $f4889efa84e1f2e30e5e9780973f68cb, $A75f2436a5614184bfe3442ddd050ec5, $a349f0750f0a814bd31ec4b3da51da95, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], "live")) {
                                                                                            $Bb37b848bec813a5c13ea0b018962c40 = STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.m3u8";
                                                                                            if (cd89785224751Cca8017139DaF9e891E::ps_running($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], FFMPEG_PATH)) {
                                                                                                goto E1419ce7470abffbe134939de24e84e9;
                                                                                            }
                                                                                            if ($ffb1e0970b62b01f46c2e57f2cded6c2["on_demand"] == 1) {
                                                                                                if (cd89785224751CcA8017139daf9e891E::CDA72bC41975C364BC559dB25648A5b2($ffb1e0970b62b01f46c2e57f2cded6c2["monitor_pid"], $ba85d77d367dcebfcc2a3db9e83bb581)) {
                                                                                                    goto Ee6938d9520fba86bd7b945048a08b63;
                                                                                                }
                                                                                                e3Cf480c172E8b47Fe10857c2a5AEb48::E79092731573697c16A932c339D0a101($ba85d77d367dcebfcc2a3db9e83bb581);
                                                                                                Ee6938d9520fba86bd7b945048a08b63:
                                                                                                goto C4ab1744d3940966d06ba8da7879dc0a;
                                                                                            }
                                                                                            cd89785224751ccA8017139daF9e891E::d0B968cD6cfDF340ca85b1C3D9a40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_not_on_air_video", "not_on_air_video_path", $F1350a5569e4b73d2f9cb26483f2a0c1);
                                                                                            C4ab1744d3940966d06ba8da7879dc0a:
                                                                                            E1419ce7470abffbe134939de24e84e9:
                                                                                            switch ($F1350a5569e4b73d2f9cb26483f2a0c1) {
                                                                                                case "m3u8":
                                                                                                    $e84deaa90130ae0163381d3f216773e3 = false;
                                                                                                    $B1772eb944c03052cd5d180cdee51b89 = 0;
                                                                                                    a5783fd272d37bf2cf23d06cadf2c0b5:
                                                                                                    if (!(!file_exists($Bb37b848bec813a5c13ea0b018962c40) && $B1772eb944c03052cd5d180cdee51b89 <= 20)) {
                                                                                                        if (!($B1772eb944c03052cd5d180cdee51b89 == 20)) {
                                                                                                            if (empty(a78bf8d35765BE2408C50712CE7A43ad::$request["segment"])) {
                                                                                                                $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT activity_id,hls_end FROM `user_activity_now` WHERE `user_id` = '%d' AND `server_id` = '%d' AND `container` = 'hls' AND `user_ip` = '%s' AND `user_agent` = '%s' AND `stream_id` = '%d'", $a8df9f055e91a1e9240230b69af85555["id"], SERVER_ID, $f4889efa84e1f2e30e5e9780973f68cb, $userAgent, $ba85d77d367dcebfcc2a3db9e83bb581);
                                                                                                                if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() == 0) {
                                                                                                                    if (!($a8df9f055e91a1e9240230b69af85555["max_connections"] != 0)) {
                                                                                                                        goto E342970ee8061f4d1a87d4316c90c6fe;
                                                                                                                    }
                                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `user_activity_now` SET `hls_end` = 1 WHERE `user_id` = '%d' AND `container` = 'hls'", $a8df9f055e91a1e9240230b69af85555["id"]);
                                                                                                                    E342970ee8061f4d1a87d4316c90c6fe:
                                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `user_activity_now` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s','%d')", $a8df9f055e91a1e9240230b69af85555["id"], $ba85d77d367dcebfcc2a3db9e83bb581, SERVER_ID, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, "hls", getmypid(), $a7e968a4f6d75092e74cdeb1b406041a, $A75f2436a5614184bfe3442ddd050ec5, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95, time());
                                                                                                                    $E821605d1d9382d422040b86d29632d9 = $f566700a43ee8e1f0412fe10fbdf03df->getLastInsertedId();
                                                                                                                    goto fe1ec2bc8c62ceae9fe6f8e0d06d6208;
                                                                                                                }
                                                                                                                $c72d66b481d02f854f0bef67db92a547 = $f566700a43ee8e1f0412fe10fbdf03df->f1eD191D78470660eDFf4A007696BC1f();
                                                                                                                if (!($c72d66b481d02f854f0bef67db92a547["hls_end"] == 1)) {
                                                                                                                    $E821605d1d9382d422040b86d29632d9 = $c72d66b481d02f854f0bef67db92a547["activity_id"];
                                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `user_activity_now` SET `hls_last_read` = '%d' WHERE `activity_id` = '%d'", time(), $c72d66b481d02f854f0bef67db92a547["activity_id"]);
                                                                                                                    fe1ec2bc8c62ceae9fe6f8e0d06d6208:
                                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->CA531f7BdC43B966deFb4abA3c8FAF22();
                                                                                                                    if (!($F3803fa85b38b65447e6d438f8e9176a = Cd89785224751cca8017139Daf9e891e::E7917F7F55606c448105A9a4016538b9($Bb37b848bec813a5c13ea0b018962c40, $f6806488699d3315dc5dc1e27a401b3e, $password, $ba85d77d367dcebfcc2a3db9e83bb581))) {
                                                                                                                        goto B2acecf827fcdc79dec47310a788b901;
                                                                                                                    }
                                                                                                                    header("Content-Type: application/x-mpegurl");
                                                                                                                    header("Content-Length: " . strlen($F3803fa85b38b65447e6d438f8e9176a));
                                                                                                                    header("Cache-Control: no-store, no-cache, must-revalidate");
                                                                                                                    echo $F3803fa85b38b65447e6d438f8e9176a;
                                                                                                                    B2acecf827fcdc79dec47310a788b901:
                                                                                                                    die;
                                                                                                                }
                                                                                                                header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                                                                                                                die;
                                                                                                            }
                                                                                                            $f566700a43ee8e1f0412fe10fbdf03df->ca531f7bDc43b966dEfB4ABa3c8FAf22();
                                                                                                            $fe9d0d199fc51f64065055d8bcade279 = STREAMS_PATH . str_replace(array("\\", "/"), '', urldecode(A78Bf8D35765BE2408c50712ce7a43AD::$request["segment"]));
                                                                                                            $ff808659f878dbd58bfa6fabe039f10c = explode("_", basename($fe9d0d199fc51f64065055d8bcade279));
                                                                                                            if (!(!file_exists($fe9d0d199fc51f64065055d8bcade279) || $ff808659f878dbd58bfa6fabe039f10c[0] != $ba85d77d367dcebfcc2a3db9e83bb581 || empty(A78BF8D35765be2408C50712ce7A43ad::$request["token"]))) {
                                                                                                                $accessToken = a78bf8D35765Be2408C50712ce7A43aD::$request["token"];
                                                                                                                $A0450eaeae72ee603999aa268ea82b0c = md5(urldecode(A78Bf8d35765Be2408c50712CE7a43AD::$request["segment"]) . $a8df9f055e91a1e9240230b69af85555["username"] . A78BF8d35765Be2408C50712Ce7a43aD::$settings["crypt_load_balancing"] . filesize($fe9d0d199fc51f64065055d8bcade279));
                                                                                                                if (!($A0450eaeae72ee603999aa268ea82b0c != $accessToken)) {
                                                                                                                    $e13ac89e162bcc9913e553b949f755b6 = filesize($fe9d0d199fc51f64065055d8bcade279);
                                                                                                                    header("Content-Length: " . $e13ac89e162bcc9913e553b949f755b6);
                                                                                                                    header("Content-Type: video/mp2t");
                                                                                                                    readfile($fe9d0d199fc51f64065055d8bcade279);
                                                                                                                    goto a1a191cea5b5ee867ae84b6dda4fbdb2;
                                                                                                                }
                                                                                                                header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                                                                                                                die;
                                                                                                            }
                                                                                                            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                                                                                                            die;
                                                                                                        }
                                                                                                        die;
                                                                                                    }
                                                                                                    usleep(500000);
                                                                                                    ++$B1772eb944c03052cd5d180cdee51b89;
                                                                                                    goto a5783fd272d37bf2cf23d06cadf2c0b5;
                                                                                                default:
                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `user_activity_now` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s')", $a8df9f055e91a1e9240230b69af85555["id"], $ba85d77d367dcebfcc2a3db9e83bb581, SERVER_ID, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $F1350a5569e4b73d2f9cb26483f2a0c1, getmypid(), $a7e968a4f6d75092e74cdeb1b406041a, $A75f2436a5614184bfe3442ddd050ec5, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95);
                                                                                                    $E821605d1d9382d422040b86d29632d9 = $f566700a43ee8e1f0412fe10fbdf03df->getLastInsertedId();
                                                                                                    $Cec7b3525ded5578bb4eaefe5020eb98 = TMP_DIR . $E821605d1d9382d422040b86d29632d9 . ".con";
                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->Ca531F7BDc43b966dEfb4aBA3C8FAF22();
                                                                                                    header("Content-Type: video/mp2t");
                                                                                                    $C325d28e238c3a646bd7b095aa1ffa85 = cd89785224751cca8017139daF9e891e::B8430212Cc8301200a4976571DbA202C($Bb37b848bec813a5c13ea0b018962c40, A78BF8d35765be2408C50712CE7a43Ad::$settings["client_prebuffer"]);
                                                                                                    if (!empty($C325d28e238c3a646bd7b095aa1ffa85)) {
                                                                                                        if (is_array($C325d28e238c3a646bd7b095aa1ffa85)) {
                                                                                                            if (!(A78BF8d35765BE2408C50712cE7a43ad::$settings["restreamer_prebuffer"] == 1 && $a8df9f055e91a1e9240230b69af85555["is_restreamer"] == 1 || $a8df9f055e91a1e9240230b69af85555["is_restreamer"] == 0)) {
                                                                                                                goto ad65a7596777f3252e1e3748791b1431;
                                                                                                            }
                                                                                                            $e13ac89e162bcc9913e553b949f755b6 = 0;
                                                                                                            $A73d5129dfb465fd94f3e09e9b179de0 = time();
                                                                                                            foreach ($C325d28e238c3a646bd7b095aa1ffa85 as $fe9d0d199fc51f64065055d8bcade279) {
                                                                                                                if (file_exists(STREAMS_PATH . $fe9d0d199fc51f64065055d8bcade279)) {
                                                                                                                    $e13ac89e162bcc9913e553b949f755b6 += readfile(STREAMS_PATH . $fe9d0d199fc51f64065055d8bcade279);
                                                                                                                    c9f5ed7627986dfb1f1107f442308454:
                                                                                                                }
                                                                                                                die;
                                                                                                            }
                                                                                                            $D6db7e73f7da5e54d965f7ef1c369bd6 = time() - $A73d5129dfb465fd94f3e09e9b179de0;
                                                                                                            if (!($D6db7e73f7da5e54d965f7ef1c369bd6 == 0)) {
                                                                                                                goto Ebf15b0d0d17ce1ef777616d5204c03f;
                                                                                                            }
                                                                                                            $D6db7e73f7da5e54d965f7ef1c369bd6 = 0.1;
                                                                                                            Ebf15b0d0d17ce1ef777616d5204c03f:
                                                                                                            file_put_contents($Cec7b3525ded5578bb4eaefe5020eb98, intval($e13ac89e162bcc9913e553b949f755b6 / $D6db7e73f7da5e54d965f7ef1c369bd6 / 1024));
                                                                                                            ad65a7596777f3252e1e3748791b1431:
                                                                                                            preg_match("/_(.*)\\./", array_pop($C325d28e238c3a646bd7b095aa1ffa85), $adb24597b0e7956b0f3baad7c260916d);
                                                                                                            $E76c20c612d64210f5bcc0611992d2f7 = $adb24597b0e7956b0f3baad7c260916d[1];
                                                                                                            goto B3376bd613b7c7b3e2e13b2dba740e4f;
                                                                                                        }
                                                                                                        $E76c20c612d64210f5bcc0611992d2f7 = $C325d28e238c3a646bd7b095aa1ffa85;
                                                                                                        B3376bd613b7c7b3e2e13b2dba740e4f:
                                                                                                        goto f4a60f5a64a086fc0304bf38dd04c18d;
                                                                                                    }
                                                                                                    if (!file_exists($Bb37b848bec813a5c13ea0b018962c40)) {
                                                                                                        $E76c20c612d64210f5bcc0611992d2f7 = -1;
                                                                                                        f4a60f5a64a086fc0304bf38dd04c18d:
                                                                                                        $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                                                                                        $f065eccc0636f7fd92043c7118f7409b = a78BF8D35765BE2408C50712CE7a43Ad::$SegmentsSettings["seg_time"] * 2;
                                                                                                        ec83cd6ff50c6b79e6b8cffbb78eecbf:
                                                                                                        if (!true) {
                                                                                                        }
                                                                                                        $c5f97e03cbf94a57a805526a8288042f = sprintf("%d_%d.ts", $ffb1e0970b62b01f46c2e57f2cded6c2["stream_id"], $E76c20c612d64210f5bcc0611992d2f7 + 1);
                                                                                                        $Bf3da9b14ae368d39b642b3f83d656fc = sprintf("%d_%d.ts", $ffb1e0970b62b01f46c2e57f2cded6c2["stream_id"], $E76c20c612d64210f5bcc0611992d2f7 + 2);
                                                                                                        $a88c8d86d7956601164a5f156d5df985 = 0;
                                                                                                        Cf93be3ee45266203c1bef9fbf92206a:
                                                                                                        if (!(!file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f) && $a88c8d86d7956601164a5f156d5df985 <= $f065eccc0636f7fd92043c7118f7409b * 10)) {
                                                                                                            if (file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f)) {
                                                                                                                if (!(empty($ffb1e0970b62b01f46c2e57f2cded6c2["pid"]) && file_exists(STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.pid"))) {
                                                                                                                    goto ad53cf2275793650541bcbe2fded0fd6;
                                                                                                                }
                                                                                                                $ffb1e0970b62b01f46c2e57f2cded6c2["pid"] = intval(file_get_contents(STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.pid"));
                                                                                                                ad53cf2275793650541bcbe2fded0fd6:
                                                                                                                if (!file_exists(SIGNALS_PATH . $E821605d1d9382d422040b86d29632d9)) {
                                                                                                                    $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                                                                                                    $c41986ad785eace90882e61c64cabb41 = time();
                                                                                                                    $Ab9f45b38498c3a010f3c4276ad5767c = fopen(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f, "r");
                                                                                                                    Cec1b4b5d1ec19950895bdff075c35b9:
                                                                                                                    if (!($c45cc215a073632a9e20d474ea91f7e3 <= $f065eccc0636f7fd92043c7118f7409b && !file_exists(STREAMS_PATH . $Bf3da9b14ae368d39b642b3f83d656fc))) {
                                                                                                                        goto ef0705fe07490d2e2ab41bcda87af246;
                                                                                                                    }
                                                                                                                    $d76067cf9572f7a6691c85c12faf2a29 = stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, A78bf8d35765Be2408c50712ce7a43aD::$settings["read_buffer_size"]);
                                                                                                                    if (!empty($d76067cf9572f7a6691c85c12faf2a29)) {
                                                                                                                        echo $d76067cf9572f7a6691c85c12faf2a29;
                                                                                                                        $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                                                                                                        goto Cec1b4b5d1ec19950895bdff075c35b9;
                                                                                                                    }
                                                                                                                    if (Cd89785224751CcA8017139daF9e891e::ps_running($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], FFMPEG_PATH)) {
                                                                                                                        sleep(1);
                                                                                                                        ++$c45cc215a073632a9e20d474ea91f7e3;
                                                                                                                        goto Cec1b4b5d1ec19950895bdff075c35b9;
                                                                                                                    }
                                                                                                                    ef0705fe07490d2e2ab41bcda87af246:
                                                                                                                    if (CD89785224751cca8017139dAF9e891e::ps_running($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], FFMPEG_PATH) && $c45cc215a073632a9e20d474ea91f7e3 <= $f065eccc0636f7fd92043c7118f7409b && file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f) && is_resource($Ab9f45b38498c3a010f3c4276ad5767c)) {
                                                                                                                        $F19b64ffad55876d290cb6f756a2dea5 = filesize(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f);
                                                                                                                        $C73fe796a6baad7ca2e4251886562ef0 = $F19b64ffad55876d290cb6f756a2dea5 - ftell($Ab9f45b38498c3a010f3c4276ad5767c);
                                                                                                                        if (!($C73fe796a6baad7ca2e4251886562ef0 > 0)) {
                                                                                                                            goto Ce918f8fde55dedaced62b39f0728c56;
                                                                                                                        }
                                                                                                                        echo stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, $C73fe796a6baad7ca2e4251886562ef0);
                                                                                                                        Ce918f8fde55dedaced62b39f0728c56:
                                                                                                                        $D6db7e73f7da5e54d965f7ef1c369bd6 = time() - $c41986ad785eace90882e61c64cabb41;
                                                                                                                        if (!($D6db7e73f7da5e54d965f7ef1c369bd6 <= 0)) {
                                                                                                                            goto E2dc5f6f12f0224820bb5d48bc17b5db;
                                                                                                                        }
                                                                                                                        $D6db7e73f7da5e54d965f7ef1c369bd6 = 0.1;
                                                                                                                        E2dc5f6f12f0224820bb5d48bc17b5db:
                                                                                                                        file_put_contents($Cec7b3525ded5578bb4eaefe5020eb98, intval($F19b64ffad55876d290cb6f756a2dea5 / 1024 / $D6db7e73f7da5e54d965f7ef1c369bd6));
                                                                                                                        goto A68eb1112c91909740ce1cfbc1a210ce;
                                                                                                                    }
                                                                                                                    if (!($a8df9f055e91a1e9240230b69af85555["is_restreamer"] == 1 || $c45cc215a073632a9e20d474ea91f7e3 > $f065eccc0636f7fd92043c7118f7409b)) {
                                                                                                                        $a88c8d86d7956601164a5f156d5df985 = 0;
                                                                                                                        F71d17aeef5dd4b69cc7d2e4bdabbeba:
                                                                                                                        if (!($a88c8d86d7956601164a5f156d5df985 <= a78bf8d35765Be2408C50712cE7A43Ad::$SegmentsSettings["seg_time"] && !cd89785224751CCa8017139daf9E891E::BcAa9B8a7b46Eb36cD507A218fA64474($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], $ba85d77d367dcebfcc2a3db9e83bb581))) {
                                                                                                                            if (!($a88c8d86d7956601164a5f156d5df985 > A78bf8D35765be2408c50712cE7A43ad::$SegmentsSettings["seg_time"] || !cd89785224751CcA8017139DAf9e891E::BCaa9B8A7B46Eb36CD507A218Fa64474($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], $ba85d77d367dcebfcc2a3db9e83bb581))) {
                                                                                                                                $E76c20c612d64210f5bcc0611992d2f7 = -2;
                                                                                                                                A68eb1112c91909740ce1cfbc1a210ce:
                                                                                                                                fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                                                                                                                                $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                                                                                                                $E76c20c612d64210f5bcc0611992d2f7++;
                                                                                                                                goto ec83cd6ff50c6b79e6b8cffbb78eecbf;
                                                                                                                            }
                                                                                                                            die;
                                                                                                                        }
                                                                                                                        sleep(1);
                                                                                                                        if (!file_exists(STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.pid")) {
                                                                                                                            goto Fd5fcda6b37e5a5e5c144e72bba0f3e7;
                                                                                                                        }
                                                                                                                        $ffb1e0970b62b01f46c2e57f2cded6c2["pid"] = intval(file_get_contents(STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.pid"));
                                                                                                                        Fd5fcda6b37e5a5e5c144e72bba0f3e7:
                                                                                                                        ++$a88c8d86d7956601164a5f156d5df985;
                                                                                                                        goto F71d17aeef5dd4b69cc7d2e4bdabbeba;
                                                                                                                    }
                                                                                                                    die;
                                                                                                                }
                                                                                                                $d38a1c3d822bdbbd61f649f33212ebde = json_decode(file_get_contents(SIGNALS_PATH . $E821605d1d9382d422040b86d29632d9), true);
                                                                                                                switch ($d38a1c3d822bdbbd61f649f33212ebde["type"]) {
                                                                                                                    case "signal":
                                                                                                                        $a88c8d86d7956601164a5f156d5df985 = 0;
                                                                                                                        bebebcdc24b95d7496a99323abc492f0:
                                                                                                                        if (!(!file_exists(STREAMS_PATH . $Bf3da9b14ae368d39b642b3f83d656fc) && $a88c8d86d7956601164a5f156d5df985 <= $f065eccc0636f7fd92043c7118f7409b)) {
                                                                                                                            CD89785224751cca8017139dAf9E891e::e8E54De10433eB446982a4af8aDeA379($d38a1c3d822bdbbd61f649f33212ebde, $c5f97e03cbf94a57a805526a8288042f);
                                                                                                                            ++$E76c20c612d64210f5bcc0611992d2f7;
                                                                                                                            goto f1e4c23da8d982d6100119195ce48da9;
                                                                                                                        }
                                                                                                                        sleep(1);
                                                                                                                        ++$a88c8d86d7956601164a5f156d5df985;
                                                                                                                        goto bebebcdc24b95d7496a99323abc492f0;
                                                                                                                    case "redirect":
                                                                                                                        $ba85d77d367dcebfcc2a3db9e83bb581 = $ffb1e0970b62b01f46c2e57f2cded6c2["stream_id"] = $d38a1c3d822bdbbd61f649f33212ebde["stream_id"];
                                                                                                                        $Bb37b848bec813a5c13ea0b018962c40 = STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.m3u8";
                                                                                                                        $ffb1e0970b62b01f46c2e57f2cded6c2["pid"] = null;
                                                                                                                        $C325d28e238c3a646bd7b095aa1ffa85 = cd89785224751cca8017139dAF9e891e::B8430212cC8301200A4976571Dba202C($Bb37b848bec813a5c13ea0b018962c40, A78Bf8d35765Be2408c50712cE7a43Ad::$settings["client_prebuffer"]);
                                                                                                                        preg_match("/_(.*)\\./", array_pop($C325d28e238c3a646bd7b095aa1ffa85), $adb24597b0e7956b0f3baad7c260916d);
                                                                                                                        $E76c20c612d64210f5bcc0611992d2f7 = $adb24597b0e7956b0f3baad7c260916d[1];
                                                                                                                        goto f1e4c23da8d982d6100119195ce48da9;
                                                                                                                }
                                                                                                                f1e4c23da8d982d6100119195ce48da9:
                                                                                                                $d38a1c3d822bdbbd61f649f33212ebde = null;
                                                                                                                unlink(SIGNALS_PATH . $E821605d1d9382d422040b86d29632d9);
                                                                                                                goto ec83cd6ff50c6b79e6b8cffbb78eecbf;
                                                                                                            }
                                                                                                            die;
                                                                                                        }
                                                                                                        usleep(100000);
                                                                                                        ++$a88c8d86d7956601164a5f156d5df985;
                                                                                                        goto Cf93be3ee45266203c1bef9fbf92206a;
                                                                                                    }
                                                                                                    die;
                                                                                            }
                                                                                            a1a191cea5b5ee867ae84b6dda4fbdb2:
                                                                                            goto E7de94241f1efed6db3bb965612215a4;
                                                                                        }
                                                                                        Cd89785224751cca8017139daf9E891E::D0b968Cd6CfDF340CA85B1C3d9A40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_not_on_air_video", "not_on_air_video_path", $F1350a5569e4b73d2f9cb26483f2a0c1);
                                                                                        E7de94241f1efed6db3bb965612215a4:
                                                                                        goto b732adb9a9a32a5395067cd139acbc17;
                                                                                    }
                                                                                    http_response_code(401);
                                                                                    cd89785224751CCA8017139DaF9e891e::c1591643eAFDaae33FF6e69e5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "CON_SVP", $f4889efa84e1f2e30e5e9780973f68cb, json_encode(array("user_agent" => $userAgent, "isp" => $a8df9f055e91a1e9240230b69af85555["con_isp_name"], "type" => $a8df9f055e91a1e9240230b69af85555["con_isp_type"])), true);
                                                                                    die;
                                                                                }
                                                                                http_response_code(401);
                                                                                cD89785224751cCa8017139DAf9e891e::C1591643EAfDAAE33Ff6e69e5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "ISP_LOCK_FAILED", $f4889efa84e1f2e30e5e9780973f68cb, json_encode(array("old" => $a8df9f055e91a1e9240230b69af85555["isp_desc"], "new" => $a8df9f055e91a1e9240230b69af85555["con_isp_name"])));
                                                                                die;
                                                                            }
                                                                            http_response_code(406);
                                                                            cd89785224751Cca8017139dAf9E891e::c1591643EaFdaAe33Ff6E69e5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "NOT_IN_BOUQUET", $f4889efa84e1f2e30e5e9780973f68cb);
                                                                            die;
                                                                        }
                                                                        http_response_code(405);
                                                                        cD89785224751CCa8017139DAf9e891E::C1591643eAFDAae33Ff6e69e5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_DISALLOW_EXT", $f4889efa84e1f2e30e5e9780973f68cb);
                                                                        die;
                                                                    }
                                                                    cd89785224751CcA8017139dAF9e891e::c1591643eAFdaAe33FF6e69e5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_ALREADY_CONNECTED", $f4889efa84e1f2e30e5e9780973f68cb);
                                                                    die;
                                                                }
                                                                Cd89785224751CcA8017139dAf9E891e::C1591643eaFDAae33Ff6e69E5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "CRACKED", $f4889efa84e1f2e30e5e9780973f68cb);
                                                                die;
                                                            }
                                                            cD89785224751cCa8017139DAf9e891e::c1591643EAFdAaE33ff6e69E5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_AGENT_BAN", $f4889efa84e1f2e30e5e9780973f68cb);
                                                            die;
                                                        }
                                                        cD89785224751cCA8017139DAf9e891E::c1591643eAFdaAE33FF6E69E5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "COUNTRY_DISALLOW", $f4889efa84e1f2e30e5e9780973f68cb);
                                                        die;
                                                    }
                                                    Cd89785224751CcA8017139DAf9E891e::c1591643EAfdaAE33Ff6e69E5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "COUNTRY_DISALLOW", $f4889efa84e1f2e30e5e9780973f68cb);
                                                    die;
                                                }
                                                CD89785224751ccA8017139daF9E891E::c1591643EaFdaAe33FF6e69E5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "IP_BAN", $f4889efa84e1f2e30e5e9780973f68cb);
                                                die;
                                            }
                                            CD89785224751Cca8017139Daf9E891E::c1591643EAfdAae33Ff6e69E5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "EMPTY_UA", $f4889efa84e1f2e30e5e9780973f68cb);
                                            die;
                                        }
                                        cD89785224751CCa8017139DAf9E891E::C1591643eaFdaAe33Ff6E69e5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_DISABLED", $f4889efa84e1f2e30e5e9780973f68cb);
                                        Cd89785224751CcA8017139DaF9e891E::D0b968CD6CFDf340CA85B1c3D9A40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_banned_video", "banned_video_path", $F1350a5569e4b73d2f9cb26483f2a0c1);
                                        die;
                                    }
                                    CD89785224751cca8017139Daf9e891e::c1591643eaFDAaE33Ff6E69e5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_BAN", $f4889efa84e1f2e30e5e9780973f68cb);
                                    cD89785224751CCA8017139Daf9E891E::d0b968Cd6CFDf340cA85B1C3d9A40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_banned_video", "banned_video_path", $F1350a5569e4b73d2f9cb26483f2a0c1);
                                    die;
                                }
                                cd89785224751CCa8017139daF9E891e::C1591643EaFDAAe33fF6e69e5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_EXPIRED", $f4889efa84e1f2e30e5e9780973f68cb);
                                CD89785224751CcA8017139DAF9e891E::d0B968Cd6cfDF340Ca85b1C3d9A40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_expired_video", "expired_video_path", $F1350a5569e4b73d2f9cb26483f2a0c1);
                                die;
                            }
                            cd89785224751ccA8017139dAF9e891E::C1591643EaFDaaE33fF6e69e5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "STALKER_KEY_EXPIRED", $f4889efa84e1f2e30e5e9780973f68cb);
                            die;
                        }
                        cD89785224751cCa8017139DAf9e891E::C1591643eAfDaae33Ff6e69e5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "STALKER_IP_MISMATCH", $f4889efa84e1f2e30e5e9780973f68cb);
                        die;
                    }
                    cD89785224751cCA8017139DAF9E891e::c1591643EAFDaae33fF6E69E5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "STALKER_CHANNEL_MISMATCH", $f4889efa84e1f2e30e5e9780973f68cb);
                    die;
                }
                Cd89785224751ccA8017139daf9E891e::C1591643eAFdAaE33FF6E69e5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "STALKER_DECRYPT_FAILED", $f4889efa84e1f2e30e5e9780973f68cb);
                die;
            }
            die;
        }
        CD89785224751CCA8017139DAF9e891e::c1591643eafDAAe33FF6E69e5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "MAG_TOKEN_INVALID", $f4889efa84e1f2e30e5e9780973f68cb);
        die;
    }
    cD89785224751ccA8017139daF9E891e::C1591643eAFdAAE33ff6E69E5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, 0, "AUTH_FAILED", $f4889efa84e1f2e30e5e9780973f68cb);
    b732adb9a9a32a5395067cd139acbc17:
    function shutdown() {
        global $f566700a43ee8e1f0412fe10fbdf03df, $E821605d1d9382d422040b86d29632d9, $e84deaa90130ae0163381d3f216773e3, $Cec7b3525ded5578bb4eaefe5020eb98, $a8df9f055e91a1e9240230b69af85555, $F1350a5569e4b73d2f9cb26483f2a0c1, $f0ac6ad2b40669833242a10c23cad2e0, $ba85d77d367dcebfcc2a3db9e83bb581, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $A75f2436a5614184bfe3442ddd050ec5, $a349f0750f0a814bd31ec4b3da51da95, $a7e968a4f6d75092e74cdeb1b406041a;
        if (!$f0ac6ad2b40669833242a10c23cad2e0) {
            goto A1903fc2c8f726a409514491318875b7;
        }
        D9F93B7c177E377D0bBFe315eaEae505();
        http_response_code(401);
        A1903fc2c8f726a409514491318875b7:
        $f566700a43ee8e1f0412fe10fbdf03df->cA531F7BDC43b966DEfB4ABA3c8Faf22();
        if (!($E821605d1d9382d422040b86d29632d9 != 0 && $e84deaa90130ae0163381d3f216773e3)) {
            goto Eced89b5228c7195ad96478575e9b90a;
        }
        Cd89785224751cca8017139DAF9e891e::E990445B40642e0EfD070E994375f6af($E821605d1d9382d422040b86d29632d9);
        CD89785224751cca8017139daf9e891e::A49C2fb1EbA096c52a352A85C8d09d8D(SERVER_ID, $a8df9f055e91a1e9240230b69af85555["id"], $ba85d77d367dcebfcc2a3db9e83bb581, $a7e968a4f6d75092e74cdeb1b406041a, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $F1350a5569e4b73d2f9cb26483f2a0c1, $A75f2436a5614184bfe3442ddd050ec5, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95);
        if (!file_exists($Cec7b3525ded5578bb4eaefe5020eb98)) {
            goto f2190e18b284c352d7108f14410b94dd;
        }
        unlink($Cec7b3525ded5578bb4eaefe5020eb98);
        f2190e18b284c352d7108f14410b94dd:
        Eced89b5228c7195ad96478575e9b90a:
        fastcgi_finish_request();
        if (!($E821605d1d9382d422040b86d29632d9 != 0 || !file_exists("IPTV_PANEL_DIRkill_pids"))) {
            goto c9c588386987168608264266a2675545;
        }
        posix_kill(getmypid(), 9);
        c9c588386987168608264266a2675545:
    }
    // [PHPDeobfuscator] Implied script end
    return;
}
die;
