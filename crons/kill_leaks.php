<?php
if (posix_getpwuid(posix_geteuid())['name'] == 'xtreamcodes') {
    include "/home/xtreamcodes/admin/functions.php";

    $ipTV_db_admin->query("SELECT `server_id`, `pid` FROM `lines_live` WHERE `user_id` = 0;");
    if ($ipTV_db_admin->num_rows() > 0) {
        foreach ($ipTV_db_admin->get_rows() as $row) {
            sexec($row["server_id"], "kill -9 " . $row["pid"]);
        }
    }
} else {
    exit('Please run as XtreamCodes!' . "\n");
}
