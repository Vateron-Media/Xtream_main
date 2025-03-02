<?php
session_start();
session_write_close();

if (isset($_SESSION['hash'])) {
    set_time_limit(2);
    ini_set('default_socket_timeout', 2);
    define('MAIN_DIR', '/home/xc_vm/');
    define('WWW_PATH', MAIN_DIR . 'wwwdir/');
    define('IMAGES_PATH', WWW_PATH . 'images/');
    define('TMP_PATH', MAIN_DIR . 'tmp/');
    define('CACHE_TMP_PATH', TMP_PATH . 'cache/');
    $rServers = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'servers'));
    $rURL = $_GET['url'];
    $rMaxW = 0;
    $rMaxH = 0;

    if (isset($_GET['maxw'])) {
        $rMaxW = intval($_GET['maxw']);
    }

    if (isset($_GET['maxh'])) {
        $rMaxH = intval($_GET['maxh']);
    }

    if (isset($_GET['max'])) {
        $rMaxW = intval($_GET['max']);
        $rMaxH = intval($_GET['max']);
    }

    if (substr($rURL, 0, 2) == 's:') {
        $rSplit = explode(':', $rURL, 3);
        $rServerID = intval($rSplit[1]);
        $rDomain = (empty($rServers[$rServerID]['domain_name']) ? $rServers[$rServerID]['server_ip'] : explode(',', $rServers[$rServerID]['domain_name'])[0]);
        $rServerURL = $rServers[$rServerID]['server_protocol'] . '://' . $rDomain . ':' . $rServers[$rServerID]['request_port'] . '/';
        $rURL = $rServerURL . 'images/' . basename($rURL);
    }

    if ($rURL && 0 < $rMaxW && 0 < $rMaxH) {
        $rImagePath = IMAGES_PATH . 'admin/' . md5($rURL) . '_' . $rMaxW . '_' . $rMaxH . '.png';

        if (!file_exists($rImagePath) && filesize($rImagePath) == 0) {
            if (isabsoluteurl($rURL)) {
                $rActURL = $rURL;
            } else {
                $rActURL = IMAGES_PATH . basename($rURL);
            }
            $rImageInfo = getimagesize($rActURL);
            $rImageSize = getImageSizeKeepAspectRatio($rImageInfo[0], $rImageInfo[1], $rMaxW, $rMaxH);

            if ($rImageSize['width'] && $rImageSize['height']) {
                if ($rImageInfo['mime'] == 'image/png') {
                    $rImage = imagecreatefrompng($rActURL);
                } else {
                    if ($rImageInfo['mime'] == 'image/jpeg') {
                        $rImage = imagecreatefromjpeg($rActURL);
                    } else {
                        $rImage = null;
                    }
                }

                if ($rImage) {
                    $rImageP = imagecreatetruecolor($rImageSize['width'], $rImageSize['height']);
                    imagealphablending($rImageP, false);
                    imagesavealpha($rImageP, true);
                    imagecopyresampled($rImageP, $rImage, 0, 0, 0, 0, $rImageSize['width'], $rImageSize['height'], $rImageInfo[0], $rImageInfo[1]);
                    imagepng($rImageP, $rImagePath);
                }
            }
        }

        if (file_exists($rImagePath)) {
            header('Content-Type: image/png');
            echo file_get_contents($rImagePath);

            exit();
        }
    }

    header('Content-Type: image/png');
    $rImage = imagecreatetruecolor(1, 1);
    imagesavealpha($rImage, true);
    imagefill($rImage, 0, 0, imagecolorallocatealpha($rImage, 0, 0, 0, 127));
    imagepng($rImage);
} else {
    exit();
}

function getImageSizeKeepAspectRatio($origWidth, $origHeight, $maxWidth, $maxHeight) {
    if ($maxWidth == 0) {
        $maxWidth = $origWidth;
    }

    if ($maxHeight == 0) {
        $maxHeight = $origHeight;
    }

    $widthRatio = $maxWidth / (($origWidth ?: 1));
    $heightRatio = $maxHeight / (($origHeight ?: 1));
    $ratio = min($widthRatio, $heightRatio);

    if ($ratio < 1) {
        $newWidth = (int) $origWidth * $ratio;
        $newHeight = (int) $origHeight * $ratio;
    } else {
        $newHeight = $origHeight;
        $newWidth = $origWidth;
    }

    return array('height' => round($newHeight, 0), 'width' => round($newWidth, 0));
}

function isabsoluteurl($rURL) {
    $rPattern = "/^(?:ftp|https?|feed)?:?\\/\\/(?:(?:(?:[\\w\\.\\-\\+!\$&'\\(\\)*\\+,;=]|%[0-9a-f]{2})+:)*" . "\r\n" . "    (?:[\\w\\.\\-\\+%!\$&'\\(\\)*\\+,;=]|%[0-9a-f]{2})+@)?(?:" . "\r\n" . '    (?:[a-z0-9\\-\\.]|%[0-9a-f]{2})+|(?:\\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\\]))(?::[0-9]+)?(?:[\\/|\\?]' . "\r\n" . "    (?:[\\w#!:\\.\\?\\+\\|=&@\$'~*,;\\/\\(\\)\\[\\]\\-]|%[0-9a-f]{2})*)?\$/xi";

    return (bool) preg_match($rPattern, $rURL);
}
