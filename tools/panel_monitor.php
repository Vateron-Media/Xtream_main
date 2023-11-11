<?php

set_time_limit(0);
if (@$argc) {
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    cli_set_process_title("XtreamCodes[XC Panel Monitor]");
    shell_exec('kill $(ps aux | grep \'XC Panel Monitor\' | grep -v grep | grep -v ' . getmypid() . ' | awk \'{print $2}\')');
    if (ipTV_lib::$settings['firewall'] == 0) {
        file_put_contents(TMP_DIR . 'firewall_off', 1);
        unlink(TMP_DIR . 'firewall_on');
        die;
    }
    file_put_contents(TMP_DIR . "firewall_on", 1);
    unlink(TMP_DIR . "firewall_off");
    $time = time();
    while (true) {
        if ($ipTV_db->query("SELECT `firewall` FROM settings")) {
            $settings = $ipTV_db->get_row();
            if ($settings["firewall"] != 0) {
                file_put_contents(TMP_DIR . "firewall_on", 1);
                if (file_exists(TMP_DIR . "firewall_off")) {
                    unlink(TMP_DIR . "firewall_off");
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
                file_put_contents(TMP_DIR . "firewall_off", 1);
                unlink(TMP_DIR . "firewall_on");
                exit;
            }
        }
    }
    shell_exec("(sleep 1; " . PHP_BIN . " " . __FILE__ . " ) > /dev/null 2>/dev/null &");
} else {
    exit(0);
}
