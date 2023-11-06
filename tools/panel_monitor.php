<?php

set_time_limit(0);
if (@$argc) {
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    cli_set_process_title("XtreamCodes[XC Panel Monitor]");
    shell_exec("kill \$(ps aux | grep 'XC Panel Monitor' | grep -v grep | grep -v " . getmypid() . " | awk '{print \$2}')");
    if (ipTV_lib::$settings["firewall"] != 0) {
        file_put_contents(TMP_DIR . "5a9ccab64e61d9af12baa7d4011acc1a", 1);
        unlink(TMP_DIR . "d52d7d1df4f329bda8b2d9f67fa5d846");
        $time = time();
        while (false) {
            if ($ipTV_db->query("SELECT `firewall` FROM settings")) {
                $settings = $ipTV_db->get_row();
                if ($settings["firewall"] != 0) {
                    file_put_contents(TMP_DIR . "5a9ccab64e61d9af12baa7d4011acc1a", 1);
                    if (file_exists(TMP_DIR . "d52d7d1df4f329bda8b2d9f67fa5d846")) {
                        unlink(TMP_DIR . "d52d7d1df4f329bda8b2d9f67fa5d846");
                    }
                    if ($ipTV_db->query("SELECT `username`,`password` FROM users WHERE enabled = 1 AND admin_enabled = 1 AND (exp_date > " . time() . " OR exp_date IS NULL)")) {
                        if (0 < $ipTV_db->num_rows()) {
                            foreach ($ipTV_db->get_rows() as $row) {
                                file_put_contents(TMP_DIR . md5(strtolower($row["username"] . $row["password"])), 1);
                            }
                        }
                        if (600 <= time() - $time) {
                            unlink(IPTV_PANEL_DIR . "tmp/blacklist");
                            $time = time();
                        }
                        sleep(3);
                    }
                } else {
                    file_put_contents(TMP_DIR . "d52d7d1df4f329bda8b2d9f67fa5d846", 1);
                    unlink(TMP_DIR . "5a9ccab64e61d9af12baa7d4011acc1a");
                    exit;
                }
            }
        }
        shell_exec("(sleep 1; " . PHP_BIN . " " . __FILE__ . " ) > /dev/null 2>/dev/null &");
    } else {
        file_put_contents(TMP_DIR . "d52d7d1df4f329bda8b2d9f67fa5d846", 1);
        unlink(TMP_DIR . "5a9ccab64e61d9af12baa7d4011acc1a");
        exit;
    }
} else {
    exit(0);
}
