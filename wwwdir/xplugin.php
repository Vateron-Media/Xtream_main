<?php

require "init.php";
header("Content-Type: application/json");
$B626d33e939f0dd9b6a026aa3f8c87a3 = $_SERVER["REMOTE_ADDR"];
$userAgent = trim($_SERVER["HTTP_USER_AGENT"]);
if (!(!empty(A78bf8d35765Be2408C50712cE7A43aD::$request["action"]) && A78Bf8d35765be2408c50712CE7a43AD::$request["action"] == "gen_mac" && !empty(a78bF8d35765Be2408C50712ce7a43ad::$request["pversion"]))) {
    if (!(!empty(A78BF8d35765bE2408C50712cE7a43Ad::$request["action"]) && a78bF8d35765Be2408C50712CE7a43Ad::$request["action"] == "auth")) {
        if (!empty(A78bf8D35765bE2408C50712Ce7a43Ad::$request["token"])) {
            $accessToken = a78bf8D35765BE2408c50712cE7A43ad::$request["token"];
            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM enigma2_devices WHERE `token` = '%s' AND `public_ip` = '%s' AND `key_auth` = '%s' LIMIT 1", $accessToken, $B626d33e939f0dd9b6a026aa3f8c87a3, $userAgent);
            if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() <= 0)) {
                $ef2191c41d898dd4d2c297b9115d985d = $f566700a43ee8e1f0412fe10fbdf03df->F1eD191d78470660edFf4A007696bc1F();
                if (!(time() - $ef2191c41d898dd4d2c297b9115d985d["last_updated"] > $ef2191c41d898dd4d2c297b9115d985d["watchdog_timeout"] + 20)) {
                    $Efbabdfbd20db2470efbf8a713287c36 = isset(A78Bf8D35765BE2408c50712ce7a43AD::$request["page"]) ? A78bf8d35765bE2408c50712cE7A43ad::$request["page"] : '';
                    if (empty($Efbabdfbd20db2470efbf8a713287c36)) {
                        $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `enigma2_devices` SET `last_updated` = '%d',`rc` = '%d' WHERE `device_id` = '%d'", time(), A78Bf8d35765be2408C50712ce7A43aD::$request["rc"], $ef2191c41d898dd4d2c297b9115d985d["device_id"]);
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `enigma2_actions` WHERE `device_id` = '%d'", $ef2191c41d898dd4d2c297b9115d985d["device_id"]);
                        $C2eef5835abdc711ef2e0b2a24dc4e46 = array();
                        if (!($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0)) {
                            goto cf01b8bc91ad0274d8753387de125ce4;
                        }
                        $Ce7729bc93110c2030dc45bb29c9f93f = $f566700a43ee8e1f0412fe10fbdf03df->f1eD191d78470660EDff4A007696bC1f();
                        if (!("message" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto Cee58a2cc8472969397cd41a7d822c83;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["message"] = array();
                        $C2eef5835abdc711ef2e0b2a24dc4e46["message"]["title"] = $Ce7729bc93110c2030dc45bb29c9f93f["command2"];
                        $C2eef5835abdc711ef2e0b2a24dc4e46["message"]["message"] = $Ce7729bc93110c2030dc45bb29c9f93f["command"];
                        Cee58a2cc8472969397cd41a7d822c83:
                        if (!("ssh" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto a51ffe87da1e4346145183b56a9d83f6;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["ssh"] = $Ce7729bc93110c2030dc45bb29c9f93f["command"];
                        a51ffe87da1e4346145183b56a9d83f6:
                        if (!("screen" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto A1d289bfce29d04b66185e05c43d40f1;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["screen"] = "1";
                        A1d289bfce29d04b66185e05c43d40f1:
                        if (!("reboot_gui" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto bc90e2970241bae59ca56956f2d9e429;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["reboot_gui"] = 1;
                        bc90e2970241bae59ca56956f2d9e429:
                        if (!("reboot" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto d2909a5cad7a88aac71b0285148e7886;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["reboot"] = 1;
                        d2909a5cad7a88aac71b0285148e7886:
                        if (!("update" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto A9327749be5670158cd0a477d689efc5;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["update"] = $Ce7729bc93110c2030dc45bb29c9f93f["command"];
                        A9327749be5670158cd0a477d689efc5:
                        if (!("block_ssh" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto Fcda9a0da3a6aaa780cfda1d814767f8;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["block_ssh"] = (int) $Ce7729bc93110c2030dc45bb29c9f93f["type"];
                        Fcda9a0da3a6aaa780cfda1d814767f8:
                        if (!("block_telnet" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto e56f7490273acd51fff07a63dad8ffb4;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["block_telnet"] = (int) $Ce7729bc93110c2030dc45bb29c9f93f["type"];
                        e56f7490273acd51fff07a63dad8ffb4:
                        if (!("block_ftp" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto b219a3409f5dea5b4f5620aef561e711;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["block_ftp"] = (int) $Ce7729bc93110c2030dc45bb29c9f93f["type"];
                        b219a3409f5dea5b4f5620aef561e711:
                        if (!("block_all" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto fa7f01aa573234252f14ddd66566dbeb;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["block_all"] = (int) $Ce7729bc93110c2030dc45bb29c9f93f["type"];
                        fa7f01aa573234252f14ddd66566dbeb:
                        if (!("block_plugin" == $Ce7729bc93110c2030dc45bb29c9f93f["key"])) {
                            goto Bf654ec5145e515532ee2ac1bfcd0c00;
                        }
                        $C2eef5835abdc711ef2e0b2a24dc4e46["block_plugin"] = (int) $Ce7729bc93110c2030dc45bb29c9f93f["type"];
                        Bf654ec5145e515532ee2ac1bfcd0c00:
                        $f566700a43ee8e1f0412fe10fbdf03df->query("DELETE FROM enigma2_actions where id = '%d'", $Ce7729bc93110c2030dc45bb29c9f93f["id"]);
                        cf01b8bc91ad0274d8753387de125ce4:
                        die(json_encode(array("valid" => true, "data" => $C2eef5835abdc711ef2e0b2a24dc4e46)));
                    }
                    if ($Efbabdfbd20db2470efbf8a713287c36 == "file") {
                        if (empty($_FILES["f"]["name"])) {
                            goto F0e0a2e77990418b147c75a8a4756db9;
                        }
                        if (!($_FILES["f"]["error"] == 0)) {
                            goto b7f668dff567b096a74031f08517ee08;
                        }
                        $Da45e9a4a377f8bd28389cf977565923 = strtolower($_FILES["f"]["tmp_name"]);
                        $a28758c1ab974badfc544e11aaf19a57 = a78Bf8d35765BE2408C50712ce7A43aD::$request["t"];
                        switch ($a28758c1ab974badfc544e11aaf19a57) {
                            case "screen":
                                move_uploaded_file($_FILES["f"]["tmp_name"], ENIGMA2_PLUGIN_DIR . $ef2191c41d898dd4d2c297b9115d985d["device_id"] . "_screen_" . time() . "_" . uniqid() . ".jpg");
                                goto b488e25340f3e0b5437dbf2a569034d1;
                        }
                        b488e25340f3e0b5437dbf2a569034d1:
                        b7f668dff567b096a74031f08517ee08:
                        F0e0a2e77990418b147c75a8a4756db9:
                        goto Caa7e997a59cf91177fe5be2e93c409b;
                    }
                    Caa7e997a59cf91177fe5be2e93c409b:
                    a5360c7a142d50608106d75d4ce0aa19:
                    // [PHPDeobfuscator] Implied script end
                    return;
                }
                die(json_encode(array("valid" => false)));
            }
            die(json_encode(array("valid" => false)));
        }
        die(json_encode(array("valid" => false)));
    }
    $mac = isset(A78bF8D35765BE2408C50712CE7a43aD::$request["mac"]) ? htmlentities(A78Bf8d35765BE2408C50712cE7A43Ad::$request["mac"]) : '';
    $A772ae3d339199a2063a8114463187e9 = isset(A78Bf8D35765be2408C50712ce7a43ad::$request["mmac"]) ? htmlentities(A78bf8d35765be2408c50712ce7A43aD::$request["mmac"]) : '';
    $fe0750f7aa30941e1e4cdf60bf6a717c = isset(a78Bf8d35765Be2408C50712ce7a43aD::$request["ip"]) ? htmlentities(a78bF8D35765BE2408C50712cE7A43AD::$request["ip"]) : '';
    $Ad76f953cd176710f1445b66d793955d = isset(A78BF8D35765be2408C50712CE7A43Ad::$request["version"]) ? htmlentities(A78Bf8D35765BE2408C50712ce7a43ad::$request["version"]) : '';
    $e001aedbc6e64693ba72fd0337a8fa76 = isset(A78Bf8D35765bE2408c50712ce7a43Ad::$request["type"]) ? htmlentities(A78bf8d35765BE2408c50712cE7A43AD::$request["type"]) : '';
    $db129c7b99cf0960c74f2c766ce8df9a = isset(A78Bf8D35765BE2408c50712Ce7a43aD::$request["pversion"]) ? htmlentities(A78bF8d35765BE2408c50712ce7a43Ad::$request["pversion"]) : '';
    $A8dcbd7c47482ea0777db7c412f03a4d = isset(A78Bf8d35765be2408C50712ce7a43ad::$request["lversion"]) ? base64_decode(A78BF8D35765bE2408C50712cE7A43Ad::$request["lversion"]) : '';
    $b37f0a028a0cad24cae4c9e61119f8de = !empty(a78bf8d35765Be2408C50712ce7A43AD::$request["dn"]) ? htmlentities(a78BF8d35765bE2408c50712CE7A43aD::$request["dn"]) : "-";
    $b5014e12f754ad55b57d1a8e17efe7b0 = !empty(A78bF8D35765Be2408c50712cE7A43aD::$request["cmac"]) ? htmlentities(strtoupper(a78BF8D35765bE2408C50712ce7A43AD::$request["cmac"])) : '';
    $f5cab1816ec764ef073063a4c9596cb6 = array();
    if (!($de0eb4ea8ae0aa5b5b8864529380cf22 = Cd89785224751cCa8017139daf9e891e::A2999eeDbe1Ff2D9cE52EF5311680cD4(array("device_id" => null, "mac" => strtoupper($mac))))) {
        goto c1dcb3f44e64195b2bdb46417f74dea1;
    }
    if (!($de0eb4ea8ae0aa5b5b8864529380cf22["enigma2"]["lock_device"] == 1)) {
        goto dee2031b96f1885960da09e8d58277c4;
    }
    if (!(!empty($de0eb4ea8ae0aa5b5b8864529380cf22["enigma2"]["modem_mac"]) && $de0eb4ea8ae0aa5b5b8864529380cf22["enigma2"]["modem_mac"] !== $A772ae3d339199a2063a8114463187e9)) {
        dee2031b96f1885960da09e8d58277c4:
        $accessToken = strtoupper(md5(uniqid(rand(), true)));
        $Fb012ef0b8c84139cf5b45c26d1a4d54 = mt_rand(60, 70);
        $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `enigma2_devices` SET `original_mac` = '%s',`dns` = '%s',`key_auth` = '%s',`lversion` = '%s',`watchdog_timeout` = '%d',`modem_mac` = '%s',`local_ip` = '%s',`public_ip` = '%s',`enigma_version` = '%s',`cpu` = '%s',`version` = '%s',`token` = '%s',`last_updated` = '%d' WHERE `device_id` = '%d'", $b5014e12f754ad55b57d1a8e17efe7b0, $b37f0a028a0cad24cae4c9e61119f8de, $userAgent, $A8dcbd7c47482ea0777db7c412f03a4d, $Fb012ef0b8c84139cf5b45c26d1a4d54, $A772ae3d339199a2063a8114463187e9, $fe0750f7aa30941e1e4cdf60bf6a717c, $B626d33e939f0dd9b6a026aa3f8c87a3, $Ad76f953cd176710f1445b66d793955d, $e001aedbc6e64693ba72fd0337a8fa76, $db129c7b99cf0960c74f2c766ce8df9a, $accessToken, time(), $de0eb4ea8ae0aa5b5b8864529380cf22["enigma2"]["device_id"]);
        $f5cab1816ec764ef073063a4c9596cb6["details"] = array();
        $f5cab1816ec764ef073063a4c9596cb6["details"]["token"] = $accessToken;
        $f5cab1816ec764ef073063a4c9596cb6["details"]["username"] = $de0eb4ea8ae0aa5b5b8864529380cf22["user_info"]["username"];
        $f5cab1816ec764ef073063a4c9596cb6["details"]["password"] = $de0eb4ea8ae0aa5b5b8864529380cf22["user_info"]["password"];
        $f5cab1816ec764ef073063a4c9596cb6["details"]["watchdog_seconds"] = $Fb012ef0b8c84139cf5b45c26d1a4d54;
        c1dcb3f44e64195b2bdb46417f74dea1:
        echo json_encode($f5cab1816ec764ef073063a4c9596cb6);
        die;
    }
    die(json_encode(array()));
}
if (!(A78bf8d35765Be2408C50712CE7A43ad::$request["pversion"] != "0.0.1")) {
    goto af0dcedaea634f96c690895117cfd159;
}
echo json_encode(strtoupper(implode(":", str_split(substr(md5(mt_rand()), 0, 12), 2))));
af0dcedaea634f96c690895117cfd159:
die;
