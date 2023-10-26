<?php
set_time_limit(0);
require "init.php";
$remoteIpAddress = $_SERVER["REMOTE_ADDR"];
if (!(!in_array($remoteIpAddress, Cd89785224751cca8017139DaF9e891e::Ab69E1103C96eE33FE21a6453d788925()) && !in_array($remoteIpAddress, a78bf8d35765BE2408c50712ce7a43ad::$settings["api_ips"]))) {
    if (!(!empty(A78Bf8D35765be2408c50712cE7A43Ad::$settings["api_pass"]) && A78BF8D35765be2408c50712cE7a43ad::$request["api_pass"] != A78Bf8D35765bE2408C50712cE7a43ad::$settings["api_pass"])) {
        $rAction = !empty(A78bF8D35765be2408c50712CE7a43ad::$request["action"]) ? A78Bf8d35765bE2408C50712cE7A43ad::$request["action"] : '';
        $rSub = !empty(A78Bf8D35765Be2408C50712Ce7A43ad::$request["sub"]) ? A78BF8D35765bE2408C50712CE7A43aD::$request["sub"] : '';
        switch ($rAction) {
            case "server":
                switch ($rSub) {
                    case "list":
                        $output = array();
                        foreach (A78Bf8D35765be2408c50712Ce7A43ad::$StreamingServers as $ServerId => $C3af9fee694e49882d2d0c32f538efc8) {
                            $output[] = array("id" => $ServerId, "server_name" => $C3af9fee694e49882d2d0c32f538efc8["server_name"], "online" => $C3af9fee694e49882d2d0c32f538efc8["server_online"], "info" => json_decode($C3af9fee694e49882d2d0c32f538efc8["server_hardware"], true));
                        }
                        echo json_encode($output);
                        break;
                }
            case "vod":
                $stream_ids = array_map("intval", A78Bf8D35765Be2408c50712Ce7a43ad::$request["stream_ids"]);
                switch ($rSub) {
                    case "start":
                    case "stop":
                        $ServersId = empty(a78bF8D35765Be2408C50712Ce7A43AD::$request["servers"]) ? array_keys(a78bF8D35765be2408c50712Ce7A43AD::$StreamingServers) : array_map("intval", a78Bf8D35765bE2408C50712CE7a43AD::$request["servers"]);
                        foreach ($ServersId as $ServerId) {
                            $B13e3f304ca1f14e137f209a5138ea10[$ServerId] = array("url" => A78BF8D35765be2408C50712ce7A43Ad::$StreamingServers[$ServerId]["api_url_ip"] . "&action=vod", "postdata" => array("function" => $rSub, "stream_ids" => $stream_ids));
                        }
                        A78Bf8D35765be2408c50712Ce7a43AD::processServers($B13e3f304ca1f14e137f209a5138ea10);
                        echo json_encode(array("result" => true));
                        die;
                }
                break;
            case "stream":
                switch ($rSub) {
                    case "start":
                    case "stop":
                        $stream_ids = array_map("intval", a78bf8d35765BE2408c50712Ce7a43aD::$request["stream_ids"]);
                        $ServersId = empty(A78Bf8d35765bE2408C50712cE7a43AD::$request["servers"]) ? array_keys(a78bF8d35765be2408C50712cE7A43ad::$StreamingServers) : array_map("intval", A78bF8D35765bE2408C50712Ce7A43AD::$request["servers"]);
                        foreach ($ServersId as $ServerId) {
                            $B13e3f304ca1f14e137f209a5138ea10[$ServerId] = array("url" => a78bf8d35765be2408c50712CE7a43ad::$StreamingServers[$ServerId]["api_url_ip"] . "&action=stream", "postdata" => array("function" => $rSub, "stream_ids" => $stream_ids));
                        }
                        A78Bf8d35765be2408C50712ce7a43aD::processServers($B13e3f304ca1f14e137f209a5138ea10);
                        echo json_encode(array("result" => true));
                        die;
                    case "list":
                        $output = array();
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT id,stream_display_name FROM `streams` WHERE type <> 2");
                        foreach ($f566700a43ee8e1f0412fe10fbdf03df->C126fD559932F625CDf6098d86C63880() as $c72d66b481d02f854f0bef67db92a547) {
                            $output[] = array("id" => $c72d66b481d02f854f0bef67db92a547["id"], "stream_name" => $c72d66b481d02f854f0bef67db92a547["stream_display_name"]);
                        }
                        echo json_encode($output);
                        goto aef34c2f5b53ef103109ab629cf3e269;
                    case "offline":
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.stream_status,t1.server_id,t1.stream_id \n                                  FROM `streams_sys` t1\n                                  INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.type <> 2\n                                  WHERE t1.stream_status = 1");
                        $D465fc5085f41251c6fa7c77b8333b0f = $f566700a43ee8e1f0412fe10fbdf03df->C126FD559932F625cdF6098d86C63880(true, "stream_id", false, "server_id");
                        $output = array();
                        foreach ($D465fc5085f41251c6fa7c77b8333b0f as $ba85d77d367dcebfcc2a3db9e83bb581 => $ServersId) {
                            $output[$ba85d77d367dcebfcc2a3db9e83bb581] = array_keys($ServersId);
                        }
                        echo json_encode($output);
                        goto aef34c2f5b53ef103109ab629cf3e269;
                    case "online":
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT t1.stream_status,t1.server_id,t1.stream_id \n                                  FROM `streams_sys` t1\n                                  INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.type <> 2\n                                  WHERE t1.pid > 0 AND t1.stream_status = 0");
                        $D465fc5085f41251c6fa7c77b8333b0f = $f566700a43ee8e1f0412fe10fbdf03df->c126fd559932f625cDF6098D86c63880(true, "stream_id", false, "server_id");
                        $output = array();
                        foreach ($D465fc5085f41251c6fa7c77b8333b0f as $ba85d77d367dcebfcc2a3db9e83bb581 => $ServersId) {
                            $output[$ba85d77d367dcebfcc2a3db9e83bb581] = array_keys($ServersId);
                        }
                        echo json_encode($output);
                        goto aef34c2f5b53ef103109ab629cf3e269;
                }
                aef34c2f5b53ef103109ab629cf3e269:
                goto Bd7b033d4dbcf543ff6bfbb182da4c5f;
            case "stb":
                switch ($rSub) {
                    case "info":
                        if (!empty(A78BF8d35765Be2408c50712ce7a43aD::$request["mac"])) {
                            $mac = a78bf8d35765BE2408C50712ce7a43aD::$request["mac"];
                            $a8df9f055e91a1e9240230b69af85555 = CD89785224751cCa8017139DAF9e891E::f2cBD6b6F59558B819C0CFF8c3b2Ef2c(false, $mac, true, false, true);
                            if (!empty($a8df9f055e91a1e9240230b69af85555)) {
                                echo json_encode(array_merge(array("result" => true), $a8df9f055e91a1e9240230b69af85555));
                                goto Cae5bb3a650138d4e51b652db78b831a;
                            }
                            echo json_encode(array("result" => false, "error" => "NOT EXISTS"));
                            Cae5bb3a650138d4e51b652db78b831a:
                            goto F7639f705c8485d1ec7e55f2e8ccb6ee;
                        }
                        echo json_encode(array("result" => false, "error" => "PARAMETER ERROR (mac)"));
                        F7639f705c8485d1ec7e55f2e8ccb6ee:
                        goto E4df417f5446cab33460283f6bfb20d4;
                    case "edit":
                        if (!empty(a78bf8D35765bE2408C50712cE7a43ad::$request["mac"])) {
                            $mac = a78bf8d35765bE2408C50712Ce7a43AD::$request["mac"];
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f = empty(A78bf8d35765BE2408c50712CE7A43ad::$request["user_data"]) ? array() : A78bf8D35765bE2408c50712CE7a43AD::$request["user_data"];
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["is_mag"] = 1;
                            $b0f1eb357ed72245e03dfe6268912497 = FBAac025084A44F7876230Ff53A6137F($Bf4bb0ad11102aaccbf77b6cdc1fd66f);
                            if ($f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `users` SET {$b0f1eb357ed72245e03dfe6268912497} WHERE id = ( SELECT user_id FROM mag_devices WHERE `mac` = '%s' )", base64_encode(strtoupper($mac)))) {
                                if ($f566700a43ee8e1f0412fe10fbdf03df->e872BE457a7F493D774179C6BdF95b46() > 0) {
                                    echo json_encode(array("result" => true));
                                    $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` ) VALUES( '%s', '%s', '%s', '%s', '%s' )", "SYSTEM API[{$remoteIpAddress}]", $mac, "-", time(), "[API->Edit MAG Device]");
                                    goto ef316132581356bd60698b48094eae79;
                                }
                                echo json_encode(array("result" => false));
                                ef316132581356bd60698b48094eae79:
                                goto Bdad3c68fac6b2d0eac3e12993acfab0;
                            }
                            echo json_encode(array("result" => false, "error" => "PARAMETER ERROR"));
                            Bdad3c68fac6b2d0eac3e12993acfab0:
                            goto C69c6552e9e4f3e1554d0017cb6b3f7a;
                        }
                        echo json_encode(array("result" => false, "error" => "PARAMETER ERROR (user/pass)"));
                        C69c6552e9e4f3e1554d0017cb6b3f7a:
                        goto E4df417f5446cab33460283f6bfb20d4;
                    case "create":
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f = empty(a78bF8d35765BE2408c50712cE7A43Ad::$request["user_data"]) ? array() : a78BF8d35765BE2408c50712ce7A43aD::$request["user_data"];
                        if (!empty($Bf4bb0ad11102aaccbf77b6cdc1fd66f["mac"])) {
                            $fb226b0ab56e366f44da9cf9ee107fff = array(1, 2, 3);
                            $mac = base64_encode(strtoupper($Bf4bb0ad11102aaccbf77b6cdc1fd66f["mac"]));
                            unset($Bf4bb0ad11102aaccbf77b6cdc1fd66f["mac"]);
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["username"] = a78bF8d35765be2408c50712Ce7a43ad::e5182e3aFA58aC7EC5D69d56B28819cd(10);
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["password"] = a78bf8D35765be2408c50712Ce7A43AD::E5182e3afA58AC7Ec5D69D56B28819cd(10);
                            if (!(!array_key_exists("allowed_ips", $Bf4bb0ad11102aaccbf77b6cdc1fd66f) || !Ef9fcEFFa62DB6eCc4c8a628b9B5A9aF($Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ips"]))) {
                                goto Ffcaed79e88470af2db73f4aa1e29e19;
                            }
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ips"] = json_encode(array());
                            Ffcaed79e88470af2db73f4aa1e29e19:
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ua"] = json_encode(array());
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["created_at"] = time();
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["created_by"] = 0;
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["exp_date"] = empty($Bf4bb0ad11102aaccbf77b6cdc1fd66f["exp_date"]) ? null : intval($Bf4bb0ad11102aaccbf77b6cdc1fd66f["exp_date"]);
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"] = empty($Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"]) || !EF9fCefFa62DB6ECc4c8a628B9B5A9aF($Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"]) ? array() : array_map("intval", json_decode($Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"], true));
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f["is_mag"] = 1;
                            if (!array_key_exists("mac", $Bf4bb0ad11102aaccbf77b6cdc1fd66f)) {
                                goto beb8381ee3b5034c3a2b71080c785432;
                            }
                            unset($Bf4bb0ad11102aaccbf77b6cdc1fd66f["mac"]);
                            beb8381ee3b5034c3a2b71080c785432:
                            if (!array_key_exists("output_formats", $Bf4bb0ad11102aaccbf77b6cdc1fd66f)) {
                                goto f11c105aa3afceda245b5b2320f8e469;
                            }
                            unset($Bf4bb0ad11102aaccbf77b6cdc1fd66f["output_formats"]);
                            f11c105aa3afceda245b5b2320f8e469:
                            if (!CE15043404aa3e950fc9C9dd8bc0325a("mag_devices", "mac", $mac)) {
                                $b0f1eb357ed72245e03dfe6268912497 = b484C4Ff0e3EE69B9d98B92884B88c0F($Bf4bb0ad11102aaccbf77b6cdc1fd66f);
                                if ($f566700a43ee8e1f0412fe10fbdf03df->Fc53e22ae7eE3bb881CD95Fb606914F0("INSERT INTO `users` {$b0f1eb357ed72245e03dfe6268912497}")) {
                                    if ($f566700a43ee8e1f0412fe10fbdf03df->e872Be457a7f493d774179c6BDF95B46() > 0) {
                                        $E38668abaa324e464e266fb7b7e784b1 = $f566700a43ee8e1f0412fe10fbdf03df->getLastInsertedId();
                                        foreach ($fb226b0ab56e366f44da9cf9ee107fff as $b1f84f020035bf724cdc2f6d05ee33c3) {
                                            $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `user_output` ( `user_id`, `access_output_id` )VALUES( '%d', '%d' )", $E38668abaa324e464e266fb7b7e784b1, $b1f84f020035bf724cdc2f6d05ee33c3);
                                        }
                                        $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `mag_devices` ( `user_id`, `mac`, `created` )VALUES( '%d', '%s', '%d' )", $E38668abaa324e464e266fb7b7e784b1, $mac, time());
                                        echo json_encode(array("result" => true));
                                        $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( '%s', '%s', '%s', '%s', '%s' )", "SYSTEM API[{$remoteIpAddress}]", base64_decode($mac), "-", time(), "[API->New MAG Device]");
                                        goto b463ba810fc419d0898e72af04bb09aa;
                                    }
                                    echo json_encode(array("result" => false));
                                    b463ba810fc419d0898e72af04bb09aa:
                                    goto dca3ea3691feda6e36431b281ca4a408;
                                }
                                echo json_encode(array("result" => false, "error" => "PARAMETER ERROR"));
                                dca3ea3691feda6e36431b281ca4a408:
                                goto Fe91467914cba337d155be580614f3d4;
                            }
                            echo json_encode(array("result" => false, "error" => "EXISTS"));
                            Fe91467914cba337d155be580614f3d4:
                            goto D347de63208146b798a7547e1f3f26f0;
                        }
                        echo json_encode(array("result" => false, "error" => "PARAMETER ERROR (mac)"));
                        D347de63208146b798a7547e1f3f26f0:
                        goto E4df417f5446cab33460283f6bfb20d4;
                }
                E4df417f5446cab33460283f6bfb20d4:
                goto Bd7b033d4dbcf543ff6bfbb182da4c5f;
            case "user":
                switch ($rSub) {
                    case "info":
                        if (!empty(a78Bf8D35765BE2408C50712CE7a43aD::$request["username"]) && !empty(a78bF8d35765bE2408c50712ce7a43aD::$request["password"])) {
                            $f6806488699d3315dc5dc1e27a401b3e = A78bF8D35765bE2408C50712Ce7a43Ad::$request["username"];
                            $password = A78Bf8D35765be2408c50712Ce7A43aD::$request["password"];
                            $a8df9f055e91a1e9240230b69af85555 = cd89785224751Cca8017139DaF9E891E::E5550592AA298DD1D5Ee59cdCe063a12(false, $f6806488699d3315dc5dc1e27a401b3e, $password, true, false, true);
                            if (!empty($a8df9f055e91a1e9240230b69af85555)) {
                                echo json_encode(array("result" => true, "user_info" => $a8df9f055e91a1e9240230b69af85555));
                                goto a84bfb6a4d110af65596b1eb3194c15f;
                            }
                            echo json_encode(array("result" => false, "error" => "NOT EXISTS"));
                            a84bfb6a4d110af65596b1eb3194c15f:
                            goto fc5f1b4efdc2cc3190061d3a9c73d406;
                        }
                        echo json_encode(array("result" => false, "error" => "PARAMETER ERROR (user/pass)"));
                        fc5f1b4efdc2cc3190061d3a9c73d406:
                        goto Bab0645719f9ad7cf724aa68a2d3f533;
                    case "edit":
                        if (!empty(A78bf8D35765BE2408c50712cE7a43aD::$request["username"]) && !empty(A78bf8D35765BE2408C50712ce7a43ad::$request["password"])) {
                            $f6806488699d3315dc5dc1e27a401b3e = a78BF8D35765be2408C50712cE7A43aD::$request["username"];
                            $password = A78bF8D35765bE2408C50712Ce7a43aD::$request["password"];
                            $Bf4bb0ad11102aaccbf77b6cdc1fd66f = empty(A78BF8d35765BE2408C50712Ce7A43aD::$request["user_data"]) ? array() : a78Bf8D35765BE2408C50712Ce7A43aD::$request["user_data"];
                            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM `users` WHERE `username` = '%s' and `password` = '%s'", $f6806488699d3315dc5dc1e27a401b3e, $password);
                            if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0) {
                                $b0f1eb357ed72245e03dfe6268912497 = fBaaC025084a44F7876230Ff53A6137F($Bf4bb0ad11102aaccbf77b6cdc1fd66f);
                                if ($f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE `users` SET {$b0f1eb357ed72245e03dfe6268912497} WHERE `username` = '%s' and `password` = '%s'", $f6806488699d3315dc5dc1e27a401b3e, $password)) {
                                    echo json_encode(array("result" => true));
                                    $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( '%s', '%s', '%s', '%s', '%s' )", "SYSTEM API[{$remoteIpAddress}]", $f6806488699d3315dc5dc1e27a401b3e, $password, time(), "[API->Edit Line]");
                                    goto A95dbe5b3031f4b2f358372057d6dc0f;
                                }
                                echo json_encode(array("result" => false, "error" => "PARAMETER ERROR"));
                                A95dbe5b3031f4b2f358372057d6dc0f:
                                goto fc51a394ceaaa811e017acaad079272a;
                            }
                            echo json_encode(array("result" => false, "error" => "NOT EXISTS"));
                            fc51a394ceaaa811e017acaad079272a:
                            goto c11cc50ed770169d383790268dd38cce;
                        }
                        echo json_encode(array("result" => false, "error" => "PARAMETER ERROR (user/pass)"));
                        c11cc50ed770169d383790268dd38cce:
                        goto Bab0645719f9ad7cf724aa68a2d3f533;
                    case "create":
                        $fb226b0ab56e366f44da9cf9ee107fff = array(1, 2, 3);
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f = empty(A78bf8D35765be2408c50712cE7A43Ad::$request["user_data"]) ? array() : A78bF8d35765be2408c50712ce7A43Ad::$request["user_data"];
                        if (array_key_exists("username", $Bf4bb0ad11102aaccbf77b6cdc1fd66f)) {
                            goto Fa0f17e75d57f133cbfafad278756634;
                        }
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["username"] = A78BF8d35765BE2408C50712cE7a43AD::E5182E3afa58AC7ec5D69d56B28819Cd(10);
                        Fa0f17e75d57f133cbfafad278756634:
                        if (array_key_exists("password", $Bf4bb0ad11102aaccbf77b6cdc1fd66f)) {
                            goto F581ebe88a4d7889bb99e82fa4b4cddd;
                        }
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["password"] = A78BF8d35765be2408c50712Ce7a43Ad::E5182e3AFa58ac7Ec5D69d56B28819CD(10);
                        F581ebe88a4d7889bb99e82fa4b4cddd:
                        if (!(!array_key_exists("allowed_ips", $Bf4bb0ad11102aaccbf77b6cdc1fd66f) || !ef9fCeffa62dB6ECC4C8A628B9B5a9aF($Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ips"]))) {
                            goto E952e4fc71dbeba80c58528a6758a07b;
                        }
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ips"] = json_encode(array());
                        E952e4fc71dbeba80c58528a6758a07b:
                        if (!(!array_key_exists("allowed_ua", $Bf4bb0ad11102aaccbf77b6cdc1fd66f) || !eF9FCEfFa62Db6ecC4C8A628b9b5A9aF($Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ua"]))) {
                            goto aebe19b8bb0bcea41ef694d495f0f5b0;
                        }
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["allowed_ua"] = json_encode(array());
                        aebe19b8bb0bcea41ef694d495f0f5b0:
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["created_at"] = time();
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["created_by"] = 0;
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["exp_date"] = empty($Bf4bb0ad11102aaccbf77b6cdc1fd66f["exp_date"]) ? null : intval($Bf4bb0ad11102aaccbf77b6cdc1fd66f["exp_date"]);
                        $Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"] = empty($Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"]) || !EF9FcEFFA62dB6Ecc4C8a628b9b5A9af($Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"]) ? array() : array_map("intval", json_decode($Bf4bb0ad11102aaccbf77b6cdc1fd66f["bouquet"], true));
                        $fb226b0ab56e366f44da9cf9ee107fff = empty($Bf4bb0ad11102aaccbf77b6cdc1fd66f["output_formats"]) || !ef9FCefFa62DB6ECC4C8A628B9B5A9AF($Bf4bb0ad11102aaccbf77b6cdc1fd66f["output_formats"]) ? $fb226b0ab56e366f44da9cf9ee107fff : array_map("intval", $Bf4bb0ad11102aaccbf77b6cdc1fd66f["output_formats"]);
                        if (!array_key_exists("output_formats", $Bf4bb0ad11102aaccbf77b6cdc1fd66f)) {
                            goto e28125caf199086c993aa163fae1cdad;
                        }
                        unset($Bf4bb0ad11102aaccbf77b6cdc1fd66f["output_formats"]);
                        e28125caf199086c993aa163fae1cdad:
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT id FROM `users` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1", $Bf4bb0ad11102aaccbf77b6cdc1fd66f["username"], $Bf4bb0ad11102aaccbf77b6cdc1fd66f["password"]);
                        if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() == 0) {
                            $b0f1eb357ed72245e03dfe6268912497 = b484C4FF0E3eE69b9D98b92884B88c0F($Bf4bb0ad11102aaccbf77b6cdc1fd66f);
                            if ($f566700a43ee8e1f0412fe10fbdf03df->fc53e22AE7Ee3bB881cD95fB606914F0("INSERT INTO `users` {$b0f1eb357ed72245e03dfe6268912497}")) {
                                if ($f566700a43ee8e1f0412fe10fbdf03df->e872bE457a7F493D774179C6bDF95B46() > 0) {
                                    $E38668abaa324e464e266fb7b7e784b1 = $f566700a43ee8e1f0412fe10fbdf03df->getLastInsertedId();
                                    foreach ($fb226b0ab56e366f44da9cf9ee107fff as $b1f84f020035bf724cdc2f6d05ee33c3) {
                                        $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `user_output` ( `user_id`, `access_output_id` ) VALUES( '%d', '%d' )", $E38668abaa324e464e266fb7b7e784b1, $b1f84f020035bf724cdc2f6d05ee33c3);
                                    }
                                    echo json_encode(array("result" => true, "created_id" => $E38668abaa324e464e266fb7b7e784b1, "username" => $Bf4bb0ad11102aaccbf77b6cdc1fd66f["username"], "password" => $Bf4bb0ad11102aaccbf77b6cdc1fd66f["password"]));
                                    $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( '%s', '%s', '%s', '%s', '%s' )", "SYSTEM API[{$remoteIpAddress}]", $Bf4bb0ad11102aaccbf77b6cdc1fd66f["username"], $Bf4bb0ad11102aaccbf77b6cdc1fd66f["password"], time(), "[API->New Line]");
                                    goto bbae56417e1870f46ce11d8c72612597;
                                }
                                echo json_encode(array("result" => false));
                                bbae56417e1870f46ce11d8c72612597:
                                goto a7e12b6f19abc28c3e2dde140c9700f2;
                            }
                            echo json_encode(array("result" => false, "error" => "PARAMETER ERROR"));
                            a7e12b6f19abc28c3e2dde140c9700f2:
                            goto b69f005b71f18d710ee8147276f70611;
                        }
                        echo json_encode(array("result" => false, "error" => "EXISTS"));
                        b69f005b71f18d710ee8147276f70611:
                        goto Bab0645719f9ad7cf724aa68a2d3f533;
                }
                Bab0645719f9ad7cf724aa68a2d3f533:
                goto Bd7b033d4dbcf543ff6bfbb182da4c5f;
            case "reg_user":
                switch ($rSub) {
                    case "list":
                        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT id,username,credits,group_id,group_name,last_login,date_registered,email,ip,status\n                            FROM `reg_users` t1\n                        INNER JOIN `member_groups` t2 ON t1.member_group_id = t2.group_id");
                        $Af301a166badb15e0b00336d72fb9497 = $f566700a43ee8e1f0412fe10fbdf03df->C126fD559932f625CdF6098D86c63880();
                        echo json_encode($Af301a166badb15e0b00336d72fb9497);
                        goto b2b9be9c0bba730efb9fa3bdde73438f;
                    case "credits":
                        if (!empty(a78BF8d35765BE2408C50712ce7a43ad::$request["amount"]) && (!empty(a78bf8d35765Be2408c50712cE7A43Ad::$request["id"]) || !empty(a78bf8D35765be2408C50712CE7A43aD::$request["username"]))) {
                            $Cadd766037a4c84044843f30dd506e37 = sprintf("%.2f", A78bf8D35765bE2408C50712CE7A43AD::$request["amount"]);
                            if (!empty(A78bF8d35765Be2408c50712Ce7A43aD::$request["id"])) {
                                $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM reg_users WHERE `id` = '%d'", A78bF8d35765Be2408C50712CE7A43ad::$request["id"]);
                                goto e87d1e7ab132aced927d2e5144fa0f97;
                            }
                            $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * FROM reg_users WHERE `username` = '%s'", A78bF8D35765BE2408C50712CE7a43aD::$request["username"]);
                            e87d1e7ab132aced927d2e5144fa0f97:
                            if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount()) {
                                $Eb809884ee4b7eb427d7a2ae5a5fb355 = $f566700a43ee8e1f0412fe10fbdf03df->f1Ed191D78470660edff4a007696Bc1F();
                                $A6f4ecc798bcb285eee6efb4467c6708 = $Cadd766037a4c84044843f30dd506e37 + $Eb809884ee4b7eb427d7a2ae5a5fb355["credits"];
                                if ($A6f4ecc798bcb285eee6efb4467c6708 < 0) {
                                    echo json_encode(array("result" => true, "error" => "NOT ENOUGH CREDITS"));
                                    goto B7758b1ca336cab2aea28d6f30495934;
                                }
                                $f566700a43ee8e1f0412fe10fbdf03df->query("UPDATE reg_users SET `credits` = '%.2f' WHERE `id` = '%d'", $A6f4ecc798bcb285eee6efb4467c6708, $Eb809884ee4b7eb427d7a2ae5a5fb355["id"]);
                                echo json_encode(array("result" => true));
                                $f566700a43ee8e1f0412fe10fbdf03df->query("INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( '%s', '%s', '%s', '%s', '%s' )", "SYSTEM API[{$remoteIpAddress}]", $Bf4bb0ad11102aaccbf77b6cdc1fd66f["username"], $Bf4bb0ad11102aaccbf77b6cdc1fd66f["password"], time(), "[API->ADD Credits {$Cadd766037a4c84044843f30dd506e37}]");
                                B7758b1ca336cab2aea28d6f30495934:
                                goto f0081f280f963109c9caf18342f311bc;
                            }
                            echo json_encode(array("result" => false, "error" => "NOT EXISTS"));
                            f0081f280f963109c9caf18342f311bc:
                            goto b6865b60547f69d51a1b730941979581;
                        }
                        echo json_encode(array("result" => false, "error" => "PARAMETER ERROR (amount & id||username)"));
                        b6865b60547f69d51a1b730941979581:
                        goto b2b9be9c0bba730efb9fa3bdde73438f;
                }
                b2b9be9c0bba730efb9fa3bdde73438f:
                goto Bd7b033d4dbcf543ff6bfbb182da4c5f;
        }
        Bd7b033d4dbcf543ff6bfbb182da4c5f:
        function fbAAC025084A44f7876230ff53A6137f($d76067cf9572f7a6691c85c12faf2a29) {
            global $f566700a43ee8e1f0412fe10fbdf03df;
            $b0f1eb357ed72245e03dfe6268912497 = '';
            foreach ($d76067cf9572f7a6691c85c12faf2a29 as $bca37bc3b9c255b1666da6076ce9aa30 => $a1daec950dd361ae639ad3a57dc018c0) {
                $bca37bc3b9c255b1666da6076ce9aa30 = preg_replace("/[^a-zA-Z0-9\\_]+/", '', $bca37bc3b9c255b1666da6076ce9aa30);
                if (is_array($a1daec950dd361ae639ad3a57dc018c0)) {
                    $b0f1eb357ed72245e03dfe6268912497 .= "`{$bca37bc3b9c255b1666da6076ce9aa30}` = '" . $f566700a43ee8e1f0412fe10fbdf03df->escape(json_encode($a1daec950dd361ae639ad3a57dc018c0)) . "',";
                    goto Eb823b81d52a0b2a1256d64215290521;
                }
                if (is_null($a1daec950dd361ae639ad3a57dc018c0)) {
                    $b0f1eb357ed72245e03dfe6268912497 .= "`{$bca37bc3b9c255b1666da6076ce9aa30}` = null,";
                    goto A95c2423b95a4715e4d2e1c8bef0a4b7;
                }
                $b0f1eb357ed72245e03dfe6268912497 .= "`{$bca37bc3b9c255b1666da6076ce9aa30}` = '" . $f566700a43ee8e1f0412fe10fbdf03df->escape($a1daec950dd361ae639ad3a57dc018c0) . "',";
                A95c2423b95a4715e4d2e1c8bef0a4b7:
                Eb823b81d52a0b2a1256d64215290521:
            }
            return rtrim($b0f1eb357ed72245e03dfe6268912497, ",");
        }
        function b484c4Ff0e3eE69B9D98B92884B88C0f($d76067cf9572f7a6691c85c12faf2a29) {
            global $f566700a43ee8e1f0412fe10fbdf03df;
            $b0f1eb357ed72245e03dfe6268912497 = "(";
            foreach (array_keys($d76067cf9572f7a6691c85c12faf2a29) as $bca37bc3b9c255b1666da6076ce9aa30) {
                $bca37bc3b9c255b1666da6076ce9aa30 = preg_replace("/[^a-zA-Z0-9\\_]+/", '', $bca37bc3b9c255b1666da6076ce9aa30);
                $b0f1eb357ed72245e03dfe6268912497 .= "`{$bca37bc3b9c255b1666da6076ce9aa30}`,";
            }
            $b0f1eb357ed72245e03dfe6268912497 = rtrim($b0f1eb357ed72245e03dfe6268912497, ",") . ") VALUES (";
            foreach (array_values($d76067cf9572f7a6691c85c12faf2a29) as $a1daec950dd361ae639ad3a57dc018c0) {
                if (!is_array($a1daec950dd361ae639ad3a57dc018c0)) {
                    $b0f1eb357ed72245e03dfe6268912497 .= "'" . $f566700a43ee8e1f0412fe10fbdf03df->escape($a1daec950dd361ae639ad3a57dc018c0) . "',";
                    goto e1365d05c90b3128c0d7a8cfecb5a3d5;
                }
                if (is_null($a1daec950dd361ae639ad3a57dc018c0)) {
                    $b0f1eb357ed72245e03dfe6268912497 .= "NULL,";
                    goto Bdacae5fbd26b939ed1bf00b09fae732;
                }
                $b0f1eb357ed72245e03dfe6268912497 .= "'" . $f566700a43ee8e1f0412fe10fbdf03df->escape(json_encode($a1daec950dd361ae639ad3a57dc018c0)) . "',";
                Bdacae5fbd26b939ed1bf00b09fae732:
                e1365d05c90b3128c0d7a8cfecb5a3d5:
            }
            $b0f1eb357ed72245e03dfe6268912497 = rtrim($b0f1eb357ed72245e03dfe6268912497, ",") . ");";
            return $b0f1eb357ed72245e03dfe6268912497;
        }
        function eF9fcefFa62Db6ecC4c8a628B9b5a9Af($F999d6c638356ee8a5d971e3eabf821a) {
            return is_array(json_decode($F999d6c638356ee8a5d971e3eabf821a, true));
        }
        // [PHPDeobfuscator] Implied script end
        return;
    }
    die(json_encode(array("result" => false, "KEY WRONG")));
}
die(json_encode(array("result" => false, "IP FORBIDDEN")));
