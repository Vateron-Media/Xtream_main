<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    include "/home/xtreamcodes/admin/functions.php";

    $result = $db->query("SELECT `server_id`, `pid` FROM `lines_live` WHERE `user_id` = 0;");
    if (($result) && ($result->num_rows > 0)) {
        while ($row = $result->fetch_assoc()) {
            sexec($rRow["server_id"], "kill -9 " . $rRow["pid"]);
        }
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}
