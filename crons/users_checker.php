<?php
/*Rev:26.09.18r0*/

set_time_limit(0);
ini_set('memory_limit', -1);
if (!@$argc) {
    die(0);
}

function checkfpm($pid, $prname)
{
    return shell_exec('ps -p ' . $pid . ' | grep ' . $prname);
}




require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
cli_set_process_title('XtreamCodes[Users Parser]');
$tmpFile = TMP_DIR . md5(AFFb052cca396818D81004fF99dB49aa() . __FILE__);
bbd9e78AC32626e138e758e840305A7C($tmpFile);
$userAutoDisconnect = A78bf8D35765Be2408c50712Ce7A43Ad::$settings['user_auto_kick_hours'] * 3600;
$userConnection = array();
$Bcf87c9b8f60adb6d7364a2c5c48f8d8 = f0Bb8dBeaB7Fb0ECCcC0A73980Dbf47a('open');
$conList = explode('
', shell_exec('find /home/xtreamcodes/iptv_xtream_codes/tmp/ -maxdepth 1 -name "*.con" -print0 | xargs -0 grep "" -H'));
shell_exec('rm -f /home/xtreamcodes/iptv_xtream_codes/tmp/*.con');
foreach ($Bcf87c9b8f60adb6d7364a2c5c48f8d8 as $E38668abaa324e464e266fb7b7e784b1 => $E80aae019385d9c9558555fb07017028) {
    $totalUserConnection = count($E80aae019385d9c9558555fb07017028);
    foreach ($E80aae019385d9c9558555fb07017028 as $E7cca48cfca85fc445419a32d7d8f973 => $user_activity) {
        if (!($user_activity['max_connections'] != 0 && $user_activity['max_connections'] < $totalUserConnection)) {
            if ($user_activity['server_id'] == SERVER_ID) {
                if (!(!is_null($user_activity['exp_date']) && $user_activity['exp_date'] < time())) {
                    $duration = time() - $user_activity['date_start'];
                    if (!($userAutoDisconnect != 0 && $userAutoDisconnect <= $duration && $user_activity['is_restreamer'] == 0)) {
                        if (!($user_activity['container'] == 'hls')) {
                            if ($user_activity['container'] != 'rtmp') {
                                if (Cd89785224751CCa8017139daF9e891e::checkfpm($user_activity['pid'], 'php-fpm')) {
                                    $userConnection[$user_activity['activity_id']] = intval($user_activity['bitrate'] / 8 * 0.92);
                                } else {
                                    echo '[+] Closing Connection (Closed UnExp): ' . $user_activity['activity_id'] . '
';
                                    cD89785224751cca8017139DaF9E891E::a1EAe86369aa95A55B4BE332f1E22FE3($user_activity);
                                }
                            } else {
                                if ((time() - $user_activity['hls_last_read']) <= 60 || $user_activity['hls_end'] == 1) {
                                    echo '[+] Closing ENDED Con HLS: ' . $user_activity['activity_id'] . '
';
                                    cD89785224751CcA8017139daf9e891e::a1EaE86369aa95a55b4BE332f1e22FE3($user_activity);
                                    $totalUserConnection--;
                                }
                            }
                        }
                    } else {
                        echo '[+] Closing Connection[KICK TIME ONLINE]: ' . $user_activity['activity_id'] . '
';
                        cd89785224751cCA8017139daf9e891E::a1eAe86369aa95A55B4Be332f1e22fe3($user_activity);
                        $totalUserConnection--;
                    }
                } else {
                    echo '[+] Closing Connection: ' . $user_activity['activity_id'] . '
';
                    cD89785224751CCA8017139daf9e891E::A1EAE86369AA95A55B4be332F1e22Fe3($user_activity);
                    $totalUserConnection = 0;
                }
            }
        } else {
            echo '[+] Closing Connection caused max Connections overflow...
';
            Cd89785224751CCa8017139daF9e891E::A1Eae86369Aa95a55b4be332f1e22Fe3($user_activity);
            $totalUserConnection--;
        }
    }
}
foreach ($conList as $arrayLine) {
    if (empty($arrayLine)) {
        continue;
    }
    list($conFile, $conPid) = explode(':', basename($arrayLine));
    list($conID, $fextension) = explode('.', $conFile);
    if (isset($userConnection[$conID])) {
        $divergence = intval(($conPid - $userConnection[$conID]) / $userConnection[$conID] * 100);
        if (0 < $divergence) {
            $divergence = 0;
        }
        $f566700a43ee8e1f0412fe10fbdf03df->query('UPDATE `user_activity_now` SET `divergence` = \'%d\' WHERE `activity_id` = \'%d\'', abs($divergence), $conID);
    } else {
        @unlink(TMP_DIR . $conFile);
    }
}
@unlink($tmpFile);
