<?php

register_shutdown_function("shutdown");
set_time_limit(0);
require "../init.php";
header("Access-Control-Allow-Origin: *");
$f0ac6ad2b40669833242a10c23cad2e0 = true;
if (!(!isset(A78BF8D35765be2408C50712cE7a43AD::$request["start"]) || !isset(A78Bf8d35765bE2408C50712cE7A43AD::$request["duration"]) || !isset(a78bf8d35765BE2408c50712cE7a43aD::$request["stream"]))) {
    if (!(A78bF8D35765Be2408C50712CE7A43AD::$settings["use_buffer"] == 0)) {
        goto Eef81c2b6ede39c1fc313a5759150ca2;
    }
    header("X-Accel-Buffering: no");
    Eef81c2b6ede39c1fc313a5759150ca2:
    $ded15b7e9c47ec5a3dea3c69332153c8 = new Ea991ba3eC74f0Fb90ACC94C2d2DE518(GEOIP2_FILENAME);
    $E821605d1d9382d422040b86d29632d9 = 0;
    $Cec7b3525ded5578bb4eaefe5020eb98 = null;
    $E2e6656d8b1675f70c487f89e4f27a3b = null;
    $f6806488699d3315dc5dc1e27a401b3e = empty(A78BF8D35765Be2408c50712CE7a43ad::$request["username"]) ? '' : a78bf8d35765BE2408C50712Ce7a43Ad::$request["username"];
    $password = empty(a78bF8D35765Be2408C50712cE7a43aD::$request["password"]) ? '' : a78bf8d35765bE2408c50712ce7A43ad::$request["password"];
    $ba85d77d367dcebfcc2a3db9e83bb581 = a78bf8d35765Be2408C50712ce7A43aD::$request["stream"];
    $F19b64ffad55876d290cb6f756a2dea5 = 0;
    if (!(!is_numeric($ba85d77d367dcebfcc2a3db9e83bb581) && stristr($ba85d77d367dcebfcc2a3db9e83bb581, "_"))) {
        goto b8deae194cc93438fc06dbdef18bf297;
    }
    list($ba85d77d367dcebfcc2a3db9e83bb581, $Fe917966573bdf0b43ab9723bb50fc6b, $F19b64ffad55876d290cb6f756a2dea5) = explode("_", $ba85d77d367dcebfcc2a3db9e83bb581);
    $ba85d77d367dcebfcc2a3db9e83bb581 = intval($ba85d77d367dcebfcc2a3db9e83bb581);
    $F919000263e8ad8e2791f92d8919f629 = intval($Fe917966573bdf0b43ab9723bb50fc6b);
    $F19b64ffad55876d290cb6f756a2dea5 = intval($F19b64ffad55876d290cb6f756a2dea5);
    a78bF8D35765bE2408C50712cE7a43Ad::$request["extension"] = "m3u8";
    b8deae194cc93438fc06dbdef18bf297:
    $f4889efa84e1f2e30e5e9780973f68cb = CD89785224751CcA8017139dAf9E891E::E1F75A50F74a8f4E2129BA474F45d670();
    $userAgent = empty($_SERVER["HTTP_USER_AGENT"]) ? '' : htmlentities(trim($_SERVER["HTTP_USER_AGENT"]));
    $A75f2436a5614184bfe3442ddd050ec5 = $ded15b7e9c47ec5a3dea3c69332153c8->c6a76952B4CEf18f3c98c0e6A9DD1274($f4889efa84e1f2e30e5e9780973f68cb)["registered_country"]["iso_code"];
    $ded15b7e9c47ec5a3dea3c69332153c8->close();
    $Cd2953f76721ad9589ab3d88c42b62b9 = empty(A78Bf8d35765BE2408c50712cE7a43ad::$request["play_token"]) ? null : A78bF8d35765Be2408c50712ce7A43Ad::$request["play_token"];
    if ($a8df9f055e91a1e9240230b69af85555 = CD89785224751ccA8017139Daf9E891e::e5550592AA298dD1d5ee59cdcE063a12(null, $f6806488699d3315dc5dc1e27a401b3e, $password, true, false, true, array(), false, $f4889efa84e1f2e30e5e9780973f68cb, $userAgent, array(), $Cd2953f76721ad9589ab3d88c42b62b9, $ba85d77d367dcebfcc2a3db9e83bb581)) {
        if (!isset($a8df9f055e91a1e9240230b69af85555["mag_invalid_token"])) {
            if (!(!is_null($a8df9f055e91a1e9240230b69af85555["exp_date"]) && time() >= $a8df9f055e91a1e9240230b69af85555["exp_date"])) {
                if (!($a8df9f055e91a1e9240230b69af85555["admin_enabled"] == 0)) {
                    if (!($a8df9f055e91a1e9240230b69af85555["enabled"] == 0)) {
                        if (!(empty($userAgent) && a78Bf8d35765Be2408c50712Ce7a43aD::$settings["disallow_empty_user_agents"] == 1)) {
                            if (!(!empty($a8df9f055e91a1e9240230b69af85555["allowed_ips"]) && !in_array($f4889efa84e1f2e30e5e9780973f68cb, array_map("gethostbyname", $a8df9f055e91a1e9240230b69af85555["allowed_ips"])))) {
                                if (empty($A75f2436a5614184bfe3442ddd050ec5)) {
                                    goto B6ee822198ecad0103603592ec0cb800;
                                }
                                $ab59908f6050f752836a953eb8bb8e52 = !empty($a8df9f055e91a1e9240230b69af85555["forced_country"]) ? true : false;
                                if (!($ab59908f6050f752836a953eb8bb8e52 && $a8df9f055e91a1e9240230b69af85555["forced_country"] != "ALL" && $A75f2436a5614184bfe3442ddd050ec5 != $a8df9f055e91a1e9240230b69af85555["forced_country"])) {
                                    if (!(!$ab59908f6050f752836a953eb8bb8e52 && !in_array("ALL", a78BF8D35765BE2408C50712Ce7a43AD::$settings["allow_countries"]) && !in_array($A75f2436a5614184bfe3442ddd050ec5, a78bF8D35765Be2408C50712Ce7a43aD::$settings["allow_countries"]))) {
                                        B6ee822198ecad0103603592ec0cb800:
                                        if (!(!empty($a8df9f055e91a1e9240230b69af85555["allowed_ua"]) && !in_array($userAgent, $a8df9f055e91a1e9240230b69af85555["allowed_ua"]))) {
                                            if (!cd89785224751cca8017139DAf9e891E::C57799E5196664CB99139813250673e2($f4889efa84e1f2e30e5e9780973f68cb)) {
                                                if (!isset($a8df9f055e91a1e9240230b69af85555["ip_limit_reached"])) {
                                                    $f0ac6ad2b40669833242a10c23cad2e0 = false;
                                                    if (in_array($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["channel_ids"])) {
                                                        if (!($a8df9f055e91a1e9240230b69af85555["max_connections"] != 0)) {
                                                            goto A341b0cf56b28817b64fcf5dbcf591fc;
                                                        }
                                                        if (empty($a8df9f055e91a1e9240230b69af85555["pair_line_info"])) {
                                                            goto f8bcdb4fc0bae6a148d7adc705925076;
                                                        }
                                                        if (!($a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"] != 0)) {
                                                            goto d1f747318cf6eae5f2c863150e6187dd;
                                                        }
                                                        if (!($a8df9f055e91a1e9240230b69af85555["pair_line_info"]["active_cons"] >= $a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"])) {
                                                            goto B0b2aa013b0d1c6e20b655395abd4aa9;
                                                        }
                                                        cD89785224751cca8017139DAF9E891E::a813cbd1e7ea2Bb17742dE7BB2392EBf($a8df9f055e91a1e9240230b69af85555["pair_id"], $a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"]);
                                                        B0b2aa013b0d1c6e20b655395abd4aa9:
                                                        d1f747318cf6eae5f2c863150e6187dd:
                                                        f8bcdb4fc0bae6a148d7adc705925076:
                                                        if (!($a8df9f055e91a1e9240230b69af85555["active_cons"] >= $a8df9f055e91a1e9240230b69af85555["max_connections"])) {
                                                            goto C9fb0dbf561dbfac4b7498599bd22584;
                                                        }
                                                        CD89785224751CCa8017139dAf9e891E::a813CBd1e7ea2bb17742DE7Bb2392eBF($a8df9f055e91a1e9240230b69af85555["id"], $a8df9f055e91a1e9240230b69af85555["max_connections"]);
                                                        C9fb0dbf561dbfac4b7498599bd22584:
                                                        A341b0cf56b28817b64fcf5dbcf591fc:
                                                        if (!($a8df9f055e91a1e9240230b69af85555["isp_violate"] == 1)) {
                                                            if (!($a8df9f055e91a1e9240230b69af85555["isp_is_server"] == 1)) {
                                                                be054dbfe2bb02917e4f5ade7915f5ae:
                                                                $ffb1e0970b62b01f46c2e57f2cded6c2 = cD89785224751cCa8017139DAf9e891E::F3c105BCCEd491229D4Aed6937F96A8C($ba85d77d367dcebfcc2a3db9e83bb581, "ts", $a8df9f055e91a1e9240230b69af85555, $f4889efa84e1f2e30e5e9780973f68cb, $A75f2436a5614184bfe3442ddd050ec5, '', $a8df9f055e91a1e9240230b69af85555["con_isp_name"], "archive");
                                                                if (!empty($ffb1e0970b62b01f46c2e57f2cded6c2)) {
                                                                    $Be553c1662ffa5054ccb6c5ce822974b = A78BF8D35765bE2408c50712cE7a43AD::$request["start"];
                                                                    $fd08711a26bab44719872c7fff1f2dfb = intval(A78bf8d35765be2408C50712cE7A43Ad::$request["duration"]);
                                                                    if (!is_numeric($Be553c1662ffa5054ccb6c5ce822974b)) {
                                                                        if (substr_count($Be553c1662ffa5054ccb6c5ce822974b, "-") == 1) {
                                                                            list($e309bb80a71b96ca2c0ff856446be219, $Af218a53429705d6e319475a2185cd90) = explode("-", $Be553c1662ffa5054ccb6c5ce822974b);
                                                                            $Ee43d9ecc9cbf5787673058445cfac70 = substr($e309bb80a71b96ca2c0ff856446be219, 0, 4);
                                                                            $Dee598827978959770188b0749ebd8dd = substr($e309bb80a71b96ca2c0ff856446be219, 4, 2);
                                                                            $b8c55e6036c9c00eccabf835e272cdcb = substr($e309bb80a71b96ca2c0ff856446be219, 6, 2);
                                                                            $Bc1d36e0762a7ca0e7cbaddd76686790 = 0;
                                                                            $Ed62709841469f20fe0f7a17a4268692 = $Af218a53429705d6e319475a2185cd90;
                                                                            goto B4a8c8310c36f4c80696114b2865ede2;
                                                                        }
                                                                        list($e309bb80a71b96ca2c0ff856446be219, $Af218a53429705d6e319475a2185cd90) = explode(":", $Be553c1662ffa5054ccb6c5ce822974b);
                                                                        list($Ee43d9ecc9cbf5787673058445cfac70, $Dee598827978959770188b0749ebd8dd, $b8c55e6036c9c00eccabf835e272cdcb) = explode("-", $e309bb80a71b96ca2c0ff856446be219);
                                                                        list($Ed62709841469f20fe0f7a17a4268692, $Bc1d36e0762a7ca0e7cbaddd76686790) = explode("-", $Af218a53429705d6e319475a2185cd90);
                                                                        B4a8c8310c36f4c80696114b2865ede2:
                                                                        $c4eb261e96f50c6cac5c08c60d657d9c = mktime($Ed62709841469f20fe0f7a17a4268692, $Bc1d36e0762a7ca0e7cbaddd76686790, 0, $Dee598827978959770188b0749ebd8dd, $b8c55e6036c9c00eccabf835e272cdcb, $Ee43d9ecc9cbf5787673058445cfac70);
                                                                        goto Eddc423ecdb922fffa2903544ee2c46b;
                                                                    }
                                                                    $fd08711a26bab44719872c7fff1f2dfb *= 24;
                                                                    $Ba78aa94423804e042a0eb27ba2e39a4 = array_values(array_filter(explode("\n", shell_exec("ls -tr TV_ARCHIVE" . $ba85d77d367dcebfcc2a3db9e83bb581 . " | sed -e 's/\\s\\+/\\n/g'"))));
                                                                    $f9214002d8ab6575c8e959794518358d = $Be553c1662ffa5054ccb6c5ce822974b * 24;
                                                                    if (count($Ba78aa94423804e042a0eb27ba2e39a4) >= $f9214002d8ab6575c8e959794518358d) {
                                                                        $f9214002d8ab6575c8e959794518358d = $Ba78aa94423804e042a0eb27ba2e39a4[count($Ba78aa94423804e042a0eb27ba2e39a4) - $f9214002d8ab6575c8e959794518358d];
                                                                        goto De789cacf5ddf4000b180b1f3587351d;
                                                                    }
                                                                    $f9214002d8ab6575c8e959794518358d = $Ba78aa94423804e042a0eb27ba2e39a4[0];
                                                                    De789cacf5ddf4000b180b1f3587351d:
                                                                    if (preg_match("/(.*)-(.*)-(.*):(.*)\\./", $f9214002d8ab6575c8e959794518358d, $matches)) {
                                                                        $c4eb261e96f50c6cac5c08c60d657d9c = mktime($matches[4], 0, 0, $matches[2], $matches[3], $matches[1]);
                                                                        e305f2f9dbef3c70be6797c38f04daf8:
                                                                        Eddc423ecdb922fffa2903544ee2c46b:
                                                                        $Df55b74833e9468cafb620fe446225a1 = array();
                                                                        $Ca434bcc380e9dbd2a3a588f6c32d84f = TV_ARCHIVE . $ba85d77d367dcebfcc2a3db9e83bb581 . "/" . date("Y-m-d:H-i", $c4eb261e96f50c6cac5c08c60d657d9c) . ".ts";
                                                                        if (!(empty($ba85d77d367dcebfcc2a3db9e83bb581) || empty($c4eb261e96f50c6cac5c08c60d657d9c) || empty($fd08711a26bab44719872c7fff1f2dfb))) {
                                                                            if (!(!file_exists($Ca434bcc380e9dbd2a3a588f6c32d84f) || !is_readable($Ca434bcc380e9dbd2a3a588f6c32d84f))) {
                                                                                $Df55b74833e9468cafb620fe446225a1 = array();
                                                                                $C48e0083a9caa391609a3c645a2ec889 = 0;
                                                                                D5fcf2a72e99ec35092bea70c6000d54:
                                                                                if (!($C48e0083a9caa391609a3c645a2ec889 < $fd08711a26bab44719872c7fff1f2dfb)) {
                                                                                    if (empty($Df55b74833e9468cafb620fe446225a1)) {
                                                                                        goto e02473341a014647a9e31478796f3412;
                                                                                    }
                                                                                    $a7e968a4f6d75092e74cdeb1b406041a = time();
                                                                                    $E2e6656d8b1675f70c487f89e4f27a3b = "TV Archive";
                                                                                    switch (A78Bf8D35765BE2408c50712cE7A43aD::$request["extension"]) {
                                                                                        case "m3u8":
                                                                                            if (!isset($F919000263e8ad8e2791f92d8919f629)) {
                                                                                                $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT activity_id,hls_end FROM `user_activity_now` WHERE `user_id` = '%d' AND `server_id` = '%d' AND `container` = 'hls' AND `user_ip` = '%s' AND `user_agent` = '%s' AND `stream_id` = '%d'", $a8df9f055e91a1e9240230b69af85555["id"], SERVER_ID, $f4889efa84e1f2e30e5e9780973f68cb, $userAgent, $ba85d77d367dcebfcc2a3db9e83bb581);
                                                                                                if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() == 0) {
                                                                                                    if (!($a8df9f055e91a1e9240230b69af85555["max_connections"] != 0)) {
                                                                                                        goto b0aa36aa901718706e7ec2048d5eb439;
                                                                                                    }
                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `user_activity_now` SET `hls_end` = 1 WHERE `user_id` = '%d' AND `container` = 'hls'", $a8df9f055e91a1e9240230b69af85555["id"]);
                                                                                                    b0aa36aa901718706e7ec2048d5eb439:
                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `user_activity_now` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s','%d')", $a8df9f055e91a1e9240230b69af85555["id"], $ba85d77d367dcebfcc2a3db9e83bb581, SERVER_ID, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $E2e6656d8b1675f70c487f89e4f27a3b . " (HLS)", getmypid(), $a7e968a4f6d75092e74cdeb1b406041a, $A75f2436a5614184bfe3442ddd050ec5, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95, $a7e968a4f6d75092e74cdeb1b406041a);
                                                                                                    $E821605d1d9382d422040b86d29632d9 = $f566700a43ee8e1f0412fe10fbdf03df->getLastInsertedId();
                                                                                                    goto f2fdf57746e4286908d3d70ab81d10de;
                                                                                                }
                                                                                                $c72d66b481d02f854f0bef67db92a547 = $f566700a43ee8e1f0412fe10fbdf03df->f1Ed191D78470660EdFF4a007696Bc1F();
                                                                                                if (!($c72d66b481d02f854f0bef67db92a547["hls_end"] == 1)) {
                                                                                                    $E821605d1d9382d422040b86d29632d9 = $c72d66b481d02f854f0bef67db92a547["activity_id"];
                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `user_activity_now` SET `hls_last_read` = '%d' WHERE `activity_id` = '%d'", time(), $c72d66b481d02f854f0bef67db92a547["activity_id"]);
                                                                                                    f2fdf57746e4286908d3d70ab81d10de:
                                                                                                    $f566700a43ee8e1f0412fe10fbdf03df->cA531F7bDC43B966DeFb4ABA3C8faF22();
                                                                                                    $output = "#EXTM3U\n";
                                                                                                    $output = "#EXTM3U\n#EXT-X-VERSION:3\n";
                                                                                                    $output = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-TARGETDURATION:60\n";
                                                                                                    $output = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-TARGETDURATION:60\n#EXT-X-MEDIA-SEQUENCE:0\n";
                                                                                                    $output = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-TARGETDURATION:60\n#EXT-X-MEDIA-SEQUENCE:0\n#EXT-X-PLAYLIST-TYPE:VOD\n";
                                                                                                    foreach ($Df55b74833e9468cafb620fe446225a1 as $Baee0c34e5755f1cfaa4159ea7e8702e => $B5d14e09bc25553da9030273f23468aa) {
                                                                                                        $output .= "#EXTINF:60.0,\n";
                                                                                                        $output .= "/timeshift/{$f6806488699d3315dc5dc1e27a401b3e}/{$password}/{$fd08711a26bab44719872c7fff1f2dfb}/{$Be553c1662ffa5054ccb6c5ce822974b}/{$ba85d77d367dcebfcc2a3db9e83bb581}_{$Baee0c34e5755f1cfaa4159ea7e8702e}_" . $B5d14e09bc25553da9030273f23468aa["filesize"] . ".ts\n";
                                                                                                    }
                                                                                                    $output .= "#EXT-X-ENDLIST";
                                                                                                    header("Content-Type: application/x-mpegurl");
                                                                                                    header("Content-Length: " . strlen($output));
                                                                                                    echo $output;
                                                                                                    die;
                                                                                                }
                                                                                                header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden", true, 403);
                                                                                                die;
                                                                                            }
                                                                                            if (!(!empty($Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]) && file_exists($Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]["filename"]) && $Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]["filesize"] == $F19b64ffad55876d290cb6f756a2dea5)) {
                                                                                                goto Dc34173fbb893b5366212f1d919b16f7;
                                                                                            }
                                                                                            $B3acfaf2dca0db7e9507c5e640b4ba41 = 0;
                                                                                            $b362cb2e1492b66663cf3718328409ad = $Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]["filesize"];
                                                                                            if (!($F919000263e8ad8e2791f92d8919f629 == 0)) {
                                                                                                goto F0fb99f5891aff12295945073b705c74;
                                                                                            }
                                                                                            $B3acfaf2dca0db7e9507c5e640b4ba41 = $Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]["filesize"] * 0.3;
                                                                                            $b362cb2e1492b66663cf3718328409ad = $Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]["filesize"] - $B3acfaf2dca0db7e9507c5e640b4ba41;
                                                                                            F0fb99f5891aff12295945073b705c74:
                                                                                            header("Content-Type: video/mp2t");
                                                                                            header("Content-Length: " . $b362cb2e1492b66663cf3718328409ad);
                                                                                            $Ab9f45b38498c3a010f3c4276ad5767c = fopen($Df55b74833e9468cafb620fe446225a1[$F919000263e8ad8e2791f92d8919f629]["filename"], "r");
                                                                                            fseek($Ab9f45b38498c3a010f3c4276ad5767c, $B3acfaf2dca0db7e9507c5e640b4ba41);
                                                                                            B54b3db2c76fb47060bd112d83e284c6:
                                                                                            if (feof($Ab9f45b38498c3a010f3c4276ad5767c)) {
                                                                                                fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                                                                                                Dc34173fbb893b5366212f1d919b16f7:
                                                                                                die;
                                                                                            }
                                                                                            echo stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, a78BF8d35765be2408C50712cE7A43AD::$settings["read_buffer_size"]);
                                                                                            goto B54b3db2c76fb47060bd112d83e284c6;
                                                                                        default:
                                                                                            header("Content-Type: video/mp2t");
                                                                                            if (empty($a8df9f055e91a1e9240230b69af85555)) {
                                                                                                goto ff670f0ee5cec293f777df829ffab43b;
                                                                                            }
                                                                                            $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `user_activity_now` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES('%d','%d','%d','%s','%s','%s','%d','%d','%s','%s','%s')", $a8df9f055e91a1e9240230b69af85555["id"], $ba85d77d367dcebfcc2a3db9e83bb581, SERVER_ID, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $E2e6656d8b1675f70c487f89e4f27a3b, getmypid(), $a7e968a4f6d75092e74cdeb1b406041a, $A75f2436a5614184bfe3442ddd050ec5, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95);
                                                                                            $E821605d1d9382d422040b86d29632d9 = $f566700a43ee8e1f0412fe10fbdf03df->getLastInsertedId();
                                                                                            $Cec7b3525ded5578bb4eaefe5020eb98 = TMP_DIR . $E821605d1d9382d422040b86d29632d9 . ".con";
                                                                                            $f566700a43ee8e1f0412fe10fbdf03df->CA531F7bdC43B966Defb4aBA3c8fAF22();
                                                                                            ff670f0ee5cec293f777df829ffab43b:
                                                                                            $b362cb2e1492b66663cf3718328409ad = $Ff876e96994aa5b09ce92e771efe2038 = D86041F168a5452E8fDEACFbFd659E19($Df55b74833e9468cafb620fe446225a1);
                                                                                            $D2f61e797d44efa20d9d559b2fc2c039 = $Ff876e96994aa5b09ce92e771efe2038 * 0.008 / ($fd08711a26bab44719872c7fff1f2dfb * 60);
                                                                                            header("Accept-Ranges: 0-{$b362cb2e1492b66663cf3718328409ad}");
                                                                                            $start = 0;
                                                                                            $ebe823668f9748302d3bd87782a71948 = $Ff876e96994aa5b09ce92e771efe2038 - 1;
                                                                                            if (!isset($_SERVER["HTTP_RANGE"])) {
                                                                                                goto E6ae50bc9d0c6e9dfca974b6d2a61c7b;
                                                                                            }
                                                                                            $dccf2f0f292208ba833261a4da87860d = $start;
                                                                                            $A34771e85be87aded632236239e94d98 = $ebe823668f9748302d3bd87782a71948;
                                                                                            list(, $cabafd9509f1a525c1d85143a5372ed8) = explode("=", $_SERVER["HTTP_RANGE"], 2);
                                                                                            if (!(strpos($cabafd9509f1a525c1d85143a5372ed8, ",") !== false)) {
                                                                                                if ($cabafd9509f1a525c1d85143a5372ed8 == "-") {
                                                                                                    $dccf2f0f292208ba833261a4da87860d = $Ff876e96994aa5b09ce92e771efe2038 - substr($cabafd9509f1a525c1d85143a5372ed8, 1);
                                                                                                    goto F462661f0ee0fcf712e8cf84b4f50579;
                                                                                                }
                                                                                                $cabafd9509f1a525c1d85143a5372ed8 = explode("-", $cabafd9509f1a525c1d85143a5372ed8);
                                                                                                $dccf2f0f292208ba833261a4da87860d = $cabafd9509f1a525c1d85143a5372ed8[0];
                                                                                                $A34771e85be87aded632236239e94d98 = isset($cabafd9509f1a525c1d85143a5372ed8[1]) && is_numeric($cabafd9509f1a525c1d85143a5372ed8[1]) ? $cabafd9509f1a525c1d85143a5372ed8[1] : $Ff876e96994aa5b09ce92e771efe2038;
                                                                                                F462661f0ee0fcf712e8cf84b4f50579:
                                                                                                $A34771e85be87aded632236239e94d98 = $A34771e85be87aded632236239e94d98 > $ebe823668f9748302d3bd87782a71948 ? $ebe823668f9748302d3bd87782a71948 : $A34771e85be87aded632236239e94d98;
                                                                                                if (!($dccf2f0f292208ba833261a4da87860d > $A34771e85be87aded632236239e94d98 || $dccf2f0f292208ba833261a4da87860d > $Ff876e96994aa5b09ce92e771efe2038 - 1 || $A34771e85be87aded632236239e94d98 >= $Ff876e96994aa5b09ce92e771efe2038)) {
                                                                                                    $start = $dccf2f0f292208ba833261a4da87860d;
                                                                                                    $ebe823668f9748302d3bd87782a71948 = $A34771e85be87aded632236239e94d98;
                                                                                                    $b362cb2e1492b66663cf3718328409ad = $ebe823668f9748302d3bd87782a71948 - $start + 1;
                                                                                                    header("HTTP/1.1 206 Partial Content");
                                                                                                    E6ae50bc9d0c6e9dfca974b6d2a61c7b:
                                                                                                    header("Content-Range: bytes {$start}-{$ebe823668f9748302d3bd87782a71948}/{$Ff876e96994aa5b09ce92e771efe2038}");
                                                                                                    header("Content-Length: " . $b362cb2e1492b66663cf3718328409ad);
                                                                                                    $b3fcd87510baa9521882b459861dcb64 = 0;
                                                                                                    if (!($start > 0)) {
                                                                                                        goto B5760f5e3012e06f1bc3daa714790889;
                                                                                                    }
                                                                                                    $b3fcd87510baa9521882b459861dcb64 = floor($start / ($Ff876e96994aa5b09ce92e771efe2038 / count($Df55b74833e9468cafb620fe446225a1)));
                                                                                                    B5760f5e3012e06f1bc3daa714790889:
                                                                                                    $c77e7ff2c5d6b14d931b3344c54e0cc5 = false;
                                                                                                    $B3acfaf2dca0db7e9507c5e640b4ba41 = 0;
                                                                                                    $F1e7eccb846733c8f188bcdec720f3b7 = 0;
                                                                                                    $b2ecba26bb0e977abdb88e118b553d51 = $D2f61e797d44efa20d9d559b2fc2c039 * 125;
                                                                                                    $b2ecba26bb0e977abdb88e118b553d51 += $b2ecba26bb0e977abdb88e118b553d51 * A78BF8D35765Be2408c50712cE7a43ad::$settings["vod_bitrate_plus"] * 0.01;
                                                                                                    $c41986ad785eace90882e61c64cabb41 = time();
                                                                                                    $b1125d7ae8a179e8c8a4c80974755bd7 = 0;
                                                                                                    $C7558f823ac28009bfd4730a82f1f01b = A78bF8d35765Be2408C50712cE7A43AD::$settings["read_buffer_size"];
                                                                                                    $C48e0083a9caa391609a3c645a2ec889 = 0;
                                                                                                    $b0cd8de619914d3df89e9fc24acad4e6 = 0;
                                                                                                    if (a78bf8d35765bE2408c50712Ce7a43aD::$settings["vod_limit_at"] > 0) {
                                                                                                        $F6295a8bab3aa6bb5b9c4a70c99ec761 = intval($Ff876e96994aa5b09ce92e771efe2038 * A78BF8d35765be2408c50712Ce7A43aD::$settings["vod_limit_at"] / 100);
                                                                                                        goto B8ab5128a28775f19e466014eab69f02;
                                                                                                    }
                                                                                                    $F6295a8bab3aa6bb5b9c4a70c99ec761 = $Ff876e96994aa5b09ce92e771efe2038;
                                                                                                    B8ab5128a28775f19e466014eab69f02:
                                                                                                    $A8e591a80910b24673b1a94b8219ab96 = false;
                                                                                                    foreach ($Df55b74833e9468cafb620fe446225a1 as $Baee0c34e5755f1cfaa4159ea7e8702e => $B5d14e09bc25553da9030273f23468aa) {
                                                                                                        $F1e7eccb846733c8f188bcdec720f3b7 += $B5d14e09bc25553da9030273f23468aa["filesize"];
                                                                                                        if (!(!$c77e7ff2c5d6b14d931b3344c54e0cc5 && $b3fcd87510baa9521882b459861dcb64 > 0)) {
                                                                                                            goto ec8964e659f3d5f843239d58cc1cb5cf;
                                                                                                        }
                                                                                                        if ($b3fcd87510baa9521882b459861dcb64 > $Baee0c34e5755f1cfaa4159ea7e8702e) {
                                                                                                            goto bf705a2f20da4ec4abb5062ccbc64ff2;
                                                                                                        }
                                                                                                        $c77e7ff2c5d6b14d931b3344c54e0cc5 = true;
                                                                                                        $B3acfaf2dca0db7e9507c5e640b4ba41 = $start - $F1e7eccb846733c8f188bcdec720f3b7;
                                                                                                        ec8964e659f3d5f843239d58cc1cb5cf:
                                                                                                        $Ab9f45b38498c3a010f3c4276ad5767c = fopen($B5d14e09bc25553da9030273f23468aa["filename"], "rb");
                                                                                                        fseek($Ab9f45b38498c3a010f3c4276ad5767c, $B3acfaf2dca0db7e9507c5e640b4ba41);
                                                                                                        dafe2b9523cba3c52c343947c994cdff:
                                                                                                        if (feof($Ab9f45b38498c3a010f3c4276ad5767c)) {
                                                                                                            if (!is_resource($Ab9f45b38498c3a010f3c4276ad5767c)) {
                                                                                                                goto a8e25af242d6fab506edda0a260320b4;
                                                                                                            }
                                                                                                            fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                                                                                                            a8e25af242d6fab506edda0a260320b4:
                                                                                                            $B3acfaf2dca0db7e9507c5e640b4ba41 = 0;
                                                                                                            bf705a2f20da4ec4abb5062ccbc64ff2:
                                                                                                        }
                                                                                                        $Fe917966573bdf0b43ab9723bb50fc6b = ftell($Ab9f45b38498c3a010f3c4276ad5767c);
                                                                                                        $B83f861d078c18d9643641c765cefee9 = stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, $C7558f823ac28009bfd4730a82f1f01b);
                                                                                                        echo $B83f861d078c18d9643641c765cefee9;
                                                                                                        $b1125d7ae8a179e8c8a4c80974755bd7 += strlen($B83f861d078c18d9643641c765cefee9);
                                                                                                        ++$C48e0083a9caa391609a3c645a2ec889;
                                                                                                        if (!$A8e591a80910b24673b1a94b8219ab96 && $b0cd8de619914d3df89e9fc24acad4e6 * $C7558f823ac28009bfd4730a82f1f01b >= $F6295a8bab3aa6bb5b9c4a70c99ec761) {
                                                                                                            $A8e591a80910b24673b1a94b8219ab96 = true;
                                                                                                            goto B8dab62f8f778bb7983a085151c9fa83;
                                                                                                        }
                                                                                                        ++$b0cd8de619914d3df89e9fc24acad4e6;
                                                                                                        B8dab62f8f778bb7983a085151c9fa83:
                                                                                                        if (!($b2ecba26bb0e977abdb88e118b553d51 > 0 && $A8e591a80910b24673b1a94b8219ab96 && $C48e0083a9caa391609a3c645a2ec889 >= ceil($b2ecba26bb0e977abdb88e118b553d51 / $C7558f823ac28009bfd4730a82f1f01b))) {
                                                                                                            goto Cb29fdbbe1e01fd2af1de47521927aa3;
                                                                                                        }
                                                                                                        sleep(1);
                                                                                                        $C48e0083a9caa391609a3c645a2ec889 = 0;
                                                                                                        Cb29fdbbe1e01fd2af1de47521927aa3:
                                                                                                        if (!(time() - $c41986ad785eace90882e61c64cabb41 >= 30)) {
                                                                                                            goto cddc31153422a73b3651dfa1073a41ce;
                                                                                                        }
                                                                                                        file_put_contents($Cec7b3525ded5578bb4eaefe5020eb98, intval($b1125d7ae8a179e8c8a4c80974755bd7 / 1024 / 30));
                                                                                                        $c41986ad785eace90882e61c64cabb41 = time();
                                                                                                        $b1125d7ae8a179e8c8a4c80974755bd7 = 0;
                                                                                                        cddc31153422a73b3651dfa1073a41ce:
                                                                                                        goto dafe2b9523cba3c52c343947c994cdff;
                                                                                                    }
                                                                                                }
                                                                                                header("HTTP/1.1 416 Requested Range Not Satisfiable");
                                                                                                header("Content-Range: bytes {$start}-{$ebe823668f9748302d3bd87782a71948}/{$Ff876e96994aa5b09ce92e771efe2038}");
                                                                                                die;
                                                                                            }
                                                                                            header("HTTP/1.1 416 Requested Range Not Satisfiable");
                                                                                            header("Content-Range: bytes {$start}-{$ebe823668f9748302d3bd87782a71948}/{$Ff876e96994aa5b09ce92e771efe2038}");
                                                                                            die;
                                                                                    }
                                                                                    B64fb1230dd1ca823670e8a756e2d78f:
                                                                                    e02473341a014647a9e31478796f3412:
                                                                                    function d86041f168a5452e8FDEacfbfD659E19($Df55b74833e9468cafb620fe446225a1) {
                                                                                        $b362cb2e1492b66663cf3718328409ad = 0;
                                                                                        foreach ($Df55b74833e9468cafb620fe446225a1 as $B5d14e09bc25553da9030273f23468aa) {
                                                                                            $b362cb2e1492b66663cf3718328409ad += $B5d14e09bc25553da9030273f23468aa["filesize"];
                                                                                        }
                                                                                        return $b362cb2e1492b66663cf3718328409ad;
                                                                                    }
                                                                                    function shutdown() {
                                                                                        global $f566700a43ee8e1f0412fe10fbdf03df, $f0ac6ad2b40669833242a10c23cad2e0, $E821605d1d9382d422040b86d29632d9, $Cec7b3525ded5578bb4eaefe5020eb98, $a8df9f055e91a1e9240230b69af85555, $E2e6656d8b1675f70c487f89e4f27a3b, $ba85d77d367dcebfcc2a3db9e83bb581, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $A75f2436a5614184bfe3442ddd050ec5, $a349f0750f0a814bd31ec4b3da51da95, $a7e968a4f6d75092e74cdeb1b406041a;
                                                                                        if (!$f0ac6ad2b40669833242a10c23cad2e0) {
                                                                                            goto fb4c29e4553c43c8a77d3e4eaeac01db;
                                                                                        }
                                                                                        d9f93b7C177e377D0bbFE315EAEAe505();
                                                                                        http_response_code(401);
                                                                                        fb4c29e4553c43c8a77d3e4eaeac01db:
                                                                                        $f566700a43ee8e1f0412fe10fbdf03df->cA531F7BDc43B966defb4abA3C8faf22();
                                                                                        if (!($E821605d1d9382d422040b86d29632d9 !== false)) {
                                                                                            goto c25cd4c4d73d874402d6033fe752e36f;
                                                                                        }
                                                                                        cd89785224751cCA8017139DAF9E891e::e990445B40642E0efD070E994375f6AF($E821605d1d9382d422040b86d29632d9);
                                                                                        CD89785224751CCa8017139DaF9e891E::A49c2Fb1EBa096c52a352a85C8d09d8D(SERVER_ID, $a8df9f055e91a1e9240230b69af85555["id"], $ba85d77d367dcebfcc2a3db9e83bb581, $a7e968a4f6d75092e74cdeb1b406041a, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $E2e6656d8b1675f70c487f89e4f27a3b, $A75f2436a5614184bfe3442ddd050ec5, $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a349f0750f0a814bd31ec4b3da51da95);
                                                                                        if (!file_exists($Cec7b3525ded5578bb4eaefe5020eb98)) {
                                                                                            goto F0aabca77894360d984b4ff70f81ec85;
                                                                                        }
                                                                                        unlink($Cec7b3525ded5578bb4eaefe5020eb98);
                                                                                        F0aabca77894360d984b4ff70f81ec85:
                                                                                        c25cd4c4d73d874402d6033fe752e36f:
                                                                                        fastcgi_finish_request();
                                                                                        posix_kill(getmypid(), 9);
                                                                                    }
                                                                                    // [PHPDeobfuscator] Implied script end
                                                                                    return;
                                                                                }
                                                                                $Ca434bcc380e9dbd2a3a588f6c32d84f = TV_ARCHIVE . $ba85d77d367dcebfcc2a3db9e83bb581 . "/" . date("Y-m-d:H-i", $c4eb261e96f50c6cac5c08c60d657d9c + $C48e0083a9caa391609a3c645a2ec889 * 60) . ".ts";
                                                                                if (!file_exists($Ca434bcc380e9dbd2a3a588f6c32d84f)) {
                                                                                    goto ba343d2d06659f5abb138f26356c9e64;
                                                                                }
                                                                                $Df55b74833e9468cafb620fe446225a1[] = array("filename" => $Ca434bcc380e9dbd2a3a588f6c32d84f, "filesize" => filesize($Ca434bcc380e9dbd2a3a588f6c32d84f));
                                                                                ba343d2d06659f5abb138f26356c9e64:
                                                                                $C48e0083a9caa391609a3c645a2ec889++;
                                                                                goto D5fcf2a72e99ec35092bea70c6000d54;
                                                                            }
                                                                            header("HTTP/1.1 404 Not Found");
                                                                            die;
                                                                        }
                                                                        header("HTTP/1.1 400 Bad Request");
                                                                        die;
                                                                    }
                                                                    die;
                                                                }
                                                                http_response_code(403);
                                                                die;
                                                            }
                                                            Cd89785224751Cca8017139Daf9E891E::C1591643EafdAaE33FF6E69e5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "CON_SVP", $f4889efa84e1f2e30e5e9780973f68cb, json_encode(array("user_agent" => $userAgent, "isp" => $a8df9f055e91a1e9240230b69af85555["con_isp_name"], "type" => $a8df9f055e91a1e9240230b69af85555["con_isp_type"])), true);
                                                            http_response_code(401);
                                                            die;
                                                        }
                                                        http_response_code(401);
                                                        cd89785224751CcA8017139DaF9E891E::c1591643eafDaae33ff6e69e5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "ISP_LOCK_FAILED", $f4889efa84e1f2e30e5e9780973f68cb, json_encode(array("old" => $a8df9f055e91a1e9240230b69af85555["isp_desc"], "new" => $a8df9f055e91a1e9240230b69af85555["con_isp_name"])));
                                                        die;
                                                    }
                                                    http_response_code(406);
                                                    CD89785224751cCA8017139Daf9E891e::c1591643EaFdaAe33ff6E69e5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "NOT_IN_BOUQUET", $f4889efa84e1f2e30e5e9780973f68cb);
                                                    die;
                                                }
                                                cD89785224751cCa8017139daF9E891e::c1591643eAfdaaE33fF6e69e5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_ALREADY_CONNECTED", $f4889efa84e1f2e30e5e9780973f68cb);
                                                die;
                                            }
                                            cD89785224751Cca8017139Daf9E891e::c1591643EAfDaae33FF6e69e5E49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "CRACKED", $f4889efa84e1f2e30e5e9780973f68cb);
                                            die;
                                        }
                                        Cd89785224751cCA8017139DaF9E891E::C1591643EAfDAaE33Ff6E69E5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_AGENT_BAN", $f4889efa84e1f2e30e5e9780973f68cb);
                                        die;
                                    }
                                    cd89785224751CcA8017139dAF9E891e::C1591643eAFdAae33Ff6E69e5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "COUNTRY_DISALLOW", $f4889efa84e1f2e30e5e9780973f68cb);
                                    die;
                                }
                                Cd89785224751ccA8017139DaF9E891E::C1591643eAFdaAE33FF6e69e5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "COUNTRY_DISALLOW", $f4889efa84e1f2e30e5e9780973f68cb);
                                die;
                            }
                            Cd89785224751cca8017139DAF9E891E::c1591643eaFdAaE33FF6E69E5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "IP_BAN", $f4889efa84e1f2e30e5e9780973f68cb);
                            die;
                        }
                        cD89785224751cCA8017139daf9e891e::c1591643eAfDaaE33FF6E69E5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "EMPTY_UA", $f4889efa84e1f2e30e5e9780973f68cb);
                        die;
                    }
                    Cd89785224751CCa8017139DaF9e891e::C1591643eAFDAae33ff6e69E5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_DISABLED", $f4889efa84e1f2e30e5e9780973f68cb);
                    CD89785224751cca8017139daF9E891e::D0b968cd6cFdf340Ca85B1c3d9A40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_banned_video", "banned_video_path");
                    die;
                }
                CD89785224751cca8017139daF9E891E::C1591643EaFDaAe33fF6e69e5e49D651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_BAN", $f4889efa84e1f2e30e5e9780973f68cb);
                cD89785224751CCA8017139daF9E891e::d0B968Cd6cFdf340CA85B1C3D9A40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_banned_video", "banned_video_path");
                die;
            }
            cD89785224751CcA8017139DAF9E891E::C1591643EAfDAAE33ff6E69E5e49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "USER_EXPIRED", $f4889efa84e1f2e30e5e9780973f68cb);
            cD89785224751cCA8017139dAF9E891E::d0B968Cd6cFdf340CA85B1C3d9a40649($a8df9f055e91a1e9240230b69af85555["is_restreamer"], "show_expired_video", "expired_video_path");
            die;
        }
        cd89785224751Cca8017139dAf9E891E::c1591643EAfDaAE33Ff6E69E5E49d651($ba85d77d367dcebfcc2a3db9e83bb581, $a8df9f055e91a1e9240230b69af85555["id"], "MAG_TOKEN_INVALID", $f4889efa84e1f2e30e5e9780973f68cb);
        die;
    }
    die;
}
die("Missing parameters.");
