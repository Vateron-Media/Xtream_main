<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if ($argc) {
        set_time_limit(0);
        require str_replace('\\', '/', dirname($argv[0])) . '/../../wwwdir/init.php';
        $rMethod = (1 < count($argv) ? $argv[1] : null);
        loadcli();
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
function loadcli() {
    global $ipTV_db;
    global $rMethod;
    global $rID;
    switch ($rMethod) {
        case 'bouquets':
            $rStreamIDs = array(array(), array());
            $ipTV_db->query('SELECT `id` FROM `streams`;');
            if ($ipTV_db->num_rows() > 0) {
                foreach ($ipTV_db->get_rows() as $rRow) {
                    $rStreamIDs[0][] = intval($rRow['id']);
                }
            }
            $ipTV_db->query('SELECT `id` FROM `series`;');
            if ($ipTV_db->num_rows() > 0) {
                foreach ($ipTV_db->get_rows() as $rRow) {
                    $rStreamIDs[1][] = intval($rRow['id']);
                }
            }
            $ipTV_db->query('SELECT * FROM `bouquets` ORDER BY `bouquet_order` ASC;');
            if ($ipTV_db->num_rows() > 0) {
                foreach ($ipTV_db->get_rows() as $rBouquet) {
                    $rUpdate = array(array(), array(), array(), array());
                    foreach (json_decode($rBouquet['bouquet_channels'], true) as $rID) {
                        if (0 < intval($rID) && in_array(intval($rID), $rStreamIDs[0])) {
                            $rUpdate[0][] = intval($rID);
                        }
                    }
                    foreach (json_decode($rBouquet['bouquet_movies'], true) as $rID) {
                        if (0 < intval($rID) && in_array(intval($rID), $rStreamIDs[0])) {
                            $rUpdate[1][] = intval($rID);
                        }
                    }
                    foreach (json_decode($rBouquet['bouquet_radios'], true) as $rID) {
                        if (0 < intval($rID) && in_array(intval($rID), $rStreamIDs[0])) {
                            $rUpdate[2][] = intval($rID);
                        }
                    }
                    foreach (json_decode($rBouquet['bouquet_series'], true) as $rID) {
                        if (0 < intval($rID) && in_array(intval($rID), $rStreamIDs[1])) {
                            $rUpdate[3][] = intval($rID);
                        }
                    }
                    $ipTV_db->query("UPDATE `bouquets` SET `bouquet_channels` = '" . json_encode($rUpdate[0]) . "', `bouquet_movies` = '" . json_encode($rUpdate[1]) . "', `bouquet_radios` = '" . json_encode($rUpdate[2]) . "', `bouquet_series` = '" . json_encode($rUpdate[3]) . "' WHERE `id` = " . intval($rBouquet["id"]) . ";");
                }
            }
            break;
    }
}
function shutdown() {
    global $ipTV_db;
    if (is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    }
}
