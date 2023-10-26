<?php

class cd89785224751CCA8017139dAF9e891E {
    public static $ipTV_db;
    public static $AllowedIPs = array();
    public static function a0218A0e77B606FEF8D734AC4510Ddb1() {
        self::$ipTV_db->query("SELECT `ip` FROM `rtmp_ips`");
        return array_merge(array("127.0.0.1"), array_map("gethostbyname", A78bf8D35765BE2408c50712ce7a43ad::c0dA8e7BD7A2028B339E52af2835a028(self::$ipTV_db->c126Fd559932F625cDF6098D86c63880())));
    }
    public static function e8e54de10433EB446982a4Af8ADea379($d38a1c3d822bdbbd61f649f33212ebde, $c5f97e03cbf94a57a805526a8288042f) {
        if (empty($d38a1c3d822bdbbd61f649f33212ebde["xy_offset"])) {
            $d43f5adb4da33d3ded5cecc9d0c0b4c7 = rand(150, 380);
            $Cca4c808b355e55e72f3bfb3c6603659 = rand(110, 250);
            goto cdfa61f0f145bc10f69ece63755158d5;
        }
        list($d43f5adb4da33d3ded5cecc9d0c0b4c7, $Cca4c808b355e55e72f3bfb3c6603659) = explode("x", $d38a1c3d822bdbbd61f649f33212ebde["xy_offset"]);
        cdfa61f0f145bc10f69ece63755158d5:
        passthru("FFMPEG_PATH -nofix_dts -fflags +igndts -copyts -vsync 0 -nostats -nostdin -hide_banner -loglevel quiet -y -i \"STREAMS_PATH" . $c5f97e03cbf94a57a805526a8288042f . "\" -filter_complex \"drawtext=fontfile=" . FFMPEG_FONTS_PATH . ":text='{$d38a1c3d822bdbbd61f649f33212ebde["message"]}':fontsize={$d38a1c3d822bdbbd61f649f33212ebde["font_size"]}:x={$d43f5adb4da33d3ded5cecc9d0c0b4c7}:y={$Cca4c808b355e55e72f3bfb3c6603659}:fontcolor={$d38a1c3d822bdbbd61f649f33212ebde["font_color"]}\" -map 0 -vcodec libx264 -preset ultrafast -acodec copy -scodec copy -mpegts_flags +initial_discontinuity -mpegts_copyts 1 -f mpegts -");
        return true;
    }
    public static function B20C5d64B4C7dBfAffeA9f96934138A4() {
        $cde0861dbbf0191a801e3f0699b834ed = array("127.0.0.1", $_SERVER["SERVER_ADDR"]);
        if (!(!file_exists("TMP_DIRcloud_ips") || time() - filemtime("TMP_DIRcloud_ips") >= 600)) {
            goto B219c799d14582056664206f0fbca4f1;
        }
        $f0bdbe56c3b41dee80ecaf635ea527e1 = A78bf8d35765Be2408C50712cE7a43Ad::d508D1E2ECC2E304e5bAb85E6A347b23("http://xtream-codes.com/cloud_ips");
        if (empty($f0bdbe56c3b41dee80ecaf635ea527e1)) {
            goto Ff2a41ce8efb46d9234b9d4d32888f8c;
        }
        file_put_contents("TMP_DIRcloud_ips", $f0bdbe56c3b41dee80ecaf635ea527e1);
        Ff2a41ce8efb46d9234b9d4d32888f8c:
        B219c799d14582056664206f0fbca4f1:
        if (!file_exists("TMP_DIRcloud_ips")) {
            goto F27eb8b170b163ee24bfdc6b123ef37b;
        }
        $cde0861dbbf0191a801e3f0699b834ed = array_filter(array_merge($cde0861dbbf0191a801e3f0699b834ed, array_map("trim", file("TMP_DIRcloud_ips"))));
        F27eb8b170b163ee24bfdc6b123ef37b:
        return array_unique($cde0861dbbf0191a801e3f0699b834ed);
    }
    public static function ab69E1103C96eE33Fe21a6453d788925($fdb41a5a5b49d5e9c80473d2f1b86731 = false) {
        if (empty(self::$AllowedIPs)) {
            $cde0861dbbf0191a801e3f0699b834ed = array("127.0.0.1", $_SERVER["SERVER_ADDR"]);
            foreach (a78BF8D35765be2408c50712ce7A43Ad::$StreamingServers as $e951d0b9610ba3624d06def5a541cb17 => $C3af9fee694e49882d2d0c32f538efc8) {
                if (empty($C3af9fee694e49882d2d0c32f538efc8["whitelist_ips"])) {
                    goto C5a77603a9b94fe4c1c59b23c1d3dabc;
                }
                $cde0861dbbf0191a801e3f0699b834ed = array_merge($cde0861dbbf0191a801e3f0699b834ed, json_decode($C3af9fee694e49882d2d0c32f538efc8["whitelist_ips"], true));
                C5a77603a9b94fe4c1c59b23c1d3dabc:
                $cde0861dbbf0191a801e3f0699b834ed[] = $C3af9fee694e49882d2d0c32f538efc8["server_ip"];
            }
            if (!$fdb41a5a5b49d5e9c80473d2f1b86731) {
                goto c549f9d49c8f4194f1617d51280b88dc;
            }
            if (empty(a78bF8d35765Be2408C50712Ce7A43AD::$settings["allowed_ips_admin"])) {
                goto d592469326e2c30fbd70c7874858d8f9;
            }
            $cde0861dbbf0191a801e3f0699b834ed = array_merge($cde0861dbbf0191a801e3f0699b834ed, explode(",", A78Bf8D35765bE2408C50712Ce7A43Ad::$settings["allowed_ips_admin"]));
            d592469326e2c30fbd70c7874858d8f9:
            self::$ipTV_db->query("SELECT * FROM `xtream_main` WHERE id = 1");
            $fdf8df33b9a361067fee2f972282611d = self::$ipTV_db->f1ed191D78470660EDFF4a007696BC1f();
            if (empty($fdf8df33b9a361067fee2f972282611d["root_ip"])) {
                goto Adde05b74b4f01675f6250936c7a3096;
            }
            $cde0861dbbf0191a801e3f0699b834ed[] = $fdf8df33b9a361067fee2f972282611d["root_ip"];
            Adde05b74b4f01675f6250936c7a3096:
            self::$ipTV_db->query("SELECT DISTINCT t1.`ip` FROM `reg_users` t1 INNER JOIN `member_groups` t2 ON t2.group_id = t1.member_group_id AND t2.is_admin = 1 AND t1.`last_login` >= '%d'", strtotime("-2 hour"));
            $Cfa6e78e5b50872422c16bab31113ce7 = a78bF8D35765Be2408c50712Ce7A43aD::c0da8E7bD7A2028B339E52AF2835A028(self::$ipTV_db->C126fd559932f625cDf6098D86C63880());
            $cde0861dbbf0191a801e3f0699b834ed = array_merge($cde0861dbbf0191a801e3f0699b834ed, $Cfa6e78e5b50872422c16bab31113ce7);
            c549f9d49c8f4194f1617d51280b88dc:
            if (!(!file_exists("TMP_DIRcloud_ips") || time() - filemtime("TMP_DIRcloud_ips") >= 600)) {
                goto Ae2b61f00853789876c502bea34fea88;
            }
            $f0bdbe56c3b41dee80ecaf635ea527e1 = a78bf8D35765bE2408C50712ce7A43aD::D508d1e2eCC2e304E5bAb85e6A347B23("http://xtream-codes.com/cloud_ips");
            if (empty($f0bdbe56c3b41dee80ecaf635ea527e1)) {
                goto d277b50bd68ced9c8fc0156bbf028465;
            }
            file_put_contents("TMP_DIRcloud_ips", $f0bdbe56c3b41dee80ecaf635ea527e1);
            d277b50bd68ced9c8fc0156bbf028465:
            Ae2b61f00853789876c502bea34fea88:
            if (!file_exists("TMP_DIRcloud_ips")) {
                goto ab12ca5d91bd872b2f2f659a2219b6d7;
            }
            $cde0861dbbf0191a801e3f0699b834ed = array_filter(array_merge($cde0861dbbf0191a801e3f0699b834ed, array_map("trim", file("TMP_DIRcloud_ips"))));
            ab12ca5d91bd872b2f2f659a2219b6d7:
            self::$AllowedIPs = $cde0861dbbf0191a801e3f0699b834ed;
            return array_unique($cde0861dbbf0191a801e3f0699b834ed);
        }
        return self::$AllowedIPs;
    }
    public static function E990445b40642e0eFD070e994375F6af($E821605d1d9382d422040b86d29632d9) {
        file_put_contents(CLOSE_OPEN_CONS_PATH . $E821605d1d9382d422040b86d29632d9, 1);
    }
    public static function C1B5A5e17240E1fbe7502CCDb57EA2EF($streamId) {
        if (!CACHE_STREAMS) {
            goto B6c33a366c8d846ac540cf7623387237;
        }
        if (!(file_exists(TMP_DIR . $streamId . "_cacheStream") && time() - filemtime(TMP_DIR . $streamId . "_cacheStream") <= CACHE_STREAMS_TIME)) {
            B6c33a366c8d846ac540cf7623387237:
            $output = array();
            self::$ipTV_db->query("SELECT * FROM `streams` t1\n                                LEFT JOIN `streams_types` t2 ON t2.type_id = t1.type\n                                WHERE t1.`id` = '%d'", $streamId);
            if (!(self::$ipTV_db->getRowCount() > 0)) {
                goto C1b72be49e69898024d500c432843cb2;
            }
            $Cb08b127bfe426d7f3ccbd3e38f05471 = self::$ipTV_db->f1ED191D78470660eDFf4A007696bC1f();
            $f9b9c9baaec5b82b03b15c6eb07ec8f9 = array();
            if (!($Cb08b127bfe426d7f3ccbd3e38f05471["direct_source"] == 0)) {
                goto d65aaa5c3a83f7ce8eb84d0cd8e2ceff;
            }
            self::$ipTV_db->query("SELECT * FROM `streams_sys` WHERE `stream_id` = '%d'", $streamId);
            if (!(self::$ipTV_db->getRowCount() > 0)) {
                goto c21457ecef144b2c3cdef3e40006a985;
            }
            $f9b9c9baaec5b82b03b15c6eb07ec8f9 = self::$ipTV_db->c126fD559932F625CDF6098D86c63880(true, "server_id");
            c21457ecef144b2c3cdef3e40006a985:
            d65aaa5c3a83f7ce8eb84d0cd8e2ceff:
            $output["info"] = $Cb08b127bfe426d7f3ccbd3e38f05471;
            $output["servers"] = $f9b9c9baaec5b82b03b15c6eb07ec8f9;
            if (!CACHE_STREAMS) {
                goto Ca3c4ba4ad4afab6f31a5f634ff17759;
            }
            file_put_contents(TMP_DIR . $streamId . "_cacheStream", serialize($output), LOCK_EX);
            Ca3c4ba4ad4afab6f31a5f634ff17759:
            C1b72be49e69898024d500c432843cb2:
            return !empty($output) ? $output : false;
        }
        return unserialize(file_get_contents(TMP_DIR . $streamId . "_cacheStream"));
    }
    public static function F3c105bCCed491229d4Aed6937F96a8c($streamId, $F1350a5569e4b73d2f9cb26483f2a0c1, $a8df9f055e91a1e9240230b69af85555, $f4889efa84e1f2e30e5e9780973f68cb, $A75f2436a5614184bfe3442ddd050ec5, $a349f0750f0a814bd31ec4b3da51da95 = '', $Cf735adc0fa7bac523a6d09af79aa459 = '', $a28758c1ab974badfc544e11aaf19a57) {
        if (!($a28758c1ab974badfc544e11aaf19a57 == "archive")) {
            $c3a18c26bfa971a25d2e6ada870ff735 = self::C1b5A5E17240e1fbE7502ccdb57eA2Ef($streamId);
            if (!empty($c3a18c26bfa971a25d2e6ada870ff735)) {
                if (!($c3a18c26bfa971a25d2e6ada870ff735["info"]["direct_source"] == 1)) {
                    $e3215fa97db12812ee074d6c110dea4b = array();
                    foreach (A78bf8d35765be2408c50712ce7A43ad::$StreamingServers as $B5d03ddefb862a50fd6abc8561488d01 => $C3af9fee694e49882d2d0c32f538efc8) {
                        if (!(!array_key_exists($B5d03ddefb862a50fd6abc8561488d01, $c3a18c26bfa971a25d2e6ada870ff735["servers"]) || !A78bf8d35765Be2408C50712ce7a43Ad::$StreamingServers[$B5d03ddefb862a50fd6abc8561488d01]["server_online"])) {
                            if ($a28758c1ab974badfc544e11aaf19a57 == "movie") {
                                if (!(!empty($c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["pid"]) && $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["to_analyze"] == 0 && $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["stream_status"] == 0 && $C3af9fee694e49882d2d0c32f538efc8["timeshift_only"] == 0)) {
                                    goto e98c598ed9f55756c7dfdfe8d7fcbbe7;
                                }
                                $e3215fa97db12812ee074d6c110dea4b[] = $B5d03ddefb862a50fd6abc8561488d01;
                                e98c598ed9f55756c7dfdfe8d7fcbbe7:
                                goto B342294113c973e85af387b1da673eaa;
                            }
                            if (!(($c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["on_demand"] == 1 && $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["pid"] >= 0 && $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["stream_status"] == 0 || $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["pid"] > 0 && $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["stream_status"] == 0) && $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["to_analyze"] == 0 && time() >= (int) $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B5d03ddefb862a50fd6abc8561488d01]["delay_available_at"] && $C3af9fee694e49882d2d0c32f538efc8["timeshift_only"] == 0)) {
                                goto f7fc5cbddb27503b0f07d4f00f75a612;
                            }
                            $e3215fa97db12812ee074d6c110dea4b[] = $B5d03ddefb862a50fd6abc8561488d01;
                            f7fc5cbddb27503b0f07d4f00f75a612:
                            B342294113c973e85af387b1da673eaa:
                            goto b6e86aab7f8b19b2d92db1fe3fa4e42d;
                        }
                        b6e86aab7f8b19b2d92db1fe3fa4e42d:
                    }
                    if (!empty($e3215fa97db12812ee074d6c110dea4b)) {
                        $aab0f9a311e1a69945f2338c5651dd87 = array();
                        if (a78bF8d35765be2408c50712ce7A43Ad::$settings["online_capacity_interval"] != 0 && file_exists("TMP_DIRservers_capacity") && time() - filemtime("TMP_DIRservers_capacity") <= a78Bf8D35765Be2408C50712CE7a43aD::$settings["online_capacity_interval"]) {
                            $Cd4eabf7ecf553f46c17f0bd5a382c46 = json_decode(file_get_contents("TMP_DIRservers_capacity"), true);
                            goto Dcb6013d6c011b31fa12d890c6f527c4;
                        }
                        self::$ipTV_db->query("SELECT\n                              server_id,\n                              COUNT(*) AS online_clients\n                            FROM\n                              `user_activity_now`\n                            GROUP BY\n                              server_id\n                            ");
                        $Cd4eabf7ecf553f46c17f0bd5a382c46 = self::$ipTV_db->c126fD559932f625cdf6098d86c63880(true, "server_id");
                        if (a78Bf8D35765bE2408c50712CE7A43AD::$settings["split_by"] == "band") {
                            $D8d3ca7afab93e5c110124dc7611906c = array();
                            foreach ($e3215fa97db12812ee074d6c110dea4b as $e951d0b9610ba3624d06def5a541cb17) {
                                $A8897e590149896423cc3c897a6c6651 = json_decode(a78bf8D35765bE2408C50712CE7A43ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["server_hardware"], true);
                                if (!empty($A8897e590149896423cc3c897a6c6651["network_speed"])) {
                                    $D8d3ca7afab93e5c110124dc7611906c[$e951d0b9610ba3624d06def5a541cb17] = (float) $A8897e590149896423cc3c897a6c6651["network_speed"];
                                    goto d6478715224e6c5d8fbba6cedd93a54a;
                                }
                                $D8d3ca7afab93e5c110124dc7611906c[$e951d0b9610ba3624d06def5a541cb17] = 1000;
                                d6478715224e6c5d8fbba6cedd93a54a:
                            }
                            foreach ($Cd4eabf7ecf553f46c17f0bd5a382c46 as $e951d0b9610ba3624d06def5a541cb17 => $c72d66b481d02f854f0bef67db92a547) {
                                $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["capacity"] = (float) ($c72d66b481d02f854f0bef67db92a547["online_clients"] / $D8d3ca7afab93e5c110124dc7611906c[$e951d0b9610ba3624d06def5a541cb17]);
                            }
                            goto fb8512650313d24ebbda99a7e541af4a;
                        }
                        if (a78Bf8d35765BE2408c50712ce7A43ad::$settings["split_by"] == "maxclients") {
                            foreach ($Cd4eabf7ecf553f46c17f0bd5a382c46 as $e951d0b9610ba3624d06def5a541cb17 => $c72d66b481d02f854f0bef67db92a547) {
                                $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["capacity"] = (float) ($c72d66b481d02f854f0bef67db92a547["online_clients"] / a78bF8d35765Be2408C50712CE7a43AD::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["total_clients"]);
                            }
                            goto fb8512650313d24ebbda99a7e541af4a;
                        }
                        if (a78bF8D35765Be2408C50712cE7a43ad::$settings["split_by"] == "guar_band") {
                            foreach ($Cd4eabf7ecf553f46c17f0bd5a382c46 as $e951d0b9610ba3624d06def5a541cb17 => $c72d66b481d02f854f0bef67db92a547) {
                                $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["capacity"] = (float) ($c72d66b481d02f854f0bef67db92a547["online_clients"] / A78Bf8d35765bE2408C50712CE7a43AD::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["network_guaranteed_speed"]);
                            }
                            goto c0b522ed318d2cbfd86f7db3c745e349;
                        }
                        foreach ($Cd4eabf7ecf553f46c17f0bd5a382c46 as $e951d0b9610ba3624d06def5a541cb17 => $c72d66b481d02f854f0bef67db92a547) {
                            $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["capacity"] = $c72d66b481d02f854f0bef67db92a547["online_clients"];
                        }
                        c0b522ed318d2cbfd86f7db3c745e349:
                        fb8512650313d24ebbda99a7e541af4a:
                        if (!(a78bF8D35765BE2408c50712Ce7A43ad::$settings["online_capacity_interval"] != 0)) {
                            goto E97a1b9abcd5b8e675ce561fae0f23ab;
                        }
                        file_put_contents("TMP_DIRservers_capacity", json_encode($Cd4eabf7ecf553f46c17f0bd5a382c46), LOCK_EX);
                        E97a1b9abcd5b8e675ce561fae0f23ab:
                        Dcb6013d6c011b31fa12d890c6f527c4:
                        foreach ($e3215fa97db12812ee074d6c110dea4b as $e951d0b9610ba3624d06def5a541cb17) {
                            $Fe028c63f38ae95c5a00bf47dbfb97a9 = isset($Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["online_clients"]) ? $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["online_clients"] : 0;
                            if (!($Fe028c63f38ae95c5a00bf47dbfb97a9 == 0)) {
                                goto F83aff35a0b0217600a2ea48f186bf3e;
                            }
                            $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["capacity"] = 0;
                            F83aff35a0b0217600a2ea48f186bf3e:
                            $aab0f9a311e1a69945f2338c5651dd87[$e951d0b9610ba3624d06def5a541cb17] = A78bf8D35765BE2408c50712cE7a43aD::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["total_clients"] > 0 && A78Bf8D35765be2408C50712CE7A43ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["total_clients"] > $Fe028c63f38ae95c5a00bf47dbfb97a9 ? $Cd4eabf7ecf553f46c17f0bd5a382c46[$e951d0b9610ba3624d06def5a541cb17]["capacity"] : false;
                        }
                        $aab0f9a311e1a69945f2338c5651dd87 = array_filter($aab0f9a311e1a69945f2338c5651dd87, "is_numeric");
                        if (empty($aab0f9a311e1a69945f2338c5651dd87)) {
                            E310f9d1d479044c3f38d4e3940098c7:
                            return false;
                        }
                        $aeab45b2c8e6c4f72bec66f6f1a380c0 = array_keys($aab0f9a311e1a69945f2338c5651dd87);
                        $C3a0e56f71bc74a3da1fc67955fac9a6 = array_values($aab0f9a311e1a69945f2338c5651dd87);
                        array_multisort($C3a0e56f71bc74a3da1fc67955fac9a6, SORT_ASC, $aeab45b2c8e6c4f72bec66f6f1a380c0, SORT_ASC);
                        $aab0f9a311e1a69945f2338c5651dd87 = array_combine($aeab45b2c8e6c4f72bec66f6f1a380c0, $C3a0e56f71bc74a3da1fc67955fac9a6);
                        if ($F1350a5569e4b73d2f9cb26483f2a0c1 == "rtmp" && array_key_exists(SERVER_ID, $aab0f9a311e1a69945f2338c5651dd87)) {
                            $B0e9c71612dc0f9cbfac184b33ec7cae = SERVER_ID;
                            goto Abfdd728972a2798d5f98e139390ecf3;
                        }
                        if ($a8df9f055e91a1e9240230b69af85555["force_server_id"] != 0 and array_key_exists($a8df9f055e91a1e9240230b69af85555["force_server_id"], $aab0f9a311e1a69945f2338c5651dd87)) {
                            $B0e9c71612dc0f9cbfac184b33ec7cae = $a8df9f055e91a1e9240230b69af85555["force_server_id"];
                            goto e89ca2920e0492def23f0ac978bab6ac;
                        }
                        $C8a559944c9ad8d120b437a065024840 = array();
                        foreach (array_keys($aab0f9a311e1a69945f2338c5651dd87) as $e951d0b9610ba3624d06def5a541cb17) {
                            if (A78bF8d35765Be2408c50712ce7a43ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["enable_geoip"] == 1) {
                                if (in_array($A75f2436a5614184bfe3442ddd050ec5, a78BF8D35765Be2408c50712ce7a43Ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["geoip_countries"])) {
                                    $B0e9c71612dc0f9cbfac184b33ec7cae = $e951d0b9610ba3624d06def5a541cb17;
                                    goto e05f1b72bef6daaab8430644e6cb5ae4;
                                }
                                if (A78Bf8d35765be2408c50712cE7A43AD::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["geoip_type"] == "strict") {
                                    unset($aab0f9a311e1a69945f2338c5651dd87[$e951d0b9610ba3624d06def5a541cb17]);
                                    goto Fd7c8e366330c6c5185231676a8c2a7d;
                                }
                                $C8a559944c9ad8d120b437a065024840[$e951d0b9610ba3624d06def5a541cb17] = a78bf8d35765bE2408C50712ce7a43aD::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["geoip_type"] == "low_priority" ? 1 : 2;
                                Fd7c8e366330c6c5185231676a8c2a7d:
                                D60a6b55cfd678a3cab0a9b2bb62cc0b:
                                goto df1dab1529b7af4fa42d1ac9d461f6c7;
                            }
                            if (a78bf8D35765Be2408c50712Ce7a43ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["enable_isp"] == 1) {
                                if (in_array($Cf735adc0fa7bac523a6d09af79aa459, a78Bf8d35765Be2408C50712Ce7A43ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["isp_names"])) {
                                    $B0e9c71612dc0f9cbfac184b33ec7cae = $e951d0b9610ba3624d06def5a541cb17;
                                    goto e05f1b72bef6daaab8430644e6cb5ae4;
                                }
                                if (a78bF8D35765Be2408C50712cE7A43Ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["isp_type"] == "strict") {
                                    unset($aab0f9a311e1a69945f2338c5651dd87[$e951d0b9610ba3624d06def5a541cb17]);
                                    goto F8729fb2e150ebd9eaececa784c85daa;
                                }
                                $C8a559944c9ad8d120b437a065024840[$e951d0b9610ba3624d06def5a541cb17] = a78bF8d35765BE2408C50712CE7a43Ad::$StreamingServers[$e951d0b9610ba3624d06def5a541cb17]["isp_type"] == "low_priority" ? 1 : 2;
                                F8729fb2e150ebd9eaececa784c85daa:
                                b5531bc8ac92447022ab49da65c2e7d0:
                                goto D5d6dab8f9060fc54285b93725e4084d;
                            }
                            $C8a559944c9ad8d120b437a065024840[$e951d0b9610ba3624d06def5a541cb17] = 1;
                            D5d6dab8f9060fc54285b93725e4084d:
                            df1dab1529b7af4fa42d1ac9d461f6c7:
                        }
                        e05f1b72bef6daaab8430644e6cb5ae4:
                        if (!(empty($C8a559944c9ad8d120b437a065024840) && empty($B0e9c71612dc0f9cbfac184b33ec7cae))) {
                            $B0e9c71612dc0f9cbfac184b33ec7cae = empty($B0e9c71612dc0f9cbfac184b33ec7cae) ? array_search(min($C8a559944c9ad8d120b437a065024840), $C8a559944c9ad8d120b437a065024840) : $B0e9c71612dc0f9cbfac184b33ec7cae;
                            e89ca2920e0492def23f0ac978bab6ac:
                            Abfdd728972a2798d5f98e139390ecf3:
                            if ($B0e9c71612dc0f9cbfac184b33ec7cae != SERVER_ID) {
                                if ($a28758c1ab974badfc544e11aaf19a57 == "live") {
                                    $D4a67bbd52a22a102a646011a4bec962 = $F1350a5569e4b73d2f9cb26483f2a0c1 == "m3u8" ? 0 : time() + 6;
                                    goto d511f32e191999fa5557c987210c2f7f;
                                }
                                $Cb08b127bfe426d7f3ccbd3e38f05471 = json_decode($c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["stream_info"], true);
                                $D4a67bbd52a22a102a646011a4bec962 = time() + (int) $Cb08b127bfe426d7f3ccbd3e38f05471["of_duration"];
                                d511f32e191999fa5557c987210c2f7f:
                                $e3874676e9103a9996301beac4efcde2 = array("hash" => md5(json_encode(array("stream_id" => $streamId, "user_id" => $a8df9f055e91a1e9240230b69af85555["id"], "username" => $a8df9f055e91a1e9240230b69af85555["username"], "password" => $a8df9f055e91a1e9240230b69af85555["password"], "user_ip" => $f4889efa84e1f2e30e5e9780973f68cb, "live_streaming_pass" => a78bf8d35765BE2408c50712CE7a43Ad::$settings["live_streaming_pass"], "pid" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["pid"], "external_device" => $a349f0750f0a814bd31ec4b3da51da95, "on_demand" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["on_demand"], "isp" => $Cf735adc0fa7bac523a6d09af79aa459, "bitrate" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["bitrate"], "country" => $A75f2436a5614184bfe3442ddd050ec5, "extension" => $F1350a5569e4b73d2f9cb26483f2a0c1, "is_restreamer" => $a8df9f055e91a1e9240230b69af85555["is_restreamer"], "max_connections" => $a8df9f055e91a1e9240230b69af85555["max_connections"], "monitor_pid" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["monitor_pid"], "time" => $D4a67bbd52a22a102a646011a4bec962))), "stream_id" => $streamId, "user_id" => $a8df9f055e91a1e9240230b69af85555["id"], "time" => $D4a67bbd52a22a102a646011a4bec962, "pid" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["pid"], "external_device" => $a349f0750f0a814bd31ec4b3da51da95, "on_demand" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["on_demand"], "isp" => $Cf735adc0fa7bac523a6d09af79aa459, "bitrate" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["bitrate"], "country" => $A75f2436a5614184bfe3442ddd050ec5, "extension" => $F1350a5569e4b73d2f9cb26483f2a0c1, "is_restreamer" => $a8df9f055e91a1e9240230b69af85555["is_restreamer"], "max_connections" => $a8df9f055e91a1e9240230b69af85555["max_connections"], "monitor_pid" => $c3a18c26bfa971a25d2e6ada870ff735["servers"][$B0e9c71612dc0f9cbfac184b33ec7cae]["monitor_pid"]);
                                $Ad100f7d10d8567e78ddc1e86e51e4a9 = substr($_SERVER["REQUEST_URI"], 1);
                                $cb8983ea8c2dc44d7be007079a71c336 = substr_count($Ad100f7d10d8567e78ddc1e86e51e4a9, "?") == 0 ? "?" : "&";
                                header("Location: " . A78bF8d35765be2408c50712CE7A43aD::$StreamingServers[$B0e9c71612dc0f9cbfac184b33ec7cae]["site_url"] . $Ad100f7d10d8567e78ddc1e86e51e4a9 . $cb8983ea8c2dc44d7be007079a71c336 . "token=" . base64_encode(EAAB451EF7a60c6d480e43b6c15a14a1(json_encode($e3874676e9103a9996301beac4efcde2), md5(a78bf8D35765Be2408C50712Ce7a43Ad::$settings["crypt_load_balancing"]))));
                                die;
                            }
                            return array_merge($c3a18c26bfa971a25d2e6ada870ff735["info"], $c3a18c26bfa971a25d2e6ada870ff735["servers"][SERVER_ID]);
                        }
                        return false;
                    }
                    return false;
                }
                header("Location: " . str_replace(" ", "%20", json_decode($c3a18c26bfa971a25d2e6ada870ff735["info"]["stream_source"], true)[0]));
                die;
            }
            return false;
        }
        self::$ipTV_db->query("SELECT `tv_archive_server_id`,`tv_archive_duration` FROM `streams` WHERE `id` = '%d'", $streamId);
        if (!(self::$ipTV_db->getRowCount() > 0)) {
            goto fe44649b515c4ec3e22b8a3cf1fc4d22;
        }
        $c72d66b481d02f854f0bef67db92a547 = self::$ipTV_db->F1eD191D78470660EDfF4A007696BC1F();
        if (!($c72d66b481d02f854f0bef67db92a547["tv_archive_duration"] > 0 && $c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"] > 0 && array_key_exists($c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"], A78bF8D35765BE2408C50712Ce7a43AD::$StreamingServers))) {
            Ec3c82ae49cdcc8ab3c7882a72a65387:
            fe44649b515c4ec3e22b8a3cf1fc4d22:
            return false;
        }
        if ($c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"] != SERVER_ID) {
            parse_str($_SERVER["QUERY_STRING"], $Cc31a34e0b1fa157d875f9946912d9fa);
            $D4a67bbd52a22a102a646011a4bec962 = time() + $Cc31a34e0b1fa157d875f9946912d9fa["duration"] * 60;
            $e3874676e9103a9996301beac4efcde2 = array("hash" => md5(json_encode(array("user_id" => $a8df9f055e91a1e9240230b69af85555["id"], "username" => $a8df9f055e91a1e9240230b69af85555["username"], "password" => $a8df9f055e91a1e9240230b69af85555["password"], "user_ip" => $f4889efa84e1f2e30e5e9780973f68cb, "live_streaming_pass" => a78bF8D35765bE2408C50712cE7a43Ad::$settings["live_streaming_pass"], "external_device" => $a349f0750f0a814bd31ec4b3da51da95, "isp" => $Cf735adc0fa7bac523a6d09af79aa459, "country" => $A75f2436a5614184bfe3442ddd050ec5, "stream_id" => $streamId, "start" => $Cc31a34e0b1fa157d875f9946912d9fa["start"], "duration" => $Cc31a34e0b1fa157d875f9946912d9fa["duration"], "extension" => $Cc31a34e0b1fa157d875f9946912d9fa["extension"], "time" => $D4a67bbd52a22a102a646011a4bec962))), "user_id" => $a8df9f055e91a1e9240230b69af85555["id"], "username" => $a8df9f055e91a1e9240230b69af85555["username"], "password" => $a8df9f055e91a1e9240230b69af85555["password"], "time" => $D4a67bbd52a22a102a646011a4bec962, "external_device" => $a349f0750f0a814bd31ec4b3da51da95, "isp" => $Cf735adc0fa7bac523a6d09af79aa459, "country" => $A75f2436a5614184bfe3442ddd050ec5, "stream_id" => $streamId, "start" => $Cc31a34e0b1fa157d875f9946912d9fa["start"], "duration" => $Cc31a34e0b1fa157d875f9946912d9fa["duration"], "extension" => $Cc31a34e0b1fa157d875f9946912d9fa["extension"]);
            $Ad100f7d10d8567e78ddc1e86e51e4a9 = substr($_SERVER["REQUEST_URI"], 1);
            header("Location: " . A78bF8D35765be2408c50712Ce7A43aD::$StreamingServers[$c72d66b481d02f854f0bef67db92a547["tv_archive_server_id"]]["site_url"] . "streaming/timeshift.php?token=" . base64_encode(eaAB451ef7A60C6d480E43b6c15A14A1(json_encode($e3874676e9103a9996301beac4efcde2), md5(A78bF8d35765BE2408c50712Ce7A43AD::$settings["crypt_load_balancing"]))));
            die;
        }
        return true;
    }
    public static function eC7E013cf424bDF03238C1d46AB2a9Ae($streamId, $a65cbae81b158857c4230683ea812050 = array(), $a28758c1ab974badfc544e11aaf19a57 = "movie") {
        if ($a28758c1ab974badfc544e11aaf19a57 == "movie") {
            return in_array($streamId, $a65cbae81b158857c4230683ea812050);
        }
        if ($a28758c1ab974badfc544e11aaf19a57 == "series") {
            self::$ipTV_db->query("SELECT series_id FROM `series_episodes` WHERE `stream_id` = '%d' LIMIT 1", $streamId);
            if (!(self::$ipTV_db->getRowCount() > 0)) {
                goto D403ce1f0eb34d70b71bf05231681a79;
            }
            return in_array(self::$ipTV_db->b98ce8B3899e362093173CC5EB4146b9(), $a65cbae81b158857c4230683ea812050);
        }
        D403ce1f0eb34d70b71bf05231681a79:
        a2519e01e648e11947db8d70937f4195:
        return false;
    }
    public static function E5550592AA298DD1D5ee59cdcE063A12($E38668abaa324e464e266fb7b7e784b1 = null, $f6806488699d3315dc5dc1e27a401b3e = null, $password = null, $Fa9d0275367287eb0662f130e93d3a25 = false, $C1c49b38f7480b5e67acaf5b0185dc21 = false, $F7b34d6358a2bbb674f79a68e942b7e2 = false, $a28758c1ab974badfc544e11aaf19a57 = array(), $B5e1c013996afcec27bf828245c3ec37 = false, $f4889efa84e1f2e30e5e9780973f68cb = '', $userAgent = '', $a8851ef591e0cdd9aad6ec4f7bd4b160 = array(), $Cd2953f76721ad9589ab3d88c42b62b9 = '', $streamId = 0) {
        if (empty($E38668abaa324e464e266fb7b7e784b1)) {
            self::$ipTV_db->query("SELECT * FROM `users` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1", $f6806488699d3315dc5dc1e27a401b3e, $password);
            goto ba15e6818a3efe445f4f9693713b0a63;
        }
        self::$ipTV_db->query("SELECT * FROM `users` WHERE `id` = '%d'", $E38668abaa324e464e266fb7b7e784b1);
        ba15e6818a3efe445f4f9693713b0a63:
        if (!(self::$ipTV_db->getRowCount() > 0)) {
            return false;
        }
        $a8df9f055e91a1e9240230b69af85555 = self::$ipTV_db->F1eD191d78470660eDFf4A007696bC1F();
        if (!(A78Bf8D35765be2408C50712cE7A43Ad::$settings["case_sensitive_line"] == 1 && !empty($f6806488699d3315dc5dc1e27a401b3e) && !empty($password))) {
            goto ec8948a763869b196c6edd56f02ebd47;
        }
        if (!($a8df9f055e91a1e9240230b69af85555["username"] != $f6806488699d3315dc5dc1e27a401b3e || $a8df9f055e91a1e9240230b69af85555["password"] != $password)) {
            ec8948a763869b196c6edd56f02ebd47:
            if (!(A78bf8d35765bE2408c50712ce7a43ad::$settings["county_override_1st"] == 1 && empty($a8df9f055e91a1e9240230b69af85555["forced_country"]) && !empty($f4889efa84e1f2e30e5e9780973f68cb) && $a8df9f055e91a1e9240230b69af85555["max_connections"] == 1)) {
                goto f09cc905c8975d30745e50905000379b;
            }
            $a8df9f055e91a1e9240230b69af85555["forced_country"] = geoip_country_code_by_name($f4889efa84e1f2e30e5e9780973f68cb);
            self::$ipTV_db->query("UPDATE `users` SET `forced_country` = '%s' WHERE `id` = '%d'", $a8df9f055e91a1e9240230b69af85555["forced_country"], $a8df9f055e91a1e9240230b69af85555["id"]);
            f09cc905c8975d30745e50905000379b:
            if (!($a8df9f055e91a1e9240230b69af85555["is_mag"] == 1 && a78Bf8D35765BE2408c50712Ce7A43aD::$settings["mag_security"] == 1)) {
                goto A4a7aff7f29c6ec2e57bfd6bad6e63a5;
            }
            if (!empty($a8df9f055e91a1e9240230b69af85555["play_token"]) && !empty($Cd2953f76721ad9589ab3d88c42b62b9)) {
                list($accessToken, $B96676565d19827b6e2eda6db94167c0, $cced8089119eaa83c17b19ea19d9af22) = explode(":", $a8df9f055e91a1e9240230b69af85555["play_token"]);
                if ($accessToken == $Cd2953f76721ad9589ab3d88c42b62b9 && $B96676565d19827b6e2eda6db94167c0 >= time() && $cced8089119eaa83c17b19ea19d9af22 == $streamId) {
                    goto bdcd318a4e6cd52c118c39713c3b9f6c;
                }
                $a8df9f055e91a1e9240230b69af85555["mag_invalid_token"] = true;
                bdcd318a4e6cd52c118c39713c3b9f6c:
                goto Eeef57bd06b00f1a39f96f869e8a433b;
            }
            $a8df9f055e91a1e9240230b69af85555["mag_invalid_token"] = true;
            Eeef57bd06b00f1a39f96f869e8a433b:
            A4a7aff7f29c6ec2e57bfd6bad6e63a5:
            $a8df9f055e91a1e9240230b69af85555["bouquet"] = json_decode($a8df9f055e91a1e9240230b69af85555["bouquet"], true);
            $a8df9f055e91a1e9240230b69af85555["allowed_ips"] = @array_filter(array_map("trim", json_decode($a8df9f055e91a1e9240230b69af85555["allowed_ips"], true)));
            $a8df9f055e91a1e9240230b69af85555["allowed_ua"] = @array_filter(array_map("trim", json_decode($a8df9f055e91a1e9240230b69af85555["allowed_ua"], true)));
            if ($F7b34d6358a2bbb674f79a68e942b7e2) {
                self::$ipTV_db->query("SELECT COUNT(`activity_id`) FROM `user_activity_now` WHERE `user_id` = '%d'", $a8df9f055e91a1e9240230b69af85555["id"]);
                $a8df9f055e91a1e9240230b69af85555["active_cons"] = self::$ipTV_db->B98CE8b3899E362093173CC5eB4146b9();
                if (!($a8df9f055e91a1e9240230b69af85555["max_connections"] == 1 && a78Bf8d35765Be2408c50712CE7a43Ad::$settings["disallow_2nd_ip_con"] == 1 && $a8df9f055e91a1e9240230b69af85555["active_cons"] > 0 && !empty($f4889efa84e1f2e30e5e9780973f68cb))) {
                    goto cec17e215e6ada13315b8a96c290b35e;
                }
                self::$ipTV_db->query("SELECT user_ip FROM `user_activity_now` WHERE `user_id` = '%d' LIMIT 1", $a8df9f055e91a1e9240230b69af85555["id"]);
                if (!(self::$ipTV_db->getRowCount() > 0)) {
                    goto d27bc04afcffd81351093f9008498dc7;
                }
                $C529401a6487f7fc473dd0ec948c66c8 = self::$ipTV_db->B98cE8b3899e362093173cC5eb4146B9();
                if (!($C529401a6487f7fc473dd0ec948c66c8 != $f4889efa84e1f2e30e5e9780973f68cb)) {
                    goto B5d4391b192de3af44f6814ffe1ead92;
                }
                $a8df9f055e91a1e9240230b69af85555["ip_limit_reached"] = 1;
                B5d4391b192de3af44f6814ffe1ead92:
                d27bc04afcffd81351093f9008498dc7:
                cec17e215e6ada13315b8a96c290b35e:
                $a8df9f055e91a1e9240230b69af85555["pair_line_info"] = array();
                if (is_null($a8df9f055e91a1e9240230b69af85555["pair_id"])) {
                    goto c8ad298c2087c7240ac59f239f61fb2e;
                }
                self::$ipTV_db->query("SELECT COUNT(`activity_id`) FROM `user_activity_now` WHERE `user_id` = '%d'", $a8df9f055e91a1e9240230b69af85555["pair_id"]);
                $a8df9f055e91a1e9240230b69af85555["pair_line_info"]["active_cons"] = self::$ipTV_db->b98Ce8b3899E362093173cc5eB4146B9();
                self::$ipTV_db->query("SELECT max_connections FROM `users` WHERE `id` = '%d'", $a8df9f055e91a1e9240230b69af85555["pair_id"]);
                $a8df9f055e91a1e9240230b69af85555["pair_line_info"]["max_connections"] = self::$ipTV_db->B98cE8b3899E362093173cc5Eb4146B9();
                c8ad298c2087c7240ac59f239f61fb2e:
                goto Ecdeae823e2553da93e6f3e571b0351a;
            }
            $a8df9f055e91a1e9240230b69af85555["active_cons"] = "N/A";
            Ecdeae823e2553da93e6f3e571b0351a:
            if (file_exists("TMP_DIRuser_output" . $a8df9f055e91a1e9240230b69af85555["id"])) {
                $a8df9f055e91a1e9240230b69af85555["output_formats"] = unserialize(file_get_contents("TMP_DIRuser_output" . $a8df9f055e91a1e9240230b69af85555["id"]));
                goto B502114d2462a2b04b290c0b3e7a0244;
            }
            self::$ipTV_db->query("SELECT *\n                                    FROM `access_output` t1\n                                    INNER JOIN `user_output` t2 ON t1.access_output_id = t2.access_output_id\n                                    WHERE t2.user_id = '%d'", $a8df9f055e91a1e9240230b69af85555["id"]);
            $a8df9f055e91a1e9240230b69af85555["output_formats"] = self::$ipTV_db->c126fd559932f625CDF6098D86C63880(true, "output_key");
            file_put_contents("TMP_DIRuser_output" . $a8df9f055e91a1e9240230b69af85555["id"], serialize($a8df9f055e91a1e9240230b69af85555["output_formats"]), LOCK_EX);
            B502114d2462a2b04b290c0b3e7a0244:
            $a8df9f055e91a1e9240230b69af85555["con_isp_name"] = $a8df9f055e91a1e9240230b69af85555["con_isp_type"] = null;
            $a8df9f055e91a1e9240230b69af85555["isp_is_server"] = $a8df9f055e91a1e9240230b69af85555["isp_violate"] = 0;
            if (!(a78bF8D35765be2408c50712CE7A43aD::$settings["show_isps"] == 1 && !empty($f4889efa84e1f2e30e5e9780973f68cb))) {
                goto D0412de8a75e99ca3552f03fbcb2f008;
            }
            $Ab8205819e41cfa4d857e6ec45554558 = self::ec7D6405e2E9AB495aFb23100077912c($f4889efa84e1f2e30e5e9780973f68cb, $userAgent);
            if (!is_array($Ab8205819e41cfa4d857e6ec45554558)) {
                goto b9c412a8f73a23a52d0c15cda3e4912b;
            }
            if (empty($Ab8205819e41cfa4d857e6ec45554558["isp_info"]["description"])) {
                goto A551dfd8f74f90d8fd0499f2b5078982;
            }
            $a8df9f055e91a1e9240230b69af85555["con_isp_name"] = $Ab8205819e41cfa4d857e6ec45554558["isp_info"]["description"];
            $de64b4b9800f8407c8499fdc13f8e4f6 = self::A477369ead7aA63E77aD3F4634982a8A($a8df9f055e91a1e9240230b69af85555["con_isp_name"]);
            if (!($a8df9f055e91a1e9240230b69af85555["is_restreamer"] == 0 && a78bf8d35765Be2408C50712CE7a43AD::$settings["block_svp"] == 1 && !empty($Ab8205819e41cfa4d857e6ec45554558["isp_info"]["is_server"]))) {
                goto aa73a3261c0741ab2dedbbf1deccd099;
            }
            $a8df9f055e91a1e9240230b69af85555["isp_is_server"] = $Ab8205819e41cfa4d857e6ec45554558["isp_info"]["is_server"];
            aa73a3261c0741ab2dedbbf1deccd099:
            if (!($a8df9f055e91a1e9240230b69af85555["isp_is_server"] == 1)) {
                goto C36f1b09018272dea575165c202c33f8;
            }
            $a8df9f055e91a1e9240230b69af85555["con_isp_type"] = $Ab8205819e41cfa4d857e6ec45554558["isp_info"]["type"];
            C36f1b09018272dea575165c202c33f8:
            if (!($de64b4b9800f8407c8499fdc13f8e4f6 !== false)) {
                goto Be31f674dffdf9e6b17ab7a11f12d66c;
            }
            $a8df9f055e91a1e9240230b69af85555["isp_is_server"] = $de64b4b9800f8407c8499fdc13f8e4f6 == 1 ? 1 : 0;
            $a8df9f055e91a1e9240230b69af85555["con_isp_type"] = $a8df9f055e91a1e9240230b69af85555["isp_is_server"] == 1 ? "Custom" : null;
            Be31f674dffdf9e6b17ab7a11f12d66c:
            A551dfd8f74f90d8fd0499f2b5078982:
            b9c412a8f73a23a52d0c15cda3e4912b:
            if (!(!empty($a8df9f055e91a1e9240230b69af85555["con_isp_name"]) && A78bF8D35765be2408C50712cE7A43aD::$settings["enable_isp_lock"] == 1 && $a8df9f055e91a1e9240230b69af85555["is_stalker"] == 0 && $a8df9f055e91a1e9240230b69af85555["is_isplock"] == 1 && !empty($a8df9f055e91a1e9240230b69af85555["isp_desc"]) && strtolower($a8df9f055e91a1e9240230b69af85555["con_isp_name"]) != strtolower($a8df9f055e91a1e9240230b69af85555["isp_desc"]))) {
                goto d723e4aa719aee16fc3798fcecf2819d;
            }
            $a8df9f055e91a1e9240230b69af85555["isp_violate"] = 1;
            d723e4aa719aee16fc3798fcecf2819d:
            if (!($a8df9f055e91a1e9240230b69af85555["isp_violate"] == 0 && strtolower($a8df9f055e91a1e9240230b69af85555["con_isp_name"]) != strtolower($a8df9f055e91a1e9240230b69af85555["isp_desc"]))) {
                goto ac33fc440f7f7f506e33791c8189a6e8;
            }
            self::$ipTV_db->query("UPDATE `users` SET `isp_desc` = '%s' WHERE `id` = '%d'", $a8df9f055e91a1e9240230b69af85555["con_isp_name"], $a8df9f055e91a1e9240230b69af85555["id"]);
            ac33fc440f7f7f506e33791c8189a6e8:
            D0412de8a75e99ca3552f03fbcb2f008:
            if (!$Fa9d0275367287eb0662f130e93d3a25) {
                goto Ae3a7fbfc860ed14102e22fa14af74b8;
            }
            $Ff48bb3649e5b84524bd8d318c03db3c = $A92229131e0f5177a362478fd6f3bd8d = array();
            if (!(a78bF8D35765BE2408C50712ce7a43aD::$settings["new_sorting_bouquet"] != 1)) {
                goto def1705bd5fb80508dec2211cd2cdfa4;
            }
            sort($a8df9f055e91a1e9240230b69af85555["bouquet"]);
            def1705bd5fb80508dec2211cd2cdfa4:
            foreach ($a8df9f055e91a1e9240230b69af85555["bouquet"] as $b3c28ce8f38cc88b3954fadda9ca6553) {
                if (!isset(A78bF8d35765BE2408c50712Ce7A43AD::$Bouquets[$b3c28ce8f38cc88b3954fadda9ca6553]["streams"])) {
                    goto Ae97305c7e24168ca652f7bf2620f352;
                }
                $Ff48bb3649e5b84524bd8d318c03db3c = array_merge($Ff48bb3649e5b84524bd8d318c03db3c, a78BF8D35765be2408c50712ce7a43AD::$Bouquets[$b3c28ce8f38cc88b3954fadda9ca6553]["streams"]);
                Ae97305c7e24168ca652f7bf2620f352:
                if (!isset(a78BF8d35765BE2408C50712cE7a43aD::$Bouquets[$b3c28ce8f38cc88b3954fadda9ca6553]["series"])) {
                    goto C177c343e54a22ea3588265047589bca;
                }
                $A92229131e0f5177a362478fd6f3bd8d = array_merge($A92229131e0f5177a362478fd6f3bd8d, A78bF8D35765be2408c50712Ce7A43AD::$Bouquets[$b3c28ce8f38cc88b3954fadda9ca6553]["series"]);
                C177c343e54a22ea3588265047589bca:
            }
            if (A78Bf8d35765be2408c50712CE7A43aD::$settings["new_sorting_bouquet"] != 1) {
                $a8df9f055e91a1e9240230b69af85555["channel_ids"] = array_unique($Ff48bb3649e5b84524bd8d318c03db3c);
                $a8df9f055e91a1e9240230b69af85555["series_ids"] = array_unique($A92229131e0f5177a362478fd6f3bd8d);
                goto Bf8a610691e4d6cca498b48622274f4c;
            }
            $a8df9f055e91a1e9240230b69af85555["channel_ids"] = array_reverse(array_unique(array_reverse($Ff48bb3649e5b84524bd8d318c03db3c)));
            $a8df9f055e91a1e9240230b69af85555["series_ids"] = array_reverse(array_unique(array_reverse($A92229131e0f5177a362478fd6f3bd8d)));
            Bf8a610691e4d6cca498b48622274f4c:
            if (!($C1c49b38f7480b5e67acaf5b0185dc21 && !empty($a8df9f055e91a1e9240230b69af85555["channel_ids"]))) {
                goto Cb7dc9da592a751caa60eec5c1b5c8bd;
            }
            $a8df9f055e91a1e9240230b69af85555["channels"] = array();
            $output = array();
            $e3a76043abaf369f5e7250f23baaf1bb = empty($a28758c1ab974badfc544e11aaf19a57) ? STREAM_TYPE : $a28758c1ab974badfc544e11aaf19a57;
            foreach ($e3a76043abaf369f5e7250f23baaf1bb as $Ca434bcc380e9dbd2a3a588f6c32d84f) {
                if (!file_exists(TMP_DIR . $Ca434bcc380e9dbd2a3a588f6c32d84f . "_main.php")) {
                    goto F74013b606ea4ccaa2fbf85d07446844;
                }
                $d8371b9d492a3a005aae00b32747599b = (include TMP_DIR . $Ca434bcc380e9dbd2a3a588f6c32d84f . "_main.php");
                $output = array_replace($output, $d8371b9d492a3a005aae00b32747599b);
                F74013b606ea4ccaa2fbf85d07446844:
            }
            foreach ($a8df9f055e91a1e9240230b69af85555["channel_ids"] as $b3c28ce8f38cc88b3954fadda9ca6553) {
                if (!isset($output[$b3c28ce8f38cc88b3954fadda9ca6553])) {
                    goto Cbc53590065dc35ae1d391492a5c3f4c;
                }
                if (!$B5e1c013996afcec27bf828245c3ec37) {
                    goto Fbbfd9a91f5062a8f5fcf23a8bacdf23;
                }
                $output[$b3c28ce8f38cc88b3954fadda9ca6553]["is_adult"] = strtolower($output[$b3c28ce8f38cc88b3954fadda9ca6553]["category_name"]) == "for adults" ? 1 : 0;
                Fbbfd9a91f5062a8f5fcf23a8bacdf23:
                $a8df9f055e91a1e9240230b69af85555["channels"][$b3c28ce8f38cc88b3954fadda9ca6553] = $output[$b3c28ce8f38cc88b3954fadda9ca6553];
                Cbc53590065dc35ae1d391492a5c3f4c:
            }
            $output = null;
            if (empty($a8851ef591e0cdd9aad6ec4f7bd4b160["items_per_page"])) {
                goto e223e65fb63c7ad10d76b1b7bec4175d;
            }
            $a8df9f055e91a1e9240230b69af85555["total_found_rows"] = count($a8df9f055e91a1e9240230b69af85555["channels"]);
            $a8df9f055e91a1e9240230b69af85555["channels"] = array_slice($a8df9f055e91a1e9240230b69af85555["channels"], $a8851ef591e0cdd9aad6ec4f7bd4b160["offset"], $a8851ef591e0cdd9aad6ec4f7bd4b160["items_per_page"]);
            e223e65fb63c7ad10d76b1b7bec4175d:
            Cb7dc9da592a751caa60eec5c1b5c8bd:
            Ae3a7fbfc860ed14102e22fa14af74b8:
            return $a8df9f055e91a1e9240230b69af85555;
        }
        return false;
    }
    public static function Bc358DB57D4903bFdDF6652560fae708($Fe9028a70727ba5f6b7129f9352b020c, $a9b4c615c3623cb531f93f87f402ccdc) {
        if (file_exists("TMP_DIRcategories_bouq")) {
            if (is_array($a9b4c615c3623cb531f93f87f402ccdc)) {
                goto Afac3ce0053ed22798fe8a8db15e4a04;
            }
            $a9b4c615c3623cb531f93f87f402ccdc = json_decode($a9b4c615c3623cb531f93f87f402ccdc, true);
            Afac3ce0053ed22798fe8a8db15e4a04:
            $output = unserialize(file_get_contents("TMP_DIRcategories_bouq"));
            foreach ($a9b4c615c3623cb531f93f87f402ccdc as $A4d6fb6268124336b7497e2f7283d227) {
                if (!isset($output[$A4d6fb6268124336b7497e2f7283d227])) {
                    goto Fa52ee302ea175e0439eab89173a11ab;
                }
                if (!in_array($Fe9028a70727ba5f6b7129f9352b020c, $output[$A4d6fb6268124336b7497e2f7283d227])) {
                    Fa52ee302ea175e0439eab89173a11ab:
                }
                return true;
            }
            return false;
        }
        return true;
    }
    public static function f2cBd6b6f59558B819C0cFF8C3B2EF2c($F89e57ebe9caa43312cce5a27bfa9e5c = null, $mac = null, $Fa9d0275367287eb0662f130e93d3a25 = false, $C1c49b38f7480b5e67acaf5b0185dc21 = false, $F7b34d6358a2bbb674f79a68e942b7e2 = false) {
        if (empty($F89e57ebe9caa43312cce5a27bfa9e5c)) {
            self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `mac` = '%s'", base64_encode($mac));
            goto Bcf01ccab863d0c4b6b2eedbf3354c5b;
        }
        self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `mag_id` = '%d'", $F89e57ebe9caa43312cce5a27bfa9e5c);
        Bcf01ccab863d0c4b6b2eedbf3354c5b:
        if (!(self::$ipTV_db->getRowCount() > 0)) {
            return false;
        }
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e = array();
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["mag_device"] = self::$ipTV_db->f1eD191d78470660eDff4a007696bc1F();
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["mag_device"]["mac"] = base64_decode($a9acf1899f95ee2d7eaf6ccc5c6e8d8e["mag_device"]["mac"]);
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["user_info"] = array();
        if (!($a8df9f055e91a1e9240230b69af85555 = self::E5550592Aa298dd1D5EE59Cdce063A12($a9acf1899f95ee2d7eaf6ccc5c6e8d8e["mag_device"]["user_id"], null, null, $Fa9d0275367287eb0662f130e93d3a25, $C1c49b38f7480b5e67acaf5b0185dc21, $F7b34d6358a2bbb674f79a68e942b7e2))) {
            goto a35cef8194cf301a2822a4a9578cc948;
        }
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["user_info"] = $a8df9f055e91a1e9240230b69af85555;
        a35cef8194cf301a2822a4a9578cc948:
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["pair_line_info"] = array();
        if (empty($a9acf1899f95ee2d7eaf6ccc5c6e8d8e["user_info"])) {
            goto E85c70c2b93bc221ac83a581f4827ddc;
        }
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["pair_line_info"] = array();
        if (is_null($a9acf1899f95ee2d7eaf6ccc5c6e8d8e["user_info"]["pair_id"])) {
            goto C9ef3fea46f3e735e386d722c0991dd4;
        }
        if (!($a8df9f055e91a1e9240230b69af85555 = self::E5550592aA298dd1D5ee59cdce063A12($a9acf1899f95ee2d7eaf6ccc5c6e8d8e["user_info"]["pair_id"], null, null, $Fa9d0275367287eb0662f130e93d3a25, $C1c49b38f7480b5e67acaf5b0185dc21, $F7b34d6358a2bbb674f79a68e942b7e2))) {
            goto dcd519d57bbd7a08048e0e076ad0502a;
        }
        $a9acf1899f95ee2d7eaf6ccc5c6e8d8e["pair_line_info"] = $a8df9f055e91a1e9240230b69af85555;
        dcd519d57bbd7a08048e0e076ad0502a:
        C9ef3fea46f3e735e386d722c0991dd4:
        E85c70c2b93bc221ac83a581f4827ddc:
        return $a9acf1899f95ee2d7eaf6ccc5c6e8d8e;
    }
    public static function a2999eEDBe1FF2D9cE52ef5311680Cd4($E84b78040a42ede27e9c6a342a7cf406, $Fa9d0275367287eb0662f130e93d3a25 = false, $C1c49b38f7480b5e67acaf5b0185dc21 = false, $F7b34d6358a2bbb674f79a68e942b7e2 = false) {
        if (empty($E84b78040a42ede27e9c6a342a7cf406["device_id"])) {
            self::$ipTV_db->query("SELECT * FROM `enigma2_devices` WHERE `mac` = '%s'", $E84b78040a42ede27e9c6a342a7cf406["mac"]);
            goto aa38446f9a9535ab42102a607edc1bc4;
        }
        self::$ipTV_db->query("SELECT * FROM `enigma2_devices` WHERE `device_id` = '%d'", $E84b78040a42ede27e9c6a342a7cf406["device_id"]);
        aa38446f9a9535ab42102a607edc1bc4:
        if (!(self::$ipTV_db->getRowCount() > 0)) {
            return false;
        }
        $f80fde69180c88b387a3450bccab89de = array();
        $f80fde69180c88b387a3450bccab89de["enigma2"] = self::$ipTV_db->F1ED191d78470660edFF4a007696bc1F();
        $f80fde69180c88b387a3450bccab89de["user_info"] = array();
        if (!($a8df9f055e91a1e9240230b69af85555 = self::E5550592aA298DD1d5EE59cdce063A12($f80fde69180c88b387a3450bccab89de["enigma2"]["user_id"], null, null, $Fa9d0275367287eb0662f130e93d3a25, $C1c49b38f7480b5e67acaf5b0185dc21, $F7b34d6358a2bbb674f79a68e942b7e2))) {
            goto Db5684deeed7d1b2d20635d03416225e;
        }
        $f80fde69180c88b387a3450bccab89de["user_info"] = $a8df9f055e91a1e9240230b69af85555;
        Db5684deeed7d1b2d20635d03416225e:
        $f80fde69180c88b387a3450bccab89de["pair_line_info"] = array();
        if (empty($f80fde69180c88b387a3450bccab89de["user_info"])) {
            goto f248176ec410a971655a3d553b3ac46a;
        }
        $f80fde69180c88b387a3450bccab89de["pair_line_info"] = array();
        if (is_null($f80fde69180c88b387a3450bccab89de["user_info"]["pair_id"])) {
            goto Dd5db504c422114fd4c511adf1ee79e3;
        }
        if (!($a8df9f055e91a1e9240230b69af85555 = self::e5550592aA298dD1d5Ee59cDce063a12($f80fde69180c88b387a3450bccab89de["user_info"]["pair_id"], null, null, $Fa9d0275367287eb0662f130e93d3a25, $C1c49b38f7480b5e67acaf5b0185dc21, $F7b34d6358a2bbb674f79a68e942b7e2))) {
            goto ede6b39e347c70608d061c4ee24f650e;
        }
        $f80fde69180c88b387a3450bccab89de["pair_line_info"] = $a8df9f055e91a1e9240230b69af85555;
        ede6b39e347c70608d061c4ee24f650e:
        Dd5db504c422114fd4c511adf1ee79e3:
        f248176ec410a971655a3d553b3ac46a:
        return $f80fde69180c88b387a3450bccab89de;
    }
    public static function a813cBD1E7EA2Bb17742dE7BB2392ebF($E38668abaa324e464e266fb7b7e784b1, $d1137e717291f9bcc4c153ac7ea29f57) {
        self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE `user_id` = '%d' ORDER BY activity_id ASC", $E38668abaa324e464e266fb7b7e784b1);
        $E80aae019385d9c9558555fb07017028 = self::$ipTV_db->c126fd559932F625Cdf6098d86C63880();
        $f5ab0145e33718c87b1ade175ab1ec24 = count($E80aae019385d9c9558555fb07017028) - $d1137e717291f9bcc4c153ac7ea29f57 + 1;
        if (!($f5ab0145e33718c87b1ade175ab1ec24 <= 0)) {
            $Fc400afa4288af82b36b1a85c30416c2 = 0;
            $a65cbae81b158857c4230683ea812050 = array();
            $C48e0083a9caa391609a3c645a2ec889 = 0;
            f800ffef6c9b9b805f7a8228580f4529:
            if (!($C48e0083a9caa391609a3c645a2ec889 < count($E80aae019385d9c9558555fb07017028) && $C48e0083a9caa391609a3c645a2ec889 < $f5ab0145e33718c87b1ade175ab1ec24)) {
                if (empty($a65cbae81b158857c4230683ea812050)) {
                    goto B56533dd9877f0824fa3fe9b91a163b2;
                }
                self::$ipTV_db->query("DELETE FROM `user_activity_now` WHERE `activity_id` IN (" . implode(",", $a65cbae81b158857c4230683ea812050) . ")");
                B56533dd9877f0824fa3fe9b91a163b2:
                return $Fc400afa4288af82b36b1a85c30416c2;
            }
            if (!($E80aae019385d9c9558555fb07017028[$C48e0083a9caa391609a3c645a2ec889]["hls_end"] == 1)) {
                if (!self::a1eAe86369aa95a55b4BE332F1e22FE3($E80aae019385d9c9558555fb07017028[$C48e0083a9caa391609a3c645a2ec889], false)) {
                    goto f41b9a9a9b55671be2931c17fb0dd560;
                }
                ++$Fc400afa4288af82b36b1a85c30416c2;
                if (!($E80aae019385d9c9558555fb07017028[$C48e0083a9caa391609a3c645a2ec889]["container"] != "hls")) {
                    goto A1af80a46cd08b74090c567d4751e202;
                }
                $a65cbae81b158857c4230683ea812050[] = $E80aae019385d9c9558555fb07017028[$C48e0083a9caa391609a3c645a2ec889]["activity_id"];
                A1af80a46cd08b74090c567d4751e202:
                f41b9a9a9b55671be2931c17fb0dd560:
                goto d73b683e3d730f23e40f720d79f33bcf;
            }
            d73b683e3d730f23e40f720d79f33bcf:
            $C48e0083a9caa391609a3c645a2ec889++;
            goto f800ffef6c9b9b805f7a8228580f4529;
        }
        return;
    }
    public static function A1Eae86369aa95A55B4bE332f1e22fE3($Cac03b89c9bf5eedf49be049cd3ad8b2, $F5478657dda4770727c6f4f19bcf419c = true) {
        if (!empty($Cac03b89c9bf5eedf49be049cd3ad8b2)) {
            if (!empty($Cac03b89c9bf5eedf49be049cd3ad8b2["activity_id"])) {
                goto B8a0dc3851f6109173f33cb9a2a66746;
            }
            self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE `activity_id` = '%d'", $Cac03b89c9bf5eedf49be049cd3ad8b2);
            $Cac03b89c9bf5eedf49be049cd3ad8b2 = self::$ipTV_db->f1Ed191D78470660edfF4A007696bC1f();
            B8a0dc3851f6109173f33cb9a2a66746:
            if (!empty($Cac03b89c9bf5eedf49be049cd3ad8b2)) {
                if ($Cac03b89c9bf5eedf49be049cd3ad8b2["container"] == "rtmp") {
                    if ($Cac03b89c9bf5eedf49be049cd3ad8b2["server_id"] == SERVER_ID) {
                        shell_exec("wget --timeout=2 -O /dev/null -o /dev/null \"" . A78bF8D35765BE2408c50712cE7a43AD::$StreamingServers[SERVER_ID]["rtmp_mport_url"] . "control/drop/client?clientid={$Cac03b89c9bf5eedf49be049cd3ad8b2["pid"]}\" >/dev/null 2>/dev/null &");
                        goto A166f13caba73ef46cba6e84fb8daa72;
                    }
                    self::$ipTV_db->query("INSERT INTO `signals` (`pid`,`server_id`,`rtmp`,`time`) VALUES('%d','%d','%d',UNIX_TIMESTAMP())", $Cac03b89c9bf5eedf49be049cd3ad8b2["pid"], $Cac03b89c9bf5eedf49be049cd3ad8b2["server_id"], 1);
                    A166f13caba73ef46cba6e84fb8daa72:
                    goto Db2da463655480a0a63d9e81a0940526;
                }
                if ($Cac03b89c9bf5eedf49be049cd3ad8b2["container"] == "hls") {
                    if ($F5478657dda4770727c6f4f19bcf419c) {
                        goto fe25f286d9aef47729c4c8e12bd1462c;
                    }
                    self::$ipTV_db->query("UPDATE `user_activity_now` SET `hls_end` = 1 WHERE `activity_id` = '%d'", $Cac03b89c9bf5eedf49be049cd3ad8b2["activity_id"]);
                    fe25f286d9aef47729c4c8e12bd1462c:
                    goto E0afc40c3eeabe7fb07301b8a5e7bf9c;
                }
                if ($Cac03b89c9bf5eedf49be049cd3ad8b2["server_id"] == SERVER_ID) {
                    shell_exec("kill -9 {$Cac03b89c9bf5eedf49be049cd3ad8b2["pid"]} >/dev/null 2>/dev/null &");
                    goto ca7c92fdfb7e6672cbd36405c9ea020e;
                }
                self::$ipTV_db->query("INSERT INTO `signals` (`pid`,`server_id`,`time`) VALUES('%d','%d',UNIX_TIMESTAMP())", $Cac03b89c9bf5eedf49be049cd3ad8b2["pid"], $Cac03b89c9bf5eedf49be049cd3ad8b2["server_id"]);
                ca7c92fdfb7e6672cbd36405c9ea020e:
                E0afc40c3eeabe7fb07301b8a5e7bf9c:
                Db2da463655480a0a63d9e81a0940526:
                if (!$F5478657dda4770727c6f4f19bcf419c) {
                    goto bd189efc7ee5279caf67d1b6a6bb70b9;
                }
                self::$ipTV_db->query("DELETE FROM `user_activity_now` WHERE `activity_id` = '%d'", $Cac03b89c9bf5eedf49be049cd3ad8b2["activity_id"]);
                bd189efc7ee5279caf67d1b6a6bb70b9:
                self::A49C2fB1ebA096C52a352A85C8D09D8d($Cac03b89c9bf5eedf49be049cd3ad8b2["server_id"], $Cac03b89c9bf5eedf49be049cd3ad8b2["user_id"], $Cac03b89c9bf5eedf49be049cd3ad8b2["stream_id"], $Cac03b89c9bf5eedf49be049cd3ad8b2["date_start"], $Cac03b89c9bf5eedf49be049cd3ad8b2["user_agent"], $Cac03b89c9bf5eedf49be049cd3ad8b2["user_ip"], $Cac03b89c9bf5eedf49be049cd3ad8b2["container"], $Cac03b89c9bf5eedf49be049cd3ad8b2["geoip_country_code"], $Cac03b89c9bf5eedf49be049cd3ad8b2["isp"], $Cac03b89c9bf5eedf49be049cd3ad8b2["external_device"]);
                return true;
            }
            return false;
        }
        return false;
    }
    public static function BA58BB30969E80D158Da7Db06421D0d8($Bc7d327b1510891329ca9859db27320f) {
        if (!empty($Bc7d327b1510891329ca9859db27320f)) {
            self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE `container` = 'rtmp' AND `pid` = '%d' AND `server_id` = '%d'", $Bc7d327b1510891329ca9859db27320f, SERVER_ID);
            if (!(self::$ipTV_db->getRowCount() > 0)) {
                return false;
            }
            $Cac03b89c9bf5eedf49be049cd3ad8b2 = self::$ipTV_db->f1eD191D78470660eDFf4a007696bc1F();
            self::$ipTV_db->query("DELETE FROM `user_activity_now` WHERE `activity_id` = '%d'", $Cac03b89c9bf5eedf49be049cd3ad8b2["activity_id"]);
            self::a49c2FB1Eba096c52a352a85C8d09D8d($Cac03b89c9bf5eedf49be049cd3ad8b2["server_id"], $Cac03b89c9bf5eedf49be049cd3ad8b2["user_id"], $Cac03b89c9bf5eedf49be049cd3ad8b2["stream_id"], $Cac03b89c9bf5eedf49be049cd3ad8b2["date_start"], $Cac03b89c9bf5eedf49be049cd3ad8b2["user_agent"], $Cac03b89c9bf5eedf49be049cd3ad8b2["user_ip"], $Cac03b89c9bf5eedf49be049cd3ad8b2["container"], $Cac03b89c9bf5eedf49be049cd3ad8b2["geoip_country_code"], $Cac03b89c9bf5eedf49be049cd3ad8b2["isp"], $Cac03b89c9bf5eedf49be049cd3ad8b2["external_device"]);
            return true;
        }
        return false;
    }
    public static function A49c2Fb1ebA096C52a352A85C8d09D8D($e951d0b9610ba3624d06def5a541cb17, $E38668abaa324e464e266fb7b7e784b1, $streamId, $start, $userAgent, $f4889efa84e1f2e30e5e9780973f68cb, $F1350a5569e4b73d2f9cb26483f2a0c1, $d453b0f7262b2c8794e5bc6e4802d449, $A1849c9433e6c0ae1d4aeafaf150d131, $a349f0750f0a814bd31ec4b3da51da95 = '') {
        if (!(A78bF8d35765bE2408c50712cE7a43ad::$settings["save_closed_connection"] == 0)) {
            $Cac03b89c9bf5eedf49be049cd3ad8b2 = array("user_id" => intval($E38668abaa324e464e266fb7b7e784b1), "stream_id" => intval($streamId), "server_id" => intval($e951d0b9610ba3624d06def5a541cb17), "date_start" => intval($start), "user_agent" => $userAgent, "user_ip" => htmlentities($f4889efa84e1f2e30e5e9780973f68cb), "date_end" => time(), "container" => $F1350a5569e4b73d2f9cb26483f2a0c1, "geoip_country_code" => $d453b0f7262b2c8794e5bc6e4802d449, "isp" => $A1849c9433e6c0ae1d4aeafaf150d131, "external_device" => htmlentities($a349f0750f0a814bd31ec4b3da51da95));
            file_put_contents("TMP_DIRoffline_cons", base64_encode(json_encode($Cac03b89c9bf5eedf49be049cd3ad8b2)) . "\n", "NOOO_EXPEND");

            return;
        }
        return;
    }
    public static function c1591643EAfdAaE33ff6E69e5e49D651($streamId, $A46fd5eee12ebb82d63744d80a987c05, $b4af8b82d0e004d138b6f62947d7a1fa, $f090771de8f10383e371fc62e73e226f, $d76067cf9572f7a6691c85c12faf2a29 = '', $aa1dc37d3856d0124e1c6669bb98c933 = false) {
        if (!(a78BF8d35765be2408C50712Ce7a43AD::$settings["client_logs_save"] == 0 && !$aa1dc37d3856d0124e1c6669bb98c933)) {
            $userAgent = !empty($_SERVER["HTTP_USER_AGENT"]) ? htmlentities($_SERVER["HTTP_USER_AGENT"]) : '';
            $edd8801e02c613e4742a16fe132ace86 = empty($_SERVER["QUERY_STRING"]) ? '' : $_SERVER["QUERY_STRING"];
            $d76067cf9572f7a6691c85c12faf2a29 = array("user_id" => $A46fd5eee12ebb82d63744d80a987c05, "stream_id" => $streamId, "action" => $b4af8b82d0e004d138b6f62947d7a1fa, "query_string" => htmlentities($_SERVER["QUERY_STRING"]), "user_agent" => $userAgent, "user_ip" => $f090771de8f10383e371fc62e73e226f, "time" => time(), "extra_data" => $d76067cf9572f7a6691c85c12faf2a29);
            file_put_contents("TMP_DIRclient_request.log", base64_encode(json_encode($d76067cf9572f7a6691c85c12faf2a29)) . "\n", FILE_APPEND);

            return;
        }
        return;
    }
    public static function b8430212cC8301200A4976571DbA202C($Bb37b848bec813a5c13ea0b018962c40, $af46c19ad71d32d62575a30b0e1b2f2a = 0) {
        if (!file_exists($Bb37b848bec813a5c13ea0b018962c40)) {
            goto dc64e1b79993e3ee305f00070681c05b;
        }
        $F3803fa85b38b65447e6d438f8e9176a = file_get_contents($Bb37b848bec813a5c13ea0b018962c40);
        if (!preg_match_all("/(.*?).ts/", $F3803fa85b38b65447e6d438f8e9176a, $matches)) {
            e80786e2c95c34cd7733e5fb4fc58a35:
            dc64e1b79993e3ee305f00070681c05b:
            return false;
        }
        if ($af46c19ad71d32d62575a30b0e1b2f2a > 0) {
            $D8eed577376997b90ec084598ddf5bab = intval($af46c19ad71d32d62575a30b0e1b2f2a / 10);
            return array_slice($matches[0], -$D8eed577376997b90ec084598ddf5bab);
        }
        preg_match("/_(.*)\\./", array_pop($matches[0]), $adb24597b0e7956b0f3baad7c260916d);
        return $adb24597b0e7956b0f3baad7c260916d[1];
    }
    public static function B18c6BF534aE0B9B94354DB508D52A48($D556aa916c3639fd698001b7fef2c4ea, $password, $streamId) {
        if (!file_exists($D556aa916c3639fd698001b7fef2c4ea)) {

            return;
        }
        $F3803fa85b38b65447e6d438f8e9176a = file_get_contents($D556aa916c3639fd698001b7fef2c4ea);
        if (!preg_match_all("/(.*?)\\.ts/", $F3803fa85b38b65447e6d438f8e9176a, $matches)) {
            return false;
        }
        foreach ($matches[0] as $A35c84197cc933d0578522463b946147) {
            $F3803fa85b38b65447e6d438f8e9176a = str_replace($A35c84197cc933d0578522463b946147, "/streaming/admin_live.php?password={$password}&extension=m3u8&segment={$A35c84197cc933d0578522463b946147}&stream={$streamId}", $F3803fa85b38b65447e6d438f8e9176a);
        }
        return $F3803fa85b38b65447e6d438f8e9176a;
    }
    public static function E7917f7f55606C448105A9A4016538b9($D556aa916c3639fd698001b7fef2c4ea, $f6806488699d3315dc5dc1e27a401b3e = '', $password = '', $Fa6494e569aed942b375e025f096b099) {
        if (!file_exists($D556aa916c3639fd698001b7fef2c4ea)) {

            return;
        }
        $F3803fa85b38b65447e6d438f8e9176a = file_get_contents($D556aa916c3639fd698001b7fef2c4ea);
        if (!preg_match_all("/(.*?)\\.ts/", $F3803fa85b38b65447e6d438f8e9176a, $matches)) {
            return false;
        }
        foreach ($matches[0] as $A35c84197cc933d0578522463b946147) {
            $accessToken = md5($A35c84197cc933d0578522463b946147 . $f6806488699d3315dc5dc1e27a401b3e . A78bF8d35765BE2408C50712CE7A43AD::$settings["crypt_load_balancing"] . filesize(STREAMS_PATH . $A35c84197cc933d0578522463b946147));
            $F3803fa85b38b65447e6d438f8e9176a = str_replace($A35c84197cc933d0578522463b946147, "/hls/{$f6806488699d3315dc5dc1e27a401b3e}/{$password}/{$Fa6494e569aed942b375e025f096b099}/{$accessToken}/{$A35c84197cc933d0578522463b946147}", $F3803fa85b38b65447e6d438f8e9176a);
        }
        return $F3803fa85b38b65447e6d438f8e9176a;
    }
    public static function DfBDd0f64a97ac684CE49c689D9636Ac($userAgent) {
        $userAgent = strtolower($userAgent);
        $ac3e12febbb571ae6bbc11a06c8f5331 = false;
        foreach (a78bf8d35765Be2408C50712CE7a43aD::$blockedUA as $E7cca48cfca85fc445419a32d7d8f973 => $e5703a07efed268fcb5c4c86a4cab348) {
            if ($e5703a07efed268fcb5c4c86a4cab348["exact_match"] == 1) {
                if (!($e5703a07efed268fcb5c4c86a4cab348["blocked_ua"] == $userAgent)) {
                    goto Fd52ab3d640e0441d09a1768d0ec7444;
                }
                $ac3e12febbb571ae6bbc11a06c8f5331 = $E7cca48cfca85fc445419a32d7d8f973;
                goto df050b7888b0372781dc39c439b3ca3a;
            }
            if (stristr($userAgent, $e5703a07efed268fcb5c4c86a4cab348["blocked_ua"])) {
                $ac3e12febbb571ae6bbc11a06c8f5331 = $E7cca48cfca85fc445419a32d7d8f973;
                goto df050b7888b0372781dc39c439b3ca3a;
            }
            Fd52ab3d640e0441d09a1768d0ec7444:
        }
        df050b7888b0372781dc39c439b3ca3a:
        if (!($ac3e12febbb571ae6bbc11a06c8f5331 > 0)) {

            return;
        }
        self::$ipTV_db->query("UPDATE `blocked_user_agents` SET `attempts_blocked` = `attempts_blocked`+1 WHERE `id` = '%d'", $ac3e12febbb571ae6bbc11a06c8f5331);
        die;
    }
    public static function CdA72bC41975C364bc559db25648a5b2($Bc7d327b1510891329ca9859db27320f, $streamId, $b2157c035e132769495d0acb4e6be575 = PHP_BIN) {
        if (!empty($Bc7d327b1510891329ca9859db27320f)) {
            clearstatcache(true);
            if (!(file_exists("/proc/" . $Bc7d327b1510891329ca9859db27320f) && is_readable("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe") && basename(readlink("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe")) == basename($b2157c035e132769495d0acb4e6be575))) {
                goto a54a5af50f7b29612948d3370eded8ef;
            }
            $ea5780c60b0a2afa62b1d8395f019e9a = trim(file_get_contents("/proc/{$Bc7d327b1510891329ca9859db27320f}/cmdline"));
            if (!($ea5780c60b0a2afa62b1d8395f019e9a == "XtreamCodes[{$streamId}]")) {
                a54a5af50f7b29612948d3370eded8ef:
                return false;
            }
            return true;
        }
        return false;
    }
    public static function C57799E5196664CB99139813250673e2($f4889efa84e1f2e30e5e9780973f68cb) {
        $b63b894b2f9b5aabe135ef4a17f2aed8 = TMP_DIR . md5($f4889efa84e1f2e30e5e9780973f68cb . "cracked");
        if (!file_exists($b63b894b2f9b5aabe135ef4a17f2aed8)) {
            if (!file_exists("TMP_DIRcache_x")) {
                goto aaca902bff37ee49038b830bd898f2a4;
            }
            $E39de148e1c9c7c038772e11158786c8 = json_decode(eAab451Ef7A60C6d480e43b6C15a14A1(base64_decode(file_get_contents("TMP_DIRcache_x")), KEY_CRYPT), true);
            if (!(is_array($E39de148e1c9c7c038772e11158786c8["ips"]) && !empty($E39de148e1c9c7c038772e11158786c8["ips"]) && in_array($f4889efa84e1f2e30e5e9780973f68cb, $E39de148e1c9c7c038772e11158786c8["ips"]))) {
                aaca902bff37ee49038b830bd898f2a4:
                file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, 0);
                return false;
            }
            file_put_contents($b63b894b2f9b5aabe135ef4a17f2aed8, 1);
            return true;
        }
        $f0bdbe56c3b41dee80ecaf635ea527e1 = intval(file_get_contents($b63b894b2f9b5aabe135ef4a17f2aed8));
        return $f0bdbe56c3b41dee80ecaf635ea527e1 == 1 ? true : false;
    }
    public static function F4a9B20600bb9A41Ed2391b0ea000578($Bc7d327b1510891329ca9859db27320f, $streamId) {
        if (!empty($Bc7d327b1510891329ca9859db27320f)) {
            clearstatcache(true);
            if (!(file_exists("/proc/" . $Bc7d327b1510891329ca9859db27320f) && is_readable("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe"))) {
                goto D4f3304acc60839c73eeddc16e813410;
            }
            $ea5780c60b0a2afa62b1d8395f019e9a = trim(file_get_contents("/proc/{$Bc7d327b1510891329ca9859db27320f}/cmdline"));
            if (!($ea5780c60b0a2afa62b1d8395f019e9a == "XtreamCodesDelay[{$streamId}]")) {
                D4f3304acc60839c73eeddc16e813410:
                return false;
            }
            return true;
        }
        return false;
    }
    public static function BCaA9B8a7B46eb36CD507A218fa64474($Bc7d327b1510891329ca9859db27320f, $streamId, $b2157c035e132769495d0acb4e6be575 = FFMPEG_PATH) {
        if (!empty($Bc7d327b1510891329ca9859db27320f)) {
            clearstatcache(true);
            if (!(file_exists("/proc/" . $Bc7d327b1510891329ca9859db27320f) && is_readable("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe") && basename(readlink("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe")) == basename($b2157c035e132769495d0acb4e6be575))) {
                goto E37dfc31f414909530fef350d0d8ff13;
            }
            $ea5780c60b0a2afa62b1d8395f019e9a = trim(file_get_contents("/proc/{$Bc7d327b1510891329ca9859db27320f}/cmdline"));
            if (!stristr($ea5780c60b0a2afa62b1d8395f019e9a, "/{$streamId}_.m3u8")) {
                E37dfc31f414909530fef350d0d8ff13:
                return false;
            }
            return true;
        }
        return false;
    }
    public static function ps_running($Bc7d327b1510891329ca9859db27320f, $b2157c035e132769495d0acb4e6be575) {
        if (!empty($Bc7d327b1510891329ca9859db27320f)) {
            clearstatcache(true);
            if (!(file_exists("/proc/" . $Bc7d327b1510891329ca9859db27320f) && is_readable("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe") && basename(readlink("/proc/" . $Bc7d327b1510891329ca9859db27320f . "/exe")) == basename($b2157c035e132769495d0acb4e6be575))) {
                return false;
            }
            return true;
        }
        return false;
    }
    public static function d0b968cd6CFdf340CA85B1c3d9A40649($Fef8638ed5bbe909f78f718c782ee1aa = 0, $f2ec7ce9da258301346cfc56d0e836ce, $Cca12fe7fb7e87077953f76f574e3128, $F1350a5569e4b73d2f9cb26483f2a0c1 = "ts") {
        if (!($Fef8638ed5bbe909f78f718c782ee1aa == 0 && A78bF8D35765Be2408C50712cE7a43aD::$settings[$f2ec7ce9da258301346cfc56d0e836ce] == 1)) {
            b80be953a08722d7d815bad8defd3b71:
            http_response_code(403);
            die;
        }
        if ($F1350a5569e4b73d2f9cb26483f2a0c1 == "m3u8") {
            $F0329648e3dd07351be94b489e02ddfb = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-MEDIA-SEQUENCE:0\n#EXT-X-ALLOW-CACHE:YES\n#EXT-X-TARGETDURATION:11\n#EXTINF:10.0,\n" . A78BF8D35765be2408c50712cE7A43Ad::$settings[$Cca12fe7fb7e87077953f76f574e3128] . "\n#EXT-X-ENDLIST";
            header("Content-Type: application/x-mpegurl");
            header("Content-Length: " . strlen($F0329648e3dd07351be94b489e02ddfb));
            echo $F0329648e3dd07351be94b489e02ddfb;
            die;
        }
        header("Content-Type: video/mp2t");
        readfile(A78bf8d35765BE2408C50712ce7A43aD::$settings[$Cca12fe7fb7e87077953f76f574e3128]);
        die;
    }
    public static function cFE2E5B7a9107cD2B2Fdb629C199787d($Bb37b848bec813a5c13ea0b018962c40, $Bc7d327b1510891329ca9859db27320f) {
        return self::ps_running($Bc7d327b1510891329ca9859db27320f, FFMPEG_PATH) && file_exists($Bb37b848bec813a5c13ea0b018962c40);
    }
    # get_real_ip_client
    public static function e1F75a50f74A8f4E2129ba474f45d670() {
        return !empty(a78bf8d35765Be2408c50712cE7A43AD::$settings["get_real_ip_client"]) && !empty($_SERVER[a78bf8d35765BE2408c50712CE7A43ad::$settings["get_real_ip_client"]]) ? $_SERVER[a78bF8D35765be2408C50712cE7a43ad::$settings["get_real_ip_client"]] : $_SERVER["REMOTE_ADDR"];
    }
    public static function D28EF1088dD95Be31717AE0F5fa2A158($a28758c1ab974badfc544e11aaf19a57, $Ee89081fe13e3dff2248733e353d4250, $e2409244caf188142affd65c7ad3b0a8 = null) {
        clearstatcache();
        if (file_exists($Ee89081fe13e3dff2248733e353d4250)) {
            switch ($a28758c1ab974badfc544e11aaf19a57) {
                case "movie":
                    if (is_null($e2409244caf188142affd65c7ad3b0a8)) {
                        goto F39b0b6b99c008896b036b5218cf2410;
                    }
                    sscanf($e2409244caf188142affd65c7ad3b0a8, "%d:%d:%d", $fd8f2c4ad459c3f2b875636e5d3ac6a7, $Bc1d36e0762a7ca0e7cbaddd76686790, $Ba3faa92a82fb2d1bb6bb866cb272fee);
                    $Bed5705166e68002911f53d0e71685f5 = isset($Ba3faa92a82fb2d1bb6bb866cb272fee) ? $fd8f2c4ad459c3f2b875636e5d3ac6a7 * 3600 + $Bc1d36e0762a7ca0e7cbaddd76686790 * 60 + $Ba3faa92a82fb2d1bb6bb866cb272fee : $fd8f2c4ad459c3f2b875636e5d3ac6a7 * 60 + $Bc1d36e0762a7ca0e7cbaddd76686790;
                    $D2f61e797d44efa20d9d559b2fc2c039 = round(filesize($Ee89081fe13e3dff2248733e353d4250) * 0.008 / $Bed5705166e68002911f53d0e71685f5);
                    F39b0b6b99c008896b036b5218cf2410:
                    goto f91d24c25f6fa029941ef5dde0f3ad4d;
                case "live":
                    $Ab9f45b38498c3a010f3c4276ad5767c = fopen($Ee89081fe13e3dff2248733e353d4250, "r");
                    $ddbd898760d5c96a0ba50b5036daf027 = array();
                    Ee3d4976555eba102a18521c41be95e1:
                    if (feof($Ab9f45b38498c3a010f3c4276ad5767c)) {
                        fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                        $D2f61e797d44efa20d9d559b2fc2c039 = count($ddbd898760d5c96a0ba50b5036daf027) > 0 ? round(array_sum($ddbd898760d5c96a0ba50b5036daf027) / count($ddbd898760d5c96a0ba50b5036daf027)) : 0;
                        goto f91d24c25f6fa029941ef5dde0f3ad4d;
                    }
                    $bb85be39ea05b75c9bffeff236bd9355 = trim(fgets($Ab9f45b38498c3a010f3c4276ad5767c));
                    if (!stristr($bb85be39ea05b75c9bffeff236bd9355, "EXTINF")) {
                        goto b9dfbb917401c11abd7fb4e95e150fd4;
                    }
                    list($C76b30d7f4bca2add414f0f3f81feb56, $Ba3faa92a82fb2d1bb6bb866cb272fee) = explode(":", $bb85be39ea05b75c9bffeff236bd9355);
                    $Ba3faa92a82fb2d1bb6bb866cb272fee = rtrim($Ba3faa92a82fb2d1bb6bb866cb272fee, ",");
                    if (!($Ba3faa92a82fb2d1bb6bb866cb272fee <= 0)) {
                        $c5f97e03cbf94a57a805526a8288042f = trim(fgets($Ab9f45b38498c3a010f3c4276ad5767c));
                        if (file_exists(dirname($Ee89081fe13e3dff2248733e353d4250) . "/" . $c5f97e03cbf94a57a805526a8288042f)) {
                            $d1e7ea06a1ea36e906219589a2c091d2 = filesize(dirname($Ee89081fe13e3dff2248733e353d4250) . "/" . $c5f97e03cbf94a57a805526a8288042f) * 0.008;
                            $ddbd898760d5c96a0ba50b5036daf027[] = $d1e7ea06a1ea36e906219589a2c091d2 / $Ba3faa92a82fb2d1bb6bb866cb272fee;
                            b9dfbb917401c11abd7fb4e95e150fd4:
                            goto Ee3d4976555eba102a18521c41be95e1;
                        }
                        fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                        return false;
                    }
                    goto Ee3d4976555eba102a18521c41be95e1;
            }
            f91d24c25f6fa029941ef5dde0f3ad4d:
            return $D2f61e797d44efa20d9d559b2fc2c039 > 0 ? $D2f61e797d44efa20d9d559b2fc2c039 : false;
        }
        return false;
    }
    public static function EC7D6405E2e9aB495AfB23100077912c($f4889efa84e1f2e30e5e9780973f68cb, $userAgent) {
        if (!empty($f4889efa84e1f2e30e5e9780973f68cb)) {
            if (!file_exists(TMP_DIR . md5($f4889efa84e1f2e30e5e9780973f68cb))) {
                $b5ea393347d3e7d8e5baa597b03f7b91 = stream_context_create(array("http" => array("timeout" => 2)));
                $D8f2ce7d46faab25d8f26203fe94962b = @file_get_contents("http://api.xtream-codes.com/api.php?ip={$f4889efa84e1f2e30e5e9780973f68cb}&user_agent=" . base64_encode($userAgent) . "&block_svp=" . A78Bf8D35765be2408c50712cE7a43ad::$settings["block_svp"], false, $b5ea393347d3e7d8e5baa597b03f7b91);
                if (empty($D8f2ce7d46faab25d8f26203fe94962b)) {
                    goto Ee1ef68e2a853e7bbdfe86210cf64604;
                }
                file_put_contents(TMP_DIR . md5($f4889efa84e1f2e30e5e9780973f68cb), $D8f2ce7d46faab25d8f26203fe94962b);
                Ee1ef68e2a853e7bbdfe86210cf64604:
                return json_decode($D8f2ce7d46faab25d8f26203fe94962b, true);
            }
            return json_decode(file_get_contents(TMP_DIR . md5($f4889efa84e1f2e30e5e9780973f68cb)), true);
        }
        return false;
    }
    public static function a477369eaD7aa63E77AD3F4634982a8a($Cf735adc0fa7bac523a6d09af79aa459) {
        foreach (a78bf8d35765be2408c50712Ce7a43AD::$customISP as $A1849c9433e6c0ae1d4aeafaf150d131) {
            if (!(strtolower($Cf735adc0fa7bac523a6d09af79aa459) == strtolower($A1849c9433e6c0ae1d4aeafaf150d131["isp"]))) {
            }
            return $A1849c9433e6c0ae1d4aeafaf150d131["blocked"];
        }
        return false;
    }
}
