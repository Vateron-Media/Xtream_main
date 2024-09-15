<?php

header('Access-Control-Allow-Origin: *');
set_time_limit(0);
require '../init.php';
$rSettings = unserialize(file_get_contents(CACHE_TMP_PATH . 'settings'));
$rServers = unserialize(file_get_contents(CACHE_TMP_PATH . 'servers'));

if (empty($rSettings['live_streaming_pass'])) {
    generate404();
}

$rVideoCodec = 'h264';

if (isset($_GET['token'])) {
    $rOffset = 0;
    $rTokenArray = explode('/', decryptData($_GET['token'], $rSettings['live_streaming_pass'], OPENSSL_EXTRA));

    if (count($rTokenArray) > 6) {
        if ($rTokenArray[0] == 'TS') {
            $rServerID = $rTokenArray[8];
        } else {
            $rServerID = $rTokenArray[6];
        }

        if ($rServerID == SERVER_ID) {
            if ($rTokenArray[0] == 'TS') {
                $rType = 'ARCHIVE';
                list(, $rUsername, $rPassword, $rUserIP, $rDuration, $rStartDate, $rSegmentData, $rUUID) = $rTokenArray;
                list($rStreamID, $rSegmentID, $rOffset) = explode('_', $rSegmentData);
                $rStreamID = intval($rStreamID);
                $rSegment = TV_ARCHIVE . $rStreamID . '/' . $rSegmentID;

                if (!file_exists($rSegment)) {
                    generate404();
                }
            } else {
                $rType = 'LIVE';
                list($rUsername, $rPassword) = $rTokenArray;
                $rUserIP = $rTokenArray[2];
                $rStreamID = intval($rTokenArray[3]);
                $rSegmentID = basename($rTokenArray[4]);
                $rUUID = $rTokenArray[5];
                $rVideoCodec = ($rTokenArray[7] ?: 'h264');
                $rOnDemand = ($rTokenArray[8] ?: 0);
                $rSegment = STREAMS_PATH . $rSegmentID;
                $rSegmentData = explode('_', $rSegmentID);

                if (!file_exists($rSegment) && $rSegmentData[0] != $rStreamID) {
                    generate404();
                }
            }

            if (!file_exists(CONS_TMP_PATH . $rUUID)) {
                generate404();
            }

            $rFilesize = filesize($rSegment);

            if ($rUserIP != getuserip() || $rSettings['restrict_same_ip']) {
                generate404();
            }

            header('Access-Control-Allow-Origin: *');
            header('Content-Type: video/mp2t');

            if ($rType == 'LIVE') {
                if ($rOnDemand) {
                    $rSettings['encrypt_hls'] = false;
                }

                if (file_exists(SIGNALS_PATH . $rUUID)) {
                    $rSignalData = json_decode(file_get_contents(SIGNALS_PATH . $rUUID), true);

                    if ($rSignalData['type'] == 'signal') {
                        if ($rSettings['encrypt_hls']) {
                            $rKey = file_get_contents(STREAMS_PATH . $rStreamID . '_.key');
                            $rIV = file_get_contents(STREAMS_PATH . $rStreamID . '_.iv');
                            $rData = ipTV_streaming::sendSignalFFMPEG($rSignalData, basename($rSegment), $rVideoCodec, true);
                            echo openssl_encrypt($rData, 'aes-128-cbc', $rKey, OPENSSL_RAW_DATA, $rIV);
                        } else {
                            ipTV_streaming::sendSignalFFMPEG($rSignalData, basename($rSegment), $rVideoCodec);
                        }

                        ipTV_lib::unlink_file(SIGNALS_PATH . $rUUID);

                        exit();
                    }
                }

                if ($rSettings['encrypt_hls']) {
                    $rSegmentData = explode('_', pathinfo($rSegmentID)['filename']);

                    if (!file_exists(STREAMS_PATH . $rStreamID . '_' . $rSegmentData[1] . '.ts')) {
                        generate404();
                    }

                    if (file_exists($rSegment . '.enc_write')) {
                        $rChecks = 0;

                        if (file_exists(STREAMS_PATH . $rStreamID . '_.dur')) {
                            $b73e9a5cd67eae9b = intval(file_get_contents(STREAMS_PATH . $rStreamID . '_.dur')) * 2;
                        } else {
                            $b73e9a5cd67eae9b = $rSettings['seg_time'] * 2;
                        }

                        while (file_exists($rSegment . '.enc_write') && !file_exists($rSegment . '.enc') && $rChecks <= $b73e9a5cd67eae9b * 10) {
                            usleep(100000);
                            $rChecks++;
                        }
                    } else {
                        ignore_user_abort(true);
                        touch($rSegment . '.enc_write');
                        $rKey = file_get_contents(STREAMS_PATH . $rStreamID . '_.key');
                        $rIV = file_get_contents(STREAMS_PATH . $rStreamID . '_.iv');
                        $rData = openssl_encrypt(file_get_contents($rSegment), 'aes-128-cbc', $rKey, OPENSSL_RAW_DATA, $rIV);
                        file_put_contents($rSegment . '.enc', $rData);
                        unset($rData);
                        ipTV_lib::unlink_file($rSegment . '.enc_write');
                        ignore_user_abort(false);
                    }

                    if (file_exists($rSegment . '.enc')) {
                        header('Content-Length: ' . filesize($rSegment . '.enc'));
                        readfile($rSegment . '.enc');
                    } else {
                        generate404();
                    }
                } else {
                    header('Content-Length: ' . $rFilesize);
                    readfile($rSegment);
                }
            } else {
                if (0 < $rOffset) {
                    header('Content-Length: ' . ($rFilesize - $rOffset));
                    $rFP = @fopen($rSegment, 'rb');

                    if ($rFP) {
                        fseek($rFP, $rOffset);

                        while (!feof($rFP)) {
                            echo stream_get_line($rFP, $rSettings['read_buffer_size']);
                        }
                        fclose($rFP);
                    }
                } else {
                    header('Content-Length: ' . $rFilesize);
                    readfile($rSegment);
                }
            }

            exit();
        }

        if ($rServers[$rServerID]['random_ip'] && 0 < count($rServers[$rServerID]['domains']['urls'])) {
            $rURL = $rServers[$rServerID]['domains']['protocol'] . '://' . $rServers[$rServerID]['domains']['urls'][array_rand($rServers[$rServerID]['domains']['urls'])] . ':' . $rServers[$rServerID]['domains']['port'];
        } else {
            $rURL = rtrim($rServers[$rServerID]['site_url'], '/');
        }

        header('Location: ' . $rURL . '/hls/' . $_GET['token']);

        exit();
    }
}

generate404();
function getuserip() {
    return $_SERVER['REMOTE_ADDR'];
}
