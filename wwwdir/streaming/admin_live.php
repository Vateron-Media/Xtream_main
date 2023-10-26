<?php

header("Access-Control-Allow-Origin: *");
register_shutdown_function("shutdown");
set_time_limit(0);
require "../init.php";
$f4889efa84e1f2e30e5e9780973f68cb = $_SERVER["REMOTE_ADDR"];
if (in_array($f4889efa84e1f2e30e5e9780973f68cb, cD89785224751CCa8017139dAF9E891e::AB69e1103c96ee33fe21a6453D788925(true))) {
    if (!(empty(a78Bf8D35765BE2408C50712Ce7A43Ad::$request["stream"]) || empty(a78Bf8D35765be2408C50712Ce7a43aD::$request["extension"]) || empty(a78BF8d35765be2408c50712CE7a43Ad::$request["password"]) || a78Bf8d35765BE2408c50712CE7a43ad::$settings["live_streaming_pass"] != a78Bf8D35765BE2408c50712CE7A43Ad::$request["password"])) {
        $password = A78bf8D35765BE2408c50712ce7a43AD::$settings["live_streaming_pass"];
        $ba85d77d367dcebfcc2a3db9e83bb581 = intval(A78bF8D35765be2408C50712CE7a43AD::$request["stream"]);
        $F1350a5569e4b73d2f9cb26483f2a0c1 = A78bf8d35765Be2408C50712cE7a43Ad::$request["extension"];
        $f566700a43ee8e1f0412fe10fbdf03df->query("SELECT * \r\n                    FROM `streams` t1\r\n                    INNER JOIN `streams_sys` t2 ON t2.stream_id = t1.id AND t2.server_id = '%d'\r\n                    WHERE t1.`id` = '%d'", SERVER_ID, $ba85d77d367dcebfcc2a3db9e83bb581);
        if (!(a78BF8D35765Be2408c50712cE7A43aD::$settings["use_buffer"] == 0)) {
            goto F8e309efba7fcd89ed83e751af3d66f9;
        }
        header("X-Accel-Buffering: no");
        F8e309efba7fcd89ed83e751af3d66f9:
        if ($f566700a43ee8e1f0412fe10fbdf03df->getRowCount() > 0) {
            $ffb1e0970b62b01f46c2e57f2cded6c2 = $f566700a43ee8e1f0412fe10fbdf03df->F1eD191d78470660Edff4a007696Bc1f();
            $f566700a43ee8e1f0412fe10fbdf03df->CA531f7bDc43b966dEfB4abA3c8FAF22();
            $Bb37b848bec813a5c13ea0b018962c40 = STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.m3u8";
            if (cd89785224751CCa8017139daF9E891E::bCaa9B8A7b46eb36Cd507A218fa64474($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], $ba85d77d367dcebfcc2a3db9e83bb581)) {
                goto B02503281263d828b5d239e052fc80f3;
            }
            if ($ffb1e0970b62b01f46c2e57f2cded6c2["on_demand"] == 1) {
                if (CD89785224751ccA8017139Daf9E891E::CDa72Bc41975C364BC559dB25648a5B2($ffb1e0970b62b01f46c2e57f2cded6c2["monitor_pid"], $ba85d77d367dcebfcc2a3db9e83bb581)) {
                    goto d6253dd512db4e7042e7b9c2022a840b;
                }
                E3Cf480C172e8b47Fe10857C2A5aeb48::e79092731573697C16a932C339d0a101($ba85d77d367dcebfcc2a3db9e83bb581);
                d6253dd512db4e7042e7b9c2022a840b:
                f43dceeb8c029bd0d7f89cb75310b3a8:
                B02503281263d828b5d239e052fc80f3:
                switch ($F1350a5569e4b73d2f9cb26483f2a0c1) {
                    case "m3u8":
                        if (!CD89785224751cca8017139dAf9e891e::cFE2e5b7A9107cd2B2FDb629c199787D($Bb37b848bec813a5c13ea0b018962c40, $ffb1e0970b62b01f46c2e57f2cded6c2["pid"])) {
                            goto A10b076c87d7c0f4d0fb85e38cffe7f0;
                        }
                        if (empty(a78bF8d35765be2408C50712ce7a43Ad::$request["segment"])) {
                            if (!($F3803fa85b38b65447e6d438f8e9176a = CD89785224751cca8017139dAF9e891E::B18C6Bf534aE0B9b94354Db508d52a48($Bb37b848bec813a5c13ea0b018962c40, $password, $ba85d77d367dcebfcc2a3db9e83bb581))) {
                                goto E67a44c84875f97149e09039f2413c43;
                            }
                            header("Content-Type: application/vnd.apple.mpegurl");
                            header("Content-Length: " . strlen($F3803fa85b38b65447e6d438f8e9176a));
                            ob_end_flush();
                            echo $F3803fa85b38b65447e6d438f8e9176a;
                            E67a44c84875f97149e09039f2413c43:
                            goto c35a238198e8aec436bd6c2f25ba9101;
                        }
                        $fe9d0d199fc51f64065055d8bcade279 = STREAMS_PATH . str_replace(array("\\", "/"), '', urldecode(A78BF8d35765bE2408c50712Ce7A43aD::$request["segment"]));
                        if (!file_exists($fe9d0d199fc51f64065055d8bcade279)) {
                            goto b43aa5227253f9fcfe35177a67082f5d;
                        }
                        $e13ac89e162bcc9913e553b949f755b6 = filesize($fe9d0d199fc51f64065055d8bcade279);
                        header("Content-Length: " . $e13ac89e162bcc9913e553b949f755b6);
                        header("Content-Type: video/mp2t");
                        readfile($fe9d0d199fc51f64065055d8bcade279);
                        b43aa5227253f9fcfe35177a67082f5d:
                        c35a238198e8aec436bd6c2f25ba9101:
                        A10b076c87d7c0f4d0fb85e38cffe7f0:
                        goto F6cb25495593f4cb0ea164b6cbf41d63;
                    default:
                        header("Content-Type: video/mp2t");
                        $C325d28e238c3a646bd7b095aa1ffa85 = cd89785224751cCA8017139DAf9e891E::b8430212cC8301200A4976571dbA202c($Bb37b848bec813a5c13ea0b018962c40, a78bF8d35765bE2408C50712cE7A43aD::$settings["client_prebuffer"]);
                        if (!empty($C325d28e238c3a646bd7b095aa1ffa85)) {
                            if (is_array($C325d28e238c3a646bd7b095aa1ffa85)) {
                                foreach ($C325d28e238c3a646bd7b095aa1ffa85 as $fe9d0d199fc51f64065055d8bcade279) {
                                    readfile(STREAMS_PATH . $fe9d0d199fc51f64065055d8bcade279);
                                }
                                preg_match("/_(.*)\\./", array_pop($C325d28e238c3a646bd7b095aa1ffa85), $adb24597b0e7956b0f3baad7c260916d);
                                $E76c20c612d64210f5bcc0611992d2f7 = $adb24597b0e7956b0f3baad7c260916d[1];
                                goto d8ee862b891bef1e03832b1aed2694db;
                            }
                            $E76c20c612d64210f5bcc0611992d2f7 = $C325d28e238c3a646bd7b095aa1ffa85;
                            d8ee862b891bef1e03832b1aed2694db:
                            goto f4dce90a7951c4692e46b0303393f7a4;
                        }
                        if (!file_exists($Bb37b848bec813a5c13ea0b018962c40)) {
                            $E76c20c612d64210f5bcc0611992d2f7 = -1;
                            f4dce90a7951c4692e46b0303393f7a4:
                            $c45cc215a073632a9e20d474ea91f7e3 = 0;
                            $f065eccc0636f7fd92043c7118f7409b = A78Bf8D35765bE2408c50712ce7a43ad::$SegmentsSettings["seg_time"] * 2;
                            d9f748ce9f53402f322a8d21a1736957:
                            if (!true) {
                            }
                            $c5f97e03cbf94a57a805526a8288042f = sprintf("%d_%d.ts", $ba85d77d367dcebfcc2a3db9e83bb581, $E76c20c612d64210f5bcc0611992d2f7 + 1);
                            $Bf3da9b14ae368d39b642b3f83d656fc = sprintf("%d_%d.ts", $ba85d77d367dcebfcc2a3db9e83bb581, $E76c20c612d64210f5bcc0611992d2f7 + 2);
                            $a88c8d86d7956601164a5f156d5df985 = 0;
                            E2548032ad734253ca2cc2ebbf6269b0:
                            if (!(!file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f) && $a88c8d86d7956601164a5f156d5df985 <= $f065eccc0636f7fd92043c7118f7409b * 10)) {
                                if (file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f)) {
                                    if (!(empty($ffb1e0970b62b01f46c2e57f2cded6c2["pid"]) && file_exists(STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.pid"))) {
                                        goto B57ea8a587049a0c9418f46db33a5ade;
                                    }
                                    $ffb1e0970b62b01f46c2e57f2cded6c2["pid"] = intval(file_get_contents(STREAMS_PATH . $ba85d77d367dcebfcc2a3db9e83bb581 . "_.pid"));
                                    B57ea8a587049a0c9418f46db33a5ade:
                                    $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                    $Ab9f45b38498c3a010f3c4276ad5767c = fopen(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f, "r");
                                    D2d24958a6f2888a694ed67016b06229:
                                    if (!($c45cc215a073632a9e20d474ea91f7e3 <= $f065eccc0636f7fd92043c7118f7409b && !file_exists(STREAMS_PATH . $Bf3da9b14ae368d39b642b3f83d656fc))) {
                                        if (CD89785224751Cca8017139daF9e891e::ps_running($ffb1e0970b62b01f46c2e57f2cded6c2["pid"], FFMPEG_PATH) && $c45cc215a073632a9e20d474ea91f7e3 <= $f065eccc0636f7fd92043c7118f7409b && file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f) && is_resource($Ab9f45b38498c3a010f3c4276ad5767c)) {
                                            $F19b64ffad55876d290cb6f756a2dea5 = filesize(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f);
                                            $C73fe796a6baad7ca2e4251886562ef0 = $F19b64ffad55876d290cb6f756a2dea5 - ftell($Ab9f45b38498c3a010f3c4276ad5767c);
                                            if (!($C73fe796a6baad7ca2e4251886562ef0 > 0)) {
                                                goto a36379e2191b31a70b09c94bfbe2b81a;
                                            }
                                            echo stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, $C73fe796a6baad7ca2e4251886562ef0);
                                            a36379e2191b31a70b09c94bfbe2b81a:
                                            Af255dbe9a3b71ca62fe38873da87e5b:
                                            fclose($Ab9f45b38498c3a010f3c4276ad5767c);
                                            $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                            $E76c20c612d64210f5bcc0611992d2f7++;
                                            goto d9f748ce9f53402f322a8d21a1736957;
                                        }
                                        die;
                                    }
                                    $d76067cf9572f7a6691c85c12faf2a29 = stream_get_line($Ab9f45b38498c3a010f3c4276ad5767c, a78bf8d35765bE2408C50712CE7A43ad::$settings["read_buffer_size"]);
                                    if (!empty($d76067cf9572f7a6691c85c12faf2a29)) {
                                        echo $d76067cf9572f7a6691c85c12faf2a29;
                                        $c45cc215a073632a9e20d474ea91f7e3 = 0;
                                        goto D2d24958a6f2888a694ed67016b06229;
                                    }
                                    sleep(1);
                                    if (!(!is_resource($Ab9f45b38498c3a010f3c4276ad5767c) || !file_exists(STREAMS_PATH . $c5f97e03cbf94a57a805526a8288042f))) {
                                        ++$c45cc215a073632a9e20d474ea91f7e3;
                                        goto D2d24958a6f2888a694ed67016b06229;
                                    }
                                    die;
                                }
                                die;
                            }
                            usleep(100000);
                            ++$a88c8d86d7956601164a5f156d5df985;
                            goto E2548032ad734253ca2cc2ebbf6269b0;
                        }
                        die;
                }
                F6cb25495593f4cb0ea164b6cbf41d63:
                goto d88911fe7a8e1ed0deb21e6e9e0ce28b;
            }
            http_response_code(403);
            die;
        }
        http_response_code(403);
        d88911fe7a8e1ed0deb21e6e9e0ce28b:
        function shutdown() {
            fastcgi_finish_request();
        }
        // [PHPDeobfuscator] Implied script end
        return;
    }
    http_response_code(401);
    die;
}
http_response_code(401);
