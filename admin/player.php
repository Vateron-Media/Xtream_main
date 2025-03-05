<?php

include "session.php";
include "functions.php";

function getuserip() {
    return $_SERVER['REMOTE_ADDR'];
}

if (isset(CoreUtilities::$request['id'])) {
    if (hasPermissions('adv', 'player')) {
        $rExpires = time() + 14400;
        $rTokenData = array('session_id' => session_id(), 'expires' => $rExpires, 'stream_id' => intval(CoreUtilities::$request['id']), 'ip' => getUserIP());
        $rLegacy = false;

        if (isset(CoreUtilities::$request['container'])) {
            $rTokenData['container'] = CoreUtilities::$request['container'];
            $rLegacy = ($rTokenData['container'] != 'mp4');
        }

        if (isset(CoreUtilities::$request['start'])) {
            $rTokenData['start'] = CoreUtilities::$request['start'];
        }

        if (isset(CoreUtilities::$request['duration'])) {
            $rTokenData['duration'] = CoreUtilities::$request['duration'];
        }

        $streamType = (in_array(CoreUtilities::$request['type'], array('live', 'timeshift')) ? 'hls' : preg_replace('/[^A-Za-z0-9 ]/', '', $rTokenData['container']));

        if (in_array(CoreUtilities::$request['type'], array('live', 'timeshift'))) {
            $ipTV_db_admin->query('SELECT `server_id`, `on_demand` FROM `streams_servers` WHERE ((`streams_servers`.`monitor_pid` > 0 AND `streams_servers`.`pid` > 0) OR (`streams_servers`.`on_demand` = 1)) AND `stream_id` = ?;', CoreUtilities::$request['id']);
        } else {
            $ipTV_db_admin->query('SELECT `server_id`, `on_demand` FROM `streams_servers` LEFT JOIN `streams` ON `streams`.`id` = `streams_servers`.`stream_id` WHERE (`streams`.`direct_source` = 0 AND `streams_servers`.`pid` > 0 AND `streams_servers`.`to_analyze` = 0 AND `streams_servers`.`stream_status` <> 1) AND `stream_id` = ?;', CoreUtilities::$request['id']);
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
            $rUIToken = encryptData(json_encode($rTokenData), CoreUtilities::$settings['live_streaming_pass'], OPENSSL_EXTRA);
            if ($rOnDemand) {
                $rStartURL = 'http://' . $rServers[$rServerID]['server_ip'] . ':' . $rServers[$rServerID]['http_broadcast_port'] . '/admin/live?password=' . CoreUtilities::$settings['live_streaming_pass'] . '&stream=' . intval(CoreUtilities::$request['id']) . '&extension=.m3u8&odstart=1';

                if (intval(@file_get_contents($rStartURL, false, stream_context_create(array('http' => array('timeout' => 20))))) == 0) {
                    exit();
                }
            }

            $rURL = $rProtocol . '://' . (($rServers[$rServerID]['domain_name'] ? explode(',', $rServers[$rServerID]['domain_name'])[0] : $rServers[$rServerID]['server_ip'])) . ':' . ((issecure() ? $rServers[$rServerID]['https_broadcast_port'] : $rServers[$rServerID]['http_broadcast_port'])) . '/admin/' . ((CoreUtilities::$request['type'] == 'live' ? 'live.php' : (CoreUtilities::$request['type'] == 'timeshift' ? 'timeshift' : 'vod'))) . '?uitoken=' . $rUIToken . ((CoreUtilities::$request['type'] == 'live' ? '&extension=.m3u8' : ''));

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