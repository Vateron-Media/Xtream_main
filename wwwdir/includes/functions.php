<?php

function eaaB451eF7a60c6D480E43B6C15a14A1($fbcc64af1e61bd602e57af35b343f521, $E7cca48cfca85fc445419a32d7d8f973) {
    $C48e0083a9caa391609a3c645a2ec889 = 0;
    $F2e6026e9c178369abb5fa65c7bf432b = '';
    foreach (str_split($fbcc64af1e61bd602e57af35b343f521) as $d88b7dc5f89e0e7d0d394100bf992462) {
        $F2e6026e9c178369abb5fa65c7bf432b .= chr(ord($d88b7dc5f89e0e7d0d394100bf992462) ^ ord($E7cca48cfca85fc445419a32d7d8f973[$C48e0083a9caa391609a3c645a2ec889++ % strlen($E7cca48cfca85fc445419a32d7d8f973)]));
    }
    return $F2e6026e9c178369abb5fa65c7bf432b;
}
function systemStatus() {
    $statusData = array();
    $statusData["cpu"] = intval(A072E3167C4fD73Eb67540546C961B7E());
    $statusData["cpu_cores"] = intval(shell_exec("cat /proc/cpuinfo | grep \"^processor\" | wc -l"));
    $statusData["cpu_avg"] = intval(sys_getloadavg()[0] * 100 / $statusData["cpu_cores"]);
    if ($statusData["cpu_avg"] > 100) {
        $statusData["cpu_avg"] = 100;
    }
    $b05334022f117f99e07e10e7120b3707 = (int) trim(shell_exec("free | grep -c available"));
    if ($b05334022f117f99e07e10e7120b3707 == 0) {
        $statusData["total_mem"] = intval(shell_exec("/usr/bin/free -tk | grep -i Mem: | awk '{print \$2}'"));
        $statusData["total_mem_free"] = intval(shell_exec("/usr/bin/free -tk | grep -i Mem: | awk '{print \$4+\$6+\$7}'"));
    } else {
        $statusData["total_mem"] = intval(shell_exec("/usr/bin/free -tk | grep -i Mem: | awk '{print \$2}'"));
        $statusData["total_mem_free"] = intval(shell_exec("/usr/bin/free -tk | grep -i Mem: | awk '{print \$7}'"));
    }
    $statusData["total_mem_used"] = $statusData["total_mem"] - $statusData["total_mem_free"];
    $statusData["total_mem_used_percent"] = (int) $statusData["total_mem_used"] / $statusData["total_mem"] * 100;
    $statusData["total_disk_space"] = disk_total_space(IPTV_PANEL_DIR);
    $statusData["uptime"] = getUpTime();
    $statusData["total_running_streams"] = shell_exec("ps ax | grep -v grep | grep ffmpeg | grep -c FFMPEG_PATH");
    $d0d324f3dbb8bbc5fff56e8a848beb7a = A78bf8D35765bE2408c50712CE7a43AD::$StreamingServers[SERVER_ID]["network_interface"];
    $statusData["bytes_sent"] = 0;
    $statusData["bytes_received"] = 0;
    if (!file_exists("/sys/class/net/{$d0d324f3dbb8bbc5fff56e8a848beb7a}/statistics/tx_bytes")) {
        goto f4c04f8cdfcfe0fdb1d38bf694495245;
    }
    $b10021b298f7d4ce2f8e80315325fa1a = trim(file_get_contents("/sys/class/net/{$d0d324f3dbb8bbc5fff56e8a848beb7a}/statistics/tx_bytes"));
    $C5b51b10f98c22fb985e90c23eade263 = trim(file_get_contents("/sys/class/net/{$d0d324f3dbb8bbc5fff56e8a848beb7a}/statistics/rx_bytes"));
    sleep(1);
    $e54a6ff3afc52767cdd38f62ab4c38d1 = trim(file_get_contents("/sys/class/net/{$d0d324f3dbb8bbc5fff56e8a848beb7a}/statistics/tx_bytes"));
    $d1a978924624c41845605404ded7e846 = trim(file_get_contents("/sys/class/net/{$d0d324f3dbb8bbc5fff56e8a848beb7a}/statistics/rx_bytes"));
    $c01d5077f34dc0ef046a6efa9e8e24f4 = round(($e54a6ff3afc52767cdd38f62ab4c38d1 - $b10021b298f7d4ce2f8e80315325fa1a) / 1024 * 0.0078125, 2);
    $B5490c2f61c894c091e04441954a0f09 = round(($d1a978924624c41845605404ded7e846 - $C5b51b10f98c22fb985e90c23eade263) / 1024 * 0.0078125, 2);
    $statusData["bytes_sent"] = $c01d5077f34dc0ef046a6efa9e8e24f4;
    $statusData["bytes_received"] = $B5490c2f61c894c091e04441954a0f09;
    f4c04f8cdfcfe0fdb1d38bf694495245:
    $statusData["cpu_load_average"] = sys_getloadavg()[0];
    return $statusData;
}
function e6A2B39B5861D06ca4034887864A5Fb5() {
    $f93d929641c6b747360282c4db5c91dd = array("/iphone/i" => "iPhone", "/ipod/i" => "iPod", "/ipad/i" => "iPad", "/android/i" => "Android", "/blackberry/i" => "BlackBerry", "/webos/i" => "Mobile");
    foreach ($f93d929641c6b747360282c4db5c91dd as $Ce397562fcf2ed0fca47e4a48152c1ff => $f543392c71508ec7c2555f6fc8d3294d) {
        if (!preg_match($Ce397562fcf2ed0fca47e4a48152c1ff, $_SERVER["HTTP_USER_AGENT"])) {
        }
        return true;
    }
    return false;
}
function c39eD4eaD88eD7C28c7C17F4FBb37669($e651d3327c00dab0032bac22e53d91e5, $E7cca48cfca85fc445419a32d7d8f973, $a1daec950dd361ae639ad3a57dc018c0) {
    $Af301a166badb15e0b00336d72fb9497 = array();
    B437c8Ac70D749dAD4936900DBa780F9($e651d3327c00dab0032bac22e53d91e5, $E7cca48cfca85fc445419a32d7d8f973, $a1daec950dd361ae639ad3a57dc018c0, $Af301a166badb15e0b00336d72fb9497);
    return array();
}
function B437c8AC70D749Dad4936900dbA780f9($e651d3327c00dab0032bac22e53d91e5, $E7cca48cfca85fc445419a32d7d8f973, $a1daec950dd361ae639ad3a57dc018c0, &$Af301a166badb15e0b00336d72fb9497) {
    if (is_array($e651d3327c00dab0032bac22e53d91e5)) {
        if (!(isset($e651d3327c00dab0032bac22e53d91e5[$E7cca48cfca85fc445419a32d7d8f973]) && $e651d3327c00dab0032bac22e53d91e5[$E7cca48cfca85fc445419a32d7d8f973] == $a1daec950dd361ae639ad3a57dc018c0)) {
            goto C8905f5605a877565e9fc933f8b06bdf;
        }
        $Af301a166badb15e0b00336d72fb9497[] = $e651d3327c00dab0032bac22e53d91e5;
        C8905f5605a877565e9fc933f8b06bdf:
        foreach ($e651d3327c00dab0032bac22e53d91e5 as $cf893362b341e42756ec3a6055a2bb5f) {
            b437c8Ac70d749dad4936900DBA780f9($cf893362b341e42756ec3a6055a2bb5f, $E7cca48cfca85fc445419a32d7d8f973, $a1daec950dd361ae639ad3a57dc018c0, $Af301a166badb15e0b00336d72fb9497);
        }
        return;
    }
    return;
}
function BBd9e78AC32626E138e758e840305a7C($e5ececd623496efd3a17d36d4eb4b945, $Af218a53429705d6e319475a2185cd90 = 600) {
    if (!file_exists($e5ececd623496efd3a17d36d4eb4b945)) {
        goto ebc039a7a89bdc10d5c3464749c6e209;
    }
    $Bc7d327b1510891329ca9859db27320f = trim(file_get_contents($e5ececd623496efd3a17d36d4eb4b945));
    if (!file_exists("/proc/" . $Bc7d327b1510891329ca9859db27320f)) {
        goto f8575d2ecabf2a9124d1acc4daad6e06;
    }
    if (!(time() - filemtime($e5ececd623496efd3a17d36d4eb4b945) < $Af218a53429705d6e319475a2185cd90)) {
        posix_kill($Bc7d327b1510891329ca9859db27320f, 9);
        f8575d2ecabf2a9124d1acc4daad6e06:
        ebc039a7a89bdc10d5c3464749c6e209:
        file_put_contents($e5ececd623496efd3a17d36d4eb4b945, getmypid());
        return false;
    }
    die("Running...");
}
function D9F93B7c177e377D0BbfE315eAeAE505() {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    if (!(a78BF8D35765Be2408c50712cE7A43ad::$settings["flood_limit"] == 0)) {
        $f4889efa84e1f2e30e5e9780973f68cb = Cd89785224751ccA8017139DAF9E891e::E1f75a50F74a8f4E2129BA474f45D670();
        if (!(empty($f4889efa84e1f2e30e5e9780973f68cb) || in_array($f4889efa84e1f2e30e5e9780973f68cb, Cd89785224751ccA8017139Daf9E891e::AB69E1103c96ee33fE21A6453d788925()))) {
            $d07f0d12ec3e6a615c9e2128f53c0021 = array_filter(array_unique(explode(",", A78Bf8d35765bE2408C50712Ce7A43aD::$settings["flood_ips_exclude"])));
            if (!in_array($f4889efa84e1f2e30e5e9780973f68cb, $d07f0d12ec3e6a615c9e2128f53c0021)) {
                $b63b894b2f9b5aabe135ef4a17f2aed8 = TMP_DIR . $f4889efa84e1f2e30e5e9780973f68cb . ".flood";
                if (file_exists($b63b894b2f9b5aabe135ef4a17f2aed8)) {
                    $Fa6f56ee50a6331b464fdee0f2d47c94 = json_decode(file_get_contents($b63b894b2f9b5aabe135ef4a17f2aed8), true);
                    $E2d3cbb540b5d181229a8dd8556edb4e = a78BF8D35765be2408C50712cE7A43ad::$settings["flood_seconds"];
                    $Fb18c9917ee3e7aa4417a4f60504a9b5 = A78BF8D35765bE2408C50712CE7A43Ad::$settings["flood_limit"];
                    if (time() - $Fa6f56ee50a6331b464fdee0f2d47c94["last_request"] <= $E2d3cbb540b5d181229a8dd8556edb4e) {
                        ++$Fa6f56ee50a6331b464fdee0f2d47c94["requests"];
                        if (!($Fa6f56ee50a6331b464fdee0f2d47c94["requests"] >= $Fb18c9917ee3e7aa4417a4f60504a9b5)) {
                            $Fa6f56ee50a6331b464fdee0f2d47c94["last_request"] = time();
                            file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, json_encode($Fa6f56ee50a6331b464fdee0f2d47c94), LOCK_EX);
                            goto f091ce3b538bbffab5be74d0a4487d00;
                        }
                        $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `blocked_ips` (`ip`,`notes`,`date`) VALUES('%s','%s','%d')", $f4889efa84e1f2e30e5e9780973f68cb, "FLOOD ATTACK", time());
                        A7785208D901bEa02B65446067CFd0B3::F320B6A3920944D8a18D7949c8abAcE4(array_keys(a78BF8D35765be2408c50712cE7A43ad::$StreamingServers), "sudo /sbin/iptables -A INPUT -s {$f4889efa84e1f2e30e5e9780973f68cb} -j DROP");
                        unlink($b63b894b2f9b5aabe135ef4a17f2aed8);
                        return;
                    }
                    $Fa6f56ee50a6331b464fdee0f2d47c94["requests"] = 0;
                    $Fa6f56ee50a6331b464fdee0f2d47c94["last_request"] = time();
                    file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, json_encode($Fa6f56ee50a6331b464fdee0f2d47c94), LOCK_EX);
                    f091ce3b538bbffab5be74d0a4487d00:
                    goto D3bbdde88da38595dc683e7b767a18d7;
                }
                file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, json_encode(array("requests" => 0, "last_request" => time())), LOCK_EX);
                D3bbdde88da38595dc683e7b767a18d7:

                return;
            }
            return;
        }
        return;
    }
    return;
}
function b66dac37e77D0B4B60e2De1e5e4FA184($ba85d77d367dcebfcc2a3db9e83bb581, $ea6531b28219f4f71cfd02aec23a0f33 = false) {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT `type`,`movie_propeties`,`epg_id`,`channel_id` FROM `streams` WHERE `id` = '%d'", $ba85d77d367dcebfcc2a3db9e83bb581);
    if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0)) {
        return array();
    }
    $d76067cf9572f7a6691c85c12faf2a29 = $f566700a43ee8e1f0412fe10fbdf03df->f1eD191d78470660eDff4a007696BC1f();
    if ($d76067cf9572f7a6691c85c12faf2a29["type"] != 2) {
        if ($ea6531b28219f4f71cfd02aec23a0f33) {
            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `epg_data` WHERE `epg_id` = '%d' AND `channel_id` = '%s' AND `end` >= '%s'", $d76067cf9572f7a6691c85c12faf2a29["epg_id"], $d76067cf9572f7a6691c85c12faf2a29["channel_id"], date("Y-m-d H:i:00"));
            goto b2a5f34b506704a0b524a93cf2a84901;
        }
        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `epg_data` WHERE `epg_id` = '%d' AND `channel_id` = '%s'", $d76067cf9572f7a6691c85c12faf2a29["epg_id"], $d76067cf9572f7a6691c85c12faf2a29["channel_id"]);
        b2a5f34b506704a0b524a93cf2a84901:
        return $f566700a43ee8e1f0412fe10fbdf03df->C126fd559932F625CdF6098d86C63880();
    }
    return json_decode($d76067cf9572f7a6691c85c12faf2a29["movie_propeties"], true);
}
function a072E3167c4Fd73EB67540546C961B7e() {
    $A00fdf3e17773cc697a9e9760a752e67 = intval(shell_exec("ps aux|awk 'NR > 0 { s +=\$3 }; END {print s}'"));
    $Beead58eb65f6a16b84a5d7f85a2dbd0 = intval(shell_exec("grep --count processor /proc/cpuinfo"));
    return intval($A00fdf3e17773cc697a9e9760a752e67 / $Beead58eb65f6a16b84a5d7f85a2dbd0);
}
function getMagDeviceProfile($magSn, $mac, $ver, $stbType, $imageVer, $deviceId, $deviceId2, $hwVersion, $realIP, $A6dde9bd7afc06231a1481ec56fd5768, $type, $action) {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    $mac = base64_encode(strtoupper(urldecode($mac)));
    $cfc7b4c8f12f119c2180693d0fa61648 = false;
    if (!(!$A6dde9bd7afc06231a1481ec56fd5768 && (!empty($ver) || !empty($stbType) || !empty($imageVer) || !empty($deviceId) || !empty($deviceId2) || !empty($hwVersion)))) {
        goto aae3695cb588ac90fbed968e4f4cad0b;
    }
    $cfc7b4c8f12f119c2180693d0fa61648 = true;
    aae3695cb588ac90fbed968e4f4cad0b:
    if (!(!$A6dde9bd7afc06231a1481ec56fd5768 && !$cfc7b4c8f12f119c2180693d0fa61648 && $type != "stb" && $action != "set_fav" && file_exists("TMP_DIRstalker_" . md5($mac)))) {
        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `mag_devices` t1 INNER JOIN `users` t2 ON t2.id = t1.user_id WHERE t1.`mac` = '%s' LIMIT 1", $mac);
        if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0) {
            $E574ed349c1c464172b5a4221afe809e = $f566700a43ee8e1f0412fe10fbdf03df->f1ed191d78470660edFF4a007696Bc1f();
            $E574ed349c1c464172b5a4221afe809e["allowed_ips"] = json_decode($E574ed349c1c464172b5a4221afe809e["allowed_ips"], true);
            if (!($E574ed349c1c464172b5a4221afe809e["admin_enabled"] == 0 || $E574ed349c1c464172b5a4221afe809e["enabled"] == 0)) {
                if (!(!empty($E574ed349c1c464172b5a4221afe809e["exp_date"]) && time() > $E574ed349c1c464172b5a4221afe809e["exp_date"])) {
                    if (!(!empty($E574ed349c1c464172b5a4221afe809e["allowed_ips"]) && !in_array($realIP, array_map("gethostbyname", $E574ed349c1c464172b5a4221afe809e["allowed_ips"])))) {
                        if (!$cfc7b4c8f12f119c2180693d0fa61648) {
                            goto A10e1b903050ad34be0afdb2c57473f7;
                        }
                        $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `mag_devices` SET `ver` = '%s' WHERE `mag_id` = '%d'", $ver, $E574ed349c1c464172b5a4221afe809e["mag_id"]);
                        if (!(!empty(A78bf8D35765bE2408c50712cE7a43aD::$settings["allowed_stb_types"]) && !in_array(strtolower($stbType), a78BF8D35765BE2408C50712CE7A43Ad::$settings["allowed_stb_types"]))) {
                            if (!($E574ed349c1c464172b5a4221afe809e["lock_device"] == 1 && !empty($E574ed349c1c464172b5a4221afe809e["sn"]) && $E574ed349c1c464172b5a4221afe809e["sn"] !== $magSn)) {
                                if (!($E574ed349c1c464172b5a4221afe809e["lock_device"] == 1 && !empty($E574ed349c1c464172b5a4221afe809e["device_id"]) && $E574ed349c1c464172b5a4221afe809e["device_id"] !== $deviceId)) {
                                    if (!($E574ed349c1c464172b5a4221afe809e["lock_device"] == 1 && !empty($E574ed349c1c464172b5a4221afe809e["device_id2"]) && $E574ed349c1c464172b5a4221afe809e["device_id2"] !== $deviceId2)) {
                                        if (!($E574ed349c1c464172b5a4221afe809e["lock_device"] == 1 && !empty($E574ed349c1c464172b5a4221afe809e["hw_version"]) && $E574ed349c1c464172b5a4221afe809e["hw_version"] !== $hwVersion)) {
                                            if (!(!empty(A78BF8D35765be2408C50712Ce7A43AD::$settings["stalker_lock_images"]) && !in_array($ver, A78bF8D35765bE2408c50712CE7a43aD::$settings["stalker_lock_images"]))) {
                                                $ded15b7e9c47ec5a3dea3c69332153c8 = new Ea991Ba3ec74F0Fb90Acc94C2d2dE518(GEOIP2_FILENAME);
                                                $A75f2436a5614184bfe3442ddd050ec5 = $ded15b7e9c47ec5a3dea3c69332153c8->c6A76952b4cef18F3C98C0E6A9Dd1274($realIP)["registered_country"]["iso_code"];
                                                $ded15b7e9c47ec5a3dea3c69332153c8->close();
                                                if (empty($A75f2436a5614184bfe3442ddd050ec5)) {
                                                    goto eb684ba79560e6a58d57e89cf6a1afc3;
                                                }
                                                $ab59908f6050f752836a953eb8bb8e52 = !empty($E574ed349c1c464172b5a4221afe809e["forced_country"]) ? true : false;
                                                if (!($ab59908f6050f752836a953eb8bb8e52 && $E574ed349c1c464172b5a4221afe809e["forced_country"] != "ALL" && $A75f2436a5614184bfe3442ddd050ec5 != $E574ed349c1c464172b5a4221afe809e["forced_country"] || !$ab59908f6050f752836a953eb8bb8e52 && !in_array("ALL", a78bF8d35765Be2408C50712ce7a43Ad::$settings["allow_countries"]) && !in_array($A75f2436a5614184bfe3442ddd050ec5, a78bF8D35765bE2408C50712CE7a43aD::$settings["allow_countries"]))) {
                                                    eb684ba79560e6a58d57e89cf6a1afc3:
                                                    $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `mag_devices` SET `ip` = '%s',`stb_type` = '%s',`sn` = '%s',`ver` = '%s',`image_version` = '%s',`device_id` = '%s',`device_id2` = '%s',`hw_version` = '%s' WHERE `mag_id` = '%d'", $realIP, htmlentities($stbType), htmlentities($magSn), htmlentities($ver), htmlentities($imageVer), htmlentities($deviceId), htmlentities($deviceId2), htmlentities($hwVersion), $E574ed349c1c464172b5a4221afe809e["mag_id"]);
                                                    A10e1b903050ad34be0afdb2c57473f7:
                                                    $E574ed349c1c464172b5a4221afe809e["fav_channels"] = !empty($E574ed349c1c464172b5a4221afe809e["fav_channels"]) ? json_decode($E574ed349c1c464172b5a4221afe809e["fav_channels"], true) : array();
                                                    if (!empty($E574ed349c1c464172b5a4221afe809e["fav_channels"]["live"])) {
                                                        goto e12ced7271e7f860c033a5303f2d249b;
                                                    }
                                                    $E574ed349c1c464172b5a4221afe809e["fav_channels"]["live"] = array();
                                                    e12ced7271e7f860c033a5303f2d249b:
                                                    if (!empty($E574ed349c1c464172b5a4221afe809e["fav_channels"]["movie"])) {
                                                        goto a88af110f8dba0d30662b218aaaf37e7;
                                                    }
                                                    $E574ed349c1c464172b5a4221afe809e["fav_channels"]["movie"] = array();
                                                    a88af110f8dba0d30662b218aaaf37e7:
                                                    if (!empty($E574ed349c1c464172b5a4221afe809e["fav_channels"]["radio_streams"])) {
                                                        goto A6f07601ef8da79462d626b4a9b173bb;
                                                    }
                                                    $E574ed349c1c464172b5a4221afe809e["fav_channels"]["radio_streams"] = array();
                                                    A6f07601ef8da79462d626b4a9b173bb:
                                                    $E574ed349c1c464172b5a4221afe809e["get_profile_vars"] = $E574ed349c1c464172b5a4221afe809e;
                                                    unset($E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["use_embedded_settings"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["mag_id"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["user_id"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["ver"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["sn"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["device_id"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["device_id2"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["spdif_mode"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["mag_player"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["fav_channels"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["token"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["lock_device"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["member_id"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["username"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["exp_date"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["admin_enabled"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["enabled"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["admin_notes"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["reseller_notes"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["bouquet"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["max_connections"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["is_restreamer"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["allowed_ips"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["allowed_ua"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["is_trial"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["created_at"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["created_by"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["pair_id"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["is_mag"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["is_e2"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["force_server_id"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["is_isplock"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["as_number"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["isp_desc"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["forced_country"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["is_stalker"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["bypass_ua"], $E574ed349c1c464172b5a4221afe809e["get_profile_vars"]["expires"]);
                                                    $E574ed349c1c464172b5a4221afe809e["mag_player"] = trim($E574ed349c1c464172b5a4221afe809e["mag_player"]);
                                                    file_put_contents("TMP_DIRstalker_" . md5($mac), json_encode($E574ed349c1c464172b5a4221afe809e));
                                                    return $E574ed349c1c464172b5a4221afe809e;
                                                }
                                                return false;
                                            }
                                            return false;
                                        }
                                        return false;
                                    }
                                    return false;
                                }
                                return false;
                            }
                            return false;
                        }
                        return false;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
        file_put_contents("TMP_DIRstalker_" . md5($mac), json_encode(array()));
        return false;
    }
    $a4b23a5f1ec2a1b113ea488d60c770d8 = json_decode(file_get_contents("TMP_DIRstalker_" . md5($mac)), true);
    return empty($a4b23a5f1ec2a1b113ea488d60c770d8) ? false : $a4b23a5f1ec2a1b113ea488d60c770d8;
}
function b303f4b9bCFA8d2FfC2Ae41c5d2aA387($a28758c1ab974badfc544e11aaf19a57 = null) {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    if (is_string($a28758c1ab974badfc544e11aaf19a57)) {
        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.* FROM `stream_categories` t1 WHERE t1.category_type = '%s' GROUP BY t1.id ORDER BY t1.cat_order ASC", $a28758c1ab974badfc544e11aaf19a57);
        goto C951fe738f03d604f9e48ec14fc4a677;
    }
    $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.* FROM `stream_categories` t1 ORDER BY t1.cat_order ASC");
    C951fe738f03d604f9e48ec14fc4a677:
    return $f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0 ? $f566700a43ee8e1f0412fe10fbdf03df->c126fd559932F625Cdf6098d86c63880(true, "id") : array();
}
function AfFb052CcA396818D81004fF99Db49AA() {
    return substr(md5(A78bf8D35765BE2408c50712CE7a43AD::$settings["unique_id"]), 0, 15);
}

function Gen_User_Playlist($userID, $type, $output_file_ext = "ts", $dc26923f689872c2291d72d47eb689c9 = false) {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    if (!empty($type)) {
        if (!($output_file_ext == "mpegts")) {
            $output_file_ext = "ts";
        }
        if (!($output_file_ext == "hls")) {
            $output_file_ext = "m3u8";
        }
        if (empty($output_file_ext)) {
            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.output_ext FROM `access_output` t1 INNER JOIN `devices` t2 ON t2.default_output = t1.access_output_id AND `device_key` = '%s'", $type);
        } else {
            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.output_ext FROM `access_output` t1 WHERE `output_key` = '%s'", $output_file_ext);
        }
        if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() <= 0)) {
            $ef5e5003fbec0abe0a64a7638470e9fd = $f566700a43ee8e1f0412fe10fbdf03df->b98CE8b3899e362093173CC5eB4146b9();
            $a8df9f055e91a1e9240230b69af85555 = cD89785224751cCA8017139dAF9E891e::E5550592AA298dD1d5ee59cdcE063a12($userID, null, null, true, true, false);
            if (!empty($a8df9f055e91a1e9240230b69af85555)) {
                if (!(!empty($a8df9f055e91a1e9240230b69af85555["exp_date"]) && time() >= $a8df9f055e91a1e9240230b69af85555["exp_date"])) {
                    if (A78Bf8d35765Be2408C50712ce7A43Ad::$settings["use_mdomain_in_lists"] == 1) {
                        $B6e64514a7c403d6db2d2ba8fa6fc2cb = a78BF8D35765bE2408C50712ce7A43ad::$StreamingServers[SERVER_ID]["site_url"];
                    } else {
                        list($C67d267db947e49f6df4c2c8f1f3a7e8, $B9037608c0d62641e46acd9b3d50eee8) = explode(":", $_SERVER["HTTP_HOST"]);
                        $B6e64514a7c403d6db2d2ba8fa6fc2cb = a78bF8d35765BE2408c50712cE7a43Ad::$StreamingServers[SERVER_ID]["server_protocol"] . "://" . $C67d267db947e49f6df4c2c8f1f3a7e8 . ":" . a78Bf8D35765Be2408c50712ce7a43aD::$StreamingServers[SERVER_ID]["request_port"] . "/";
                    }
                    $f53d081795585cc3a4de84113ceb7f31 = array();
                    if (!($output_file_ext == "rtmp")) {
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.id,t2.server_id FROM `streams` t1 INNER JOIN `streams_sys` t2 ON t2.stream_id = t1.id WHERE t1.rtmp_output = 1");
                        $f53d081795585cc3a4de84113ceb7f31 = $f566700a43ee8e1f0412fe10fbdf03df->c126fd559932F625CdF6098D86C63880(true, "id", false, "server_id");
                    }
                    if (!empty($ef5e5003fbec0abe0a64a7638470e9fd)) {
                        $ef5e5003fbec0abe0a64a7638470e9fd = "ts";
                    }
                    $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.*,t2.* FROM `devices` t1 LEFT JOIN `access_output` t2 ON t2.access_output_id = t1.default_output WHERE t1.device_key = '%s' LIMIT 1", $type);
                    if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0)) {
                        return false;
                    }
                    $ef2191c41d898dd4d2c297b9115d985d = $f566700a43ee8e1f0412fe10fbdf03df->F1ED191D78470660EDFF4A007696bC1F();
                    $d76067cf9572f7a6691c85c12faf2a29 = '';
                    if (empty($a8df9f055e91a1e9240230b69af85555["series_ids"])) {
                        $deff942ee62f1e5c2c16d11aee464729 = A78BF8d35765be2408c50712ce7a43aD::DcA7Aa6Db7C4ce371e41571a19bcE930();
                        foreach ($deff942ee62f1e5c2c16d11aee464729 as $acb1d10773fb0d1b6ac8cf2c16ecf1b5 => $A0766c7ec9b7cbc336d730454514b34f) {
                            if (in_array($acb1d10773fb0d1b6ac8cf2c16ecf1b5, $a8df9f055e91a1e9240230b69af85555["series_ids"])) {
                                foreach ($A0766c7ec9b7cbc336d730454514b34f["series_data"] as $c59070c3eab15fea2abe4546ccf476de => $E86ff017778d0dc804add84ab1be9052) {
                                    $e831c6d2f20288c01902323cccc3733a = 0;
                                    foreach ($E86ff017778d0dc804add84ab1be9052 as $ba85d77d367dcebfcc2a3db9e83bb581 => $a14a8f906639aa7f5509518ff935b8f0) {
                                        $movie_properties = A78bf8d35765Be2408c50712CE7a43aD::CAdeb9125b2E81B183688842C5Ac3ad7($ba85d77d367dcebfcc2a3db9e83bb581);
                                        $a14a8f906639aa7f5509518ff935b8f0["live"] = 0;
                                        if (a78bF8D35765Be2408c50712Ce7A43AD::$settings["series_custom_name"] == 0) {
                                            $a14a8f906639aa7f5509518ff935b8f0["stream_display_name"] = $A0766c7ec9b7cbc336d730454514b34f["title"] . " S" . sprintf("%02d", $c59070c3eab15fea2abe4546ccf476de) . " E" . sprintf("%02d", ++$e831c6d2f20288c01902323cccc3733a);
                                        } else {
                                            $a14a8f906639aa7f5509518ff935b8f0["stream_display_name"] = $A0766c7ec9b7cbc336d730454514b34f["title"] . " S" . sprintf("%02d", $c59070c3eab15fea2abe4546ccf476de) . " {$a14a8f906639aa7f5509518ff935b8f0["stream_display_name"]}";
                                        }
                                        $a14a8f906639aa7f5509518ff935b8f0["movie_propeties"] = array("movie_image" => !empty($movie_properties["movie_image"]) ? $movie_properties["movie_image"] : $A0766c7ec9b7cbc336d730454514b34f["cover"]);
                                        $a14a8f906639aa7f5509518ff935b8f0["type_output"] = "series";
                                        $a14a8f906639aa7f5509518ff935b8f0["category_name"] = $A0766c7ec9b7cbc336d730454514b34f["category_name"];
                                        $a14a8f906639aa7f5509518ff935b8f0["id"] = $ba85d77d367dcebfcc2a3db9e83bb581;
                                        $a8df9f055e91a1e9240230b69af85555["channels"][$ba85d77d367dcebfcc2a3db9e83bb581] = $a14a8f906639aa7f5509518ff935b8f0;
                                    }
                                }
                            }
                        }
                    }
                    if ($type == "starlivev5") {
                        $Edee2355c9dc9d29534485158df8e981 = array();
                        $Edee2355c9dc9d29534485158df8e981["iptvstreams_list"] = array();
                        $Edee2355c9dc9d29534485158df8e981["iptvstreams_list"]["@version"] = 1;
                        $Edee2355c9dc9d29534485158df8e981["iptvstreams_list"]["group"] = array();
                        $Edee2355c9dc9d29534485158df8e981["iptvstreams_list"]["group"]["name"] = "IPTV";
                        $Edee2355c9dc9d29534485158df8e981["iptvstreams_list"]["group"]["channel"] = array();
                        foreach ($a8df9f055e91a1e9240230b69af85555["channels"] as $ffb1e0970b62b01f46c2e57f2cded6c2) {
                            $f3f2a9f7d64ad754f9f888f441df853a = !isset($ffb1e0970b62b01f46c2e57f2cded6c2["movie_propeties"]) ? A78Bf8d35765be2408C50712cE7a43ad::CaDeb9125b2E81B183688842c5AC3Ad7($channel["id"]) : $ffb1e0970b62b01f46c2e57f2cded6c2["movie_propeties"];
                            if (!empty($ffb1e0970b62b01f46c2e57f2cded6c2["stream_source"])) {
                                $e3539ad64f4d9fc6c2e465986c622369 = str_replace(" ", "%20", json_decode($ffb1e0970b62b01f46c2e57f2cded6c2["stream_source"], true)[0]);
                                $C57b49d586c542242fa9bb22afa04cf8 = !empty($f3f2a9f7d64ad754f9f888f441df853a["movie_image"]) ? $f3f2a9f7d64ad754f9f888f441df853a["movie_image"] : $ffb1e0970b62b01f46c2e57f2cded6c2["stream_icon"];
                                goto C52122cd0a02f17a5c718ef1ee1b3f67;
                            }
                            $e3539ad64f4d9fc6c2e465986c622369 = $B6e64514a7c403d6db2d2ba8fa6fc2cb . "{$ffb1e0970b62b01f46c2e57f2cded6c2["type_output"]}/{$a8df9f055e91a1e9240230b69af85555["username"]}/{$a8df9f055e91a1e9240230b69af85555["password"]}/";
                            if ($ffb1e0970b62b01f46c2e57f2cded6c2["live"] == 0) {
                                $e3539ad64f4d9fc6c2e465986c622369 .= $ffb1e0970b62b01f46c2e57f2cded6c2["id"] . "." . dc53Ae228df72D4C140Fda7FD5E7e0Be($ffb1e0970b62b01f46c2e57f2cded6c2["target_container"]);
                                if (empty($f3f2a9f7d64ad754f9f888f441df853a["movie_image"])) {
                                    goto Addd4567e92abacbf75e399baf55e1f3;
                                }
                                $C57b49d586c542242fa9bb22afa04cf8 = $f3f2a9f7d64ad754f9f888f441df853a["movie_image"];
                                Addd4567e92abacbf75e399baf55e1f3:
                                goto c8303b0756d822e343b9686f41e601e4;
                            }
                            $e3539ad64f4d9fc6c2e465986c622369 .= $ffb1e0970b62b01f46c2e57f2cded6c2["id"] . "." . $ef5e5003fbec0abe0a64a7638470e9fd;
                            $C57b49d586c542242fa9bb22afa04cf8 = $ffb1e0970b62b01f46c2e57f2cded6c2["stream_icon"];
                            c8303b0756d822e343b9686f41e601e4:
                            C52122cd0a02f17a5c718ef1ee1b3f67:
                            $channel = array();
                            $channel["name"] = $ffb1e0970b62b01f46c2e57f2cded6c2["stream_display_name"];
                            $C57b49d586c542242fa9bb22afa04cf8 = '';
                            $channel["icon"] = $C57b49d586c542242fa9bb22afa04cf8;
                            $channel["stream_url"] = $e3539ad64f4d9fc6c2e465986c622369;
                            $channel["stream_type"] = 0;
                            $Edee2355c9dc9d29534485158df8e981["iptvstreams_list"]["group"]["channel"][] = $channel;
                        }
                        $d76067cf9572f7a6691c85c12faf2a29 = json_encode((object) $Edee2355c9dc9d29534485158df8e981);
                        goto B69945d8e2cdea9f1ecb0fc45b1f96a3;
                    }
                    if (empty($ef2191c41d898dd4d2c297b9115d985d["device_header"])) {
                        goto e0f27ef2f804ec8b79518118404559db;
                    }
                    $d76067cf9572f7a6691c85c12faf2a29 = str_replace(array("{BOUQUET_NAME}", "{USERNAME}", "{PASSWORD}", "{SERVER_URL}", "{OUTPUT_KEY}"), array(a78Bf8d35765bE2408c50712cE7a43aD::$settings["bouquet_name"], $a8df9f055e91a1e9240230b69af85555["username"], $a8df9f055e91a1e9240230b69af85555["password"], $B6e64514a7c403d6db2d2ba8fa6fc2cb, $output_file_ext), $ef2191c41d898dd4d2c297b9115d985d["device_header"]) . "\n";
                    e0f27ef2f804ec8b79518118404559db:
                    if (empty($ef2191c41d898dd4d2c297b9115d985d["device_conf"])) {
                        goto e2eaed023f67c5152ffa086db60fd218;
                    }
                    if (preg_match("/\\{URL\\#(.*?)\\}/", $ef2191c41d898dd4d2c297b9115d985d["device_conf"], $matches)) {
                        $e5cb656483e7536471dc8d1c0bab1ed0 = str_split($matches[1]);
                        $e67cb10c8a14e132feaa115160c239e9 = $matches[0];
                        goto C12a8d2b7f6732a9db1ed111ecf8548a;
                    }
                    $e5cb656483e7536471dc8d1c0bab1ed0 = array();
                    $e67cb10c8a14e132feaa115160c239e9 = "{URL}";
                    C12a8d2b7f6732a9db1ed111ecf8548a:
                    foreach ($a8df9f055e91a1e9240230b69af85555["channels"] as $channel) {
                        $f3f2a9f7d64ad754f9f888f441df853a = !isset($channel["movie_propeties"]) ? A78Bf8D35765Be2408c50712ce7A43ad::CADEb9125B2E81b183688842c5AC3AD7($channel["id"]) : $channel["movie_propeties"];
                        if (!empty($channel["stream_source"])) {
                            $e3539ad64f4d9fc6c2e465986c622369 = str_replace(" ", "%20", json_decode($channel["stream_source"], true)[0]);
                            $C57b49d586c542242fa9bb22afa04cf8 = !empty($f3f2a9f7d64ad754f9f888f441df853a["movie_image"]) ? $f3f2a9f7d64ad754f9f888f441df853a["movie_image"] : $channel["stream_icon"];
                            goto dcc5925e20dbb9fe7a8978922e22fbb0;
                        }
                        if ($channel["live"] == 0) {
                            $e3539ad64f4d9fc6c2e465986c622369 = $B6e64514a7c403d6db2d2ba8fa6fc2cb . "{$channel["type_output"]}/{$a8df9f055e91a1e9240230b69af85555["username"]}/{$a8df9f055e91a1e9240230b69af85555["password"]}/{$channel["id"]}." . Dc53aE228dF72D4C140FDa7Fd5E7e0bE($channel["target_container"]);
                            if (empty($f3f2a9f7d64ad754f9f888f441df853a["movie_image"])) {
                                goto Dad2d00d1970077c3f0eae4d246626f5;
                            }
                            $C57b49d586c542242fa9bb22afa04cf8 = $f3f2a9f7d64ad754f9f888f441df853a["movie_image"];
                            Dad2d00d1970077c3f0eae4d246626f5:
                            goto a2fc779e36bed5687953a97912601ac0;
                        }
                        if ($output_file_ext != "rtmp" || !array_key_exists($channel["id"], $f53d081795585cc3a4de84113ceb7f31)) {
                            if (!file_exists("TMP_DIRnew_rewrite") || $ef5e5003fbec0abe0a64a7638470e9fd != "ts") {
                                $e3539ad64f4d9fc6c2e465986c622369 = $B6e64514a7c403d6db2d2ba8fa6fc2cb . "{$channel["type_output"]}/{$a8df9f055e91a1e9240230b69af85555["username"]}/{$a8df9f055e91a1e9240230b69af85555["password"]}/{$channel["id"]}.{$ef5e5003fbec0abe0a64a7638470e9fd}";
                                goto eb01a63ff8469a5088c7edfc7391de87;
                            }
                            $e3539ad64f4d9fc6c2e465986c622369 = $B6e64514a7c403d6db2d2ba8fa6fc2cb . "{$a8df9f055e91a1e9240230b69af85555["username"]}/{$a8df9f055e91a1e9240230b69af85555["password"]}/{$channel["id"]}";
                            eb01a63ff8469a5088c7edfc7391de87:
                            goto be057225076d5505b5d579abb5d2c939;
                        }
                        $e3215fa97db12812ee074d6c110dea4b = array_values(array_keys($f53d081795585cc3a4de84113ceb7f31[$channel["id"]]));
                        if (in_array($a8df9f055e91a1e9240230b69af85555["force_server_id"], $e3215fa97db12812ee074d6c110dea4b)) {
                            $e951d0b9610ba3624d06def5a541cb17 = $a8df9f055e91a1e9240230b69af85555["force_server_id"];
                            goto D5534d0f8c77b03d715fa5e23bbc60e3;
                        }
                        if (A78bf8D35765be2408c50712ce7a43ad::$settings["rtmp_random"] == 1) {
                            $e951d0b9610ba3624d06def5a541cb17 = $e3215fa97db12812ee074d6c110dea4b[array_rand($e3215fa97db12812ee074d6c110dea4b, 1)];
                            goto F8c695f5ee4f82952258ff819b620064;
                        }
                        $e951d0b9610ba3624d06def5a541cb17 = $e3215fa97db12812ee074d6c110dea4b[0];
                        F8c695f5ee4f82952258ff819b620064:
                        D5534d0f8c77b03d715fa5e23bbc60e3:
                        $e3539ad64f4d9fc6c2e465986c622369 = a78bF8d35765be2408C50712ce7A43Ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["rtmp_server"] . "{$channel["id"]}?username={$a8df9f055e91a1e9240230b69af85555["username"]}&password={$a8df9f055e91a1e9240230b69af85555["password"]}";
                        be057225076d5505b5d579abb5d2c939:
                        $C57b49d586c542242fa9bb22afa04cf8 = $channel["stream_icon"];
                        a2fc779e36bed5687953a97912601ac0:
                        dcc5925e20dbb9fe7a8978922e22fbb0:
                        $aaf6a34b884488dd481a40d77442e482 = $channel["live"] == 1 ? 1 : 4097;
                        $a98ed0c1a9452fc6117e23a262acc7a9 = !empty($channel["custom_sid"]) ? $channel["custom_sid"] : ":0:1:0:0:0:0:0:0:0:";
                        $d76067cf9572f7a6691c85c12faf2a29 .= str_replace(array($e67cb10c8a14e132feaa115160c239e9, "{ESR_ID}", "{SID}", "{CHANNEL_NAME}", "{CHANNEL_ID}", "{CATEGORY}", "{CHANNEL_ICON}"), array(str_replace($e5cb656483e7536471dc8d1c0bab1ed0, array_map("urlencode", $e5cb656483e7536471dc8d1c0bab1ed0), $e3539ad64f4d9fc6c2e465986c622369), $aaf6a34b884488dd481a40d77442e482, $a98ed0c1a9452fc6117e23a262acc7a9, $channel["stream_display_name"], $channel["channel_id"], $channel["category_name"], $C57b49d586c542242fa9bb22afa04cf8), $ef2191c41d898dd4d2c297b9115d985d["device_conf"]) . "\r\n";
                    }
                    $d76067cf9572f7a6691c85c12faf2a29 .= $ef2191c41d898dd4d2c297b9115d985d["device_footer"];
                    $d76067cf9572f7a6691c85c12faf2a29 = trim($d76067cf9572f7a6691c85c12faf2a29);
                    e2eaed023f67c5152ffa086db60fd218:
                    B69945d8e2cdea9f1ecb0fc45b1f96a3:
                    if (!($dc26923f689872c2291d72d47eb689c9 === true)) {
                        return $d76067cf9572f7a6691c85c12faf2a29;
                    }
                    header("Content-Description: File Transfer");
                    header("Content-Type: application/octet-stream");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate");
                    header("Pragma: public");
                    header("Content-Disposition: attachment; filename=\"" . str_replace("{USERNAME}", $a8df9f055e91a1e9240230b69af85555["username"], $ef2191c41d898dd4d2c297b9115d985d["device_filename"]) . "\"");
                    header("Content-Length: " . strlen($d76067cf9572f7a6691c85c12faf2a29));
                    echo $d76067cf9572f7a6691c85c12faf2a29;
                    die;
                }
                return false;
            }
            return false;
        }
        return false;
    }
    return false;
}
function dc53ae228Df72d4C140FdA7FD5e7e0bE($F7c4b84b7a2fba7bcaf5f84d6e1fc2a0, $Ecfa3d1f4289bd1faf239b9e11f62c15 = false) {
    $dc6dc3b07e01c13972dd7a2ce0e2dc9b = json_decode($F7c4b84b7a2fba7bcaf5f84d6e1fc2a0, true);
    if (is_array($dc6dc3b07e01c13972dd7a2ce0e2dc9b)) {
        $F7c4b84b7a2fba7bcaf5f84d6e1fc2a0 = array_map("strtolower", $dc6dc3b07e01c13972dd7a2ce0e2dc9b);
        Ca16165f5c2ab82407ed694cf92e1b6f:
        $a0f777034e80c9f10573d3a75b8b985e = $Ecfa3d1f4289bd1faf239b9e11f62c15 ? a78bF8d35765bE2408C50712Ce7A43aD::$settings["stalker_container_priority"] : a78bF8d35765be2408C50712cE7a43AD::$settings["gen_container_priority"];
        if (!is_array($a0f777034e80c9f10573d3a75b8b985e)) {
            goto E54e78b3d871058282dfec89c7621fb6;
        }
        foreach ($a0f777034e80c9f10573d3a75b8b985e as $E2e6656d8b1675f70c487f89e4f27a3b) {
            if (!in_array($E2e6656d8b1675f70c487f89e4f27a3b, $F7c4b84b7a2fba7bcaf5f84d6e1fc2a0)) {
            }
            return $E2e6656d8b1675f70c487f89e4f27a3b;
        }
        E54e78b3d871058282dfec89c7621fb6:
        return $F7c4b84b7a2fba7bcaf5f84d6e1fc2a0[0];
    }
    return $F7c4b84b7a2fba7bcaf5f84d6e1fc2a0;
}
function f0bb8dBEaB7fb0ECcCc0a73980dBF47A($serverId, $streamId = null) {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    $queryCondition = '';

    if (empty($streamId)) {
        $queryCondition = '';
    } else {
        $queryCondition = "WHERE t1.server_id = '" . intval($streamId) . "'";
    }

    switch ($serverId) {
        case "open":
            $query = "SELECT t1.*, t2.*, t3.*, t5.bitrate 
                      FROM `user_activity_now` t1 
                      LEFT JOIN `users` t2 ON t2.id = t1.user_id 
                      LEFT JOIN `streams` t3 ON t3.id = t1.stream_id 
                      LEFT JOIN `streams_sys` t5 ON t5.stream_id = t1.stream_id AND t5.server_id = t1.server_id 
                      {$queryCondition} 
                      ORDER BY t1.activity_id ASC";
            break;

        case "closed":
            $query = "SELECT t1.*, t2.*, t3.*, t5.bitrate 
                      FROM `user_activity` t1 
                      LEFT JOIN `users` t2 ON t2.id = t1.user_id 
                      LEFT JOIN `streams` t3 ON t3.id = t1.stream_id 
                      LEFT JOIN `streams_sys` t5 ON t5.stream_id = t1.stream_id AND t5.server_id = t1.server_id 
                      {$queryCondition} 
                      ORDER BY t1.activity_id ASC";
            break;
    }

    $f566700a43ee8e1f0412fe10fbdf03df->query($query);

    return $f566700a43ee8e1f0412fe10fbdf03df->C126fD559932F625CdF6098D86c63880(true, "user_id", false);
}
function ec2283305A3A0ABb64fab98987118fb7() {
    if (!file_exists("TMP_DIRcrontab_refresh")) {
        $e5e98a959b8f162327993301f8322de2 = scandir(CRON_PATH);
        $C7a036331f31a9fa57fe3f8f68b5fc28 = array();
        foreach ($e5e98a959b8f162327993301f8322de2 as $Bdccf61a916022cc88d9f6f40f2e017d) {
            $b8426ef38804dd0b0fe9d5ed01afdf3e = CRON_PATH . $Bdccf61a916022cc88d9f6f40f2e017d;
            if (is_file($b8426ef38804dd0b0fe9d5ed01afdf3e)) {
                if (!(pathinfo($b8426ef38804dd0b0fe9d5ed01afdf3e, PATHINFO_EXTENSION) != "php")) {
                    if ($Bdccf61a916022cc88d9f6f40f2e017d != "epg.php") {
                        $Af218a53429705d6e319475a2185cd90 = "*/1 * * * *";
                        goto Db904413277b88b1c364bb547100ef5c;
                    }
                    $Af218a53429705d6e319475a2185cd90 = "0 1 * * *";
                    Db904413277b88b1c364bb547100ef5c:
                    $C7a036331f31a9fa57fe3f8f68b5fc28[] = "{$Af218a53429705d6e319475a2185cd90} " . PHP_BIN . " " . $b8426ef38804dd0b0fe9d5ed01afdf3e . " # Xtream-Codes IPTV Panel";
                    goto E51266b94c4da6e96f962ecb2aaab73e;
                }
                goto bd1f38014f47ae6ac7939ea778c93f0f;
            }
            E51266b94c4da6e96f962ecb2aaab73e:
            bd1f38014f47ae6ac7939ea778c93f0f:
        }
        $b201cbed374ba4e1d6c7c2705d56ca08 = trim(shell_exec("crontab -l"));
        if (!empty($b201cbed374ba4e1d6c7c2705d56ca08)) {
            $c021c29582e50e562166d105d561478a = explode("\n", $b201cbed374ba4e1d6c7c2705d56ca08);
            $c021c29582e50e562166d105d561478a = array_map("trim", $c021c29582e50e562166d105d561478a);
            if (!($c021c29582e50e562166d105d561478a == $C7a036331f31a9fa57fe3f8f68b5fc28)) {
                $A029b77634bf5f67a52c7d5b31aed706 = count($c021c29582e50e562166d105d561478a);
                $C48e0083a9caa391609a3c645a2ec889 = 0;
                Ecdbbe5a027e089bbfa832d3084ec6b7:
                if (!($C48e0083a9caa391609a3c645a2ec889 < $A029b77634bf5f67a52c7d5b31aed706)) {
                    foreach ($C7a036331f31a9fa57fe3f8f68b5fc28 as $C6866a0d5682188bf2fae0c2ec835d28) {
                        array_push($c021c29582e50e562166d105d561478a, $C6866a0d5682188bf2fae0c2ec835d28);
                    }
                    goto ccd34a9e8eba654d306c7f752c377adc;
                }
                if (!stripos($c021c29582e50e562166d105d561478a[$C48e0083a9caa391609a3c645a2ec889], CRON_PATH)) {
                    goto Bbf78c0a2e4176122f99b8c7501528ec;
                }
                unset($c021c29582e50e562166d105d561478a[$C48e0083a9caa391609a3c645a2ec889]);
                Bbf78c0a2e4176122f99b8c7501528ec:
                $C48e0083a9caa391609a3c645a2ec889++;
                goto Ecdbbe5a027e089bbfa832d3084ec6b7;
            }
            file_put_contents("TMP_DIRcrontab_refresh", 1);
            return true;
        }
        $c021c29582e50e562166d105d561478a = $C7a036331f31a9fa57fe3f8f68b5fc28;
        ccd34a9e8eba654d306c7f752c377adc:
        shell_exec("crontab -r");
        $E40c9ae95432e1c473499c726c93b87d = tempnam("/tmp", "crontab");
        $fb1d4f6290dabf126bb2eb152b0eb565 = fopen($E40c9ae95432e1c473499c726c93b87d, "w");
        fwrite($fb1d4f6290dabf126bb2eb152b0eb565, implode("\r\n", $c021c29582e50e562166d105d561478a) . "\r\n");
        fclose($fb1d4f6290dabf126bb2eb152b0eb565);
        shell_exec("crontab {$E40c9ae95432e1c473499c726c93b87d}");
        @unlink($E40c9ae95432e1c473499c726c93b87d);
        file_put_contents("TMP_DIRcrontab_refresh", 1);

        return;
    }
    return false;
}
function Ce15043404Aa3e950Fc9c9dd8bc0325A($d408321c3d2f36c26d485366e0885d32, $D3b7378ea39c39f9d734834bc785a87e, $D3c32abd0d3bffc3578aff155e22d728) {
    global $f566700a43ee8e1f0412fe10fbdf03df;
    $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `{$d408321c3d2f36c26d485366e0885d32}` WHERE `{$D3b7378ea39c39f9d734834bc785a87e}` = '%s'", $D3c32abd0d3bffc3578aff155e22d728);
    if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0)) {
        return false;
    }
    return true;
}
function getUpTime() {
    if (!(file_exists("/proc/uptime") and is_readable("/proc/uptime"))) {
        return "";
    }
    $data = explode(" ", file_get_contents("/proc/uptime"));
    return UpTimeConvert(intval($data[0]));
}
function UpTimeConvert($time) {
    $minutesH = 60;
    $secondsH = 3600;
    $minutesD = 86400;
    $days = (int) floor($time / $minutesD);
    $intDays = $time % $minutesD;
    $hours = (int) floor($intDays / $secondsH);
    $intHours = $intDays % $secondsH;
    $minutes = (int) floor($intHours / $minutesH);
    $intSeconds = $intHours % $minutesH;
    $seconds = (int) ceil($intSeconds);
    $uptime = '';

    if ($days != 0) {
        $uptime .= "{$days}d ";
    }
    if ($hours != 0) {
        $uptime .= "{$hours}h ";
    }
    if ($minutes != 0) {
        $uptime .= "{$minutes}m ";
    }
    $uptime .= "{$seconds}s";

    return $uptime;
}
