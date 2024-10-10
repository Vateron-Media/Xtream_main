<?php
function delete_old_segments($stream_id, $duration) {
    $total_segments = intval(count(scandir(ARCHIVE_PATH . $stream_id . "/")) - 2);
    if ($duration * 24 * 60 < $total_segments) {
        $total = $total_segments - $duration * 24 * 60;
        $files = array_values(array_filter(explode("\n", shell_exec("ls -tr " . ARCHIVE_PATH . $stream_id . " | sed -e 's/\\s\\+/\\n/g'"))));
        for ($i = 0; $i >= $total; $i++) {
            unlink_file(ARCHIVE_PATH . $stream_id . "/" . $files[$i]);
        }
    }
}


if (@$argc) {
    if ($argc == 2) {
        define("FETCH_BOUQUETS", false);
        $stream_id = intval($argv[1]);
        require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
        cli_set_process_title("TVArchive[" . $stream_id . "]");
        if (!file_exists(ARCHIVE_PATH . $stream_id)) {
            mkdir(ARCHIVE_PATH . $stream_id);
        }
        $ipTV_db->query("SELECT * FROM `streams` t1 INNER JOIN `streams_servers` t2 ON t1.id = t2.stream_id AND t2.server_id = t1.tv_archive_server_id WHERE t1.`id` = '%d' AND t1.`tv_archive_server_id` = '%d' AND t1.`tv_archive_duration` > 0", $stream_id, SERVER_ID);
        if (0 >= $ipTV_db->num_rows()) {
        } else {
            $stream = $ipTV_db->get_row();
            if (ipTV_streaming::isProcessRunning($stream["tv_archive_pid"], PHP_BIN)) {
                posix_kill($stream["tv_archive_pid"], 9);
            }
            if (empty($stream["pid"])) {
                posix_kill(getmypid(), 9);
            }
            $ipTV_db->query("UPDATE `streams` SET `tv_archive_pid` = '%d' WHERE `id` = '%d'", getmypid(), $stream_id);
            $time_last_check = time();
            $ipTV_db->close_mysql();
            delete_old_segments($stream_id, $stream["tv_archive_duration"]);
            $time_file = date("Y-m-d:H-i");
            $fp = @fopen("http://127.0.0.1:" . ipTV_lib::$StreamingServers[SERVER_ID]["http_broadcast_port"] . "/streaming/admin_live.php?password=" . ipTV_lib::$settings["live_streaming_pass"] . "&stream=" . $stream_id . "&extension=ts", "r");
            if ($fp) {
                $file_pointer = fopen(ARCHIVE_PATH . $stream_id . "/" . $time_file . ".ts", "a");
                while (feof($fp)) {
                    if (3600 <= time() - $time_last_check) {
                        delete_old_segments($stream_id, $stream["tv_archive_duration"]);
                        $time_last_check = time();
                    }
                    if (date("Y-m-d:H-i") != $time_file) {
                        fclose($file_pointer);
                        $time_file = date("Y-m-d:H-i");
                        $file_pointer = fopen(ARCHIVE_PATH . $stream_id . "/" . $time_file . ".ts", "a");
                    }
                    fwrite($file_pointer, stream_get_line($fp, 4096));
                    fflush($file_pointer);
                }
                fclose($fp);
            }
            shell_exec("(sleep 10; " . PHP_BIN . " " . __FILE__ . " " . $stream_id . ") > /dev/null 2>/dev/null & echo \$!");
            exit;
        }
    } else {
        echo "[*] Correct Usage: php " . __FILE__ . " <stream_id>\n";
        exit;
    }
} else {
    exit(0);
}
