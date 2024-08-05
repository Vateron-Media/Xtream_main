<?php
include "/home/xtreamcodes/admin/functions.php";

$rAdminSettings = getAdminSettings();
$rSettings = getSettings();
$rServers = getStreamingServers();

if (isset($rAdminSettings["cc_time"])) {
    if (time() - $rAdminSettings["cc_time"] < 3600) {
        exit;
    } else {
        $db->query("UPDATE `admin_settings` SET `value` = " . intval(time()) . " WHERE `type` = 'cc_time';");
    }
} else {
    $db->query("INSERT INTO `admin_settings`(`type`, `value`) VALUES('cc_time', " . intval(time()) . ");");
}

$result = $db->query("SELECT `id`, `stream_display_name`, `series_no`, `stream_source` FROM `streams` WHERE `type` = 3 AND `series_no` <> 0;");
if (($result) && ($result->num_rows > 0)) {
    while ($row = $result->fetch_assoc()) {
        $playlist = generateSeriesPlaylist(intval($row["series_no"]));
        if ($playlist["success"]) {
            $rSourceArray = json_decode($row["stream_source"], True);
            $rUpdate = False;
            foreach ($playlist["sources"] as $rSource) {
                if (!in_array($rSource, $rSourceArray)) {
                    $rUpdate = True;
                }
            }
            if ($rUpdate) {
                $db->query("UPDATE `streams` SET `stream_source` = '" . $db->real_escape_string(json_encode($playlist["sources"])) . "' WHERE `id` = " . intval($row["id"]) . ";");
                echo "Updated: " . $row["stream_display_name"] . "\n";
            }
        }
    }
}
