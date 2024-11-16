<?php

include "session.php";
include "functions.php";

function getuserip() {
    return $_SERVER['REMOTE_ADDR'];
}

if (isset(ipTV_lib::$request['id'])) {
    if (hasPermissions('adv', 'player')) {
        $rExpires = time() + 14400;
        $rTokenData = array('session_id' => session_id(), 'expires' => $rExpires, 'stream_id' => intval(ipTV_lib::$request['id']), 'ip' => getUserIP());
        $rLegacy = false;

        if (isset(ipTV_lib::$request['container'])) {
            $rTokenData['container'] = ipTV_lib::$request['container'];
            $rLegacy = ($rTokenData['container'] != 'mp4');
        }

        if (isset(ipTV_lib::$request['start'])) {
            $rTokenData['start'] = ipTV_lib::$request['start'];
        }

        if (isset(ipTV_lib::$request['duration'])) {
            $rTokenData['duration'] = ipTV_lib::$request['duration'];
        }

        $streamType = (in_array(ipTV_lib::$request['type'], array('live', 'timeshift')) ? 'hls' : preg_replace('/[^A-Za-z0-9 ]/', '', $rTokenData['container']));

        if (in_array(ipTV_lib::$request['type'], array('live', 'timeshift'))) {
            $ipTV_db_admin->query('SELECT `server_id`, `on_demand` FROM `streams_servers` WHERE ((`streams_servers`.`monitor_pid` > 0 AND `streams_servers`.`pid` > 0) OR (`streams_servers`.`on_demand` = 1)) AND `stream_id` = \'%d\';', ipTV_lib::$request['id']);
        } else {
            $ipTV_db_admin->query('SELECT `server_id`, `on_demand` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE (`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 0 AND `streams_servers`.`stream_status` <> 1) AND `stream_id` = \'%d\';', ipTV_lib::$request['id']);
        }

        $rOnDemand = false;
        $rServerID = null;

        foreach ($ipTV_db_admin->get_rows() as $rRow) {
            if ($rRow['server_id'] == SERVER_ID || !$rServerID) {
                $rServerID = $rRow['server_id'];
            }

            $rOnDemand = $rRow['on_demand'];
        }



        if ($rServerID) {
            $rUIToken = encryptData(json_encode($rTokenData), ipTV_lib::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            if ($rOnDemand) {
                $rStartURL = 'http://' . $rServers[$rServerID]['server_ip'] . ':' . $rServers[$rServerID]['http_broadcast_port'] . '/admin/live?password=' . ipTV_lib::$settings['live_streaming_pass'] . '&stream=' . intval(ipTV_lib::$request['id']) . '&extension=.m3u8&odstart=1';

                if (intval(@file_get_contents($rStartURL, false, stream_context_create(array('http' => array('timeout' => 20))))) == 0) {
                    exit();
                }
            }

            $rURL = $rProtocol . '://' . (($rServers[$rServerID]['domain_name'] ? explode(',', $rServers[$rServerID]['domain_name'])[0] : $rServers[$rServerID]['server_ip'])) . ':' . ((issecure() ? $rServers[$rServerID]['https_broadcast_port'] : $rServers[$rServerID]['http_broadcast_port'])) . '/admin/' . ((ipTV_lib::$request['type'] == 'live' ? 'live.php' : (ipTV_lib::$request['type'] == 'timeshift' ? 'timeshift' : 'vod'))) . '?uitoken=' . $rUIToken . ((ipTV_lib::$request['type'] == 'live' ? '&extension=.m3u8' : ''));

            ?>
            <html>

            <head>
                <script src="assets/js/vendor.min.js"></script>

                <?php if (!$rLegacy): ?>
                    <script src="assets/libs/jwplayer/jwplayer.js"></script>
                    <script src="assets/libs/jwplayer/jwplayer.core.controls.js"></script>
                <?php endif; ?>
                <style>
                    html {
                        overflow: hidden;
                    }
                </style>
            </head>

            <body>
                <?php if (!$rLegacy): ?>
                    <div id="now__playing__player"></div>
                <?php else: ?>
                    <video id="video" width="100%" height="100%" src="<?php echo $rURL; ?>" controls></video>
                <?php endif; ?>
                <script>
                    $(document).ready(function () {
                        <?php if (!$rLegacy): ?>
                            var rPlayer = jwplayer("now__playing__player");
                            rPlayer.setup({
                                "file": "<?php echo $rURL; ?>",
                                "type": "<?php echo $streamType; ?>",
                                "autostart": true,
                                "width": "100%",
                                "height": "100%"
                            });
                            rPlayer.play();
                        <?php else: ?>
                            $("video").trigger("play");
                        <?php endif; ?>
                    });
                </script>
            </body>

            </html>
            <?php
        } else {
            exit();
        }
    } else {
        header('Location: dashboard.php');
        exit();
    }
} else {
    exit();
}
?>