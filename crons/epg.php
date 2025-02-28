<?php
ini_set("memory_limit", -1);
class Epg {
    public $validEpg = false;
    public $epgSource = null;
    public $from_cache = false;
    public function __construct($result, $set = false) {
        $this->LoadEpg($result, $set);
    }
    public function getData() {
        $output = array();
        foreach ($this->epgSource->channel as $item) {
            $channel_id = trim((string) $item->attributes()->id);
            $display_name = !empty($item->{"display-name"}) ? trim((string) $item->{"display-name"}) : "";
            if (!array_key_exists($channel_id, $output)) {
                $output[$channel_id] = array();
                $output[$channel_id]["display_name"] = $display_name;
                $output[$channel_id]["langs"] = array();
            }
        }
        foreach ($this->epgSource->programme as $item) {
            $channel_id = trim((string) $item->attributes()->channel);
            if (array_key_exists($channel_id, $output)) {
                $title = $item->title;
                foreach ($title as $data) {
                    $lang = (string) $data->attributes()->lang;
                    if (!in_array($lang, $output[$channel_id]["langs"])) {
                        $output[$channel_id]["langs"][] = $lang;
                    }
                }
            }
        }
        return $output;
    }
    public function getProgrammes($epg_id, $streams) {
        global $ipTV_db;
        $list = array();
        foreach ($this->epgSource->programme as $item) {
            $channel_id = (string) $item->attributes()->channel;
            if (array_key_exists($channel_id, $streams)) {
                $desc_data = $data = "";
                $start = strtotime(strval($item->attributes()->start));
                $stop = strtotime(strval($item->attributes()->stop));
                if (!empty($item->title)) {
                    $titles = $item->title;
                    if (is_object($titles)) {
                        $epg_lang_check = false;
                        foreach ($titles as $title) {
                            if ($title->attributes()->lang == $streams[$channel_id]["epg_lang"]) {
                                $epg_lang_check = true;
                                $desc_data = base64_encode($title);
                                if (!$epg_lang_check) {
                                    $desc_data = base64_encode($title[0]);
                                }
                            }
                        }
                    } else {
                        $desc_data = base64_encode($titles);
                    }
                    if (!empty($item->desc)) {
                        $descs = $item->desc;
                        if (is_object($descs)) {
                            $epg_lang_check = false;
                            foreach ($descs as $desc) {
                                if ($desc->attributes()->lang == $streams[$channel_id]["epg_lang"]) {
                                    $epg_lang_check = true;
                                    $data = base64_encode($desc);
                                    if (!$epg_lang_check) {
                                        $data = base64_encode($descs[0]);
                                    }
                                }
                            }
                        } else {
                            $data = base64_encode($item->desc);
                        }
                    }
                    $channel_id = addslashes($channel_id);
                    $streams[$channel_id]["epg_lang"] = addslashes($streams[$channel_id]["epg_lang"]);
                    $date_start = date("Y-m-d H:i:s", $start);
                    $date_stop = date("Y-m-d H:i:s", $stop);
                    $list[] = "('" . $ipTV_db->escape($epg_id) . "', '" . $ipTV_db->escape($channel_id) . "', '" . $ipTV_db->escape($date_start) . "', '" . $ipTV_db->escape($date_stop) . "', '" . $ipTV_db->escape($streams[$channel_id]["epg_lang"]) . "', '" . $ipTV_db->escape($desc_data) . "', '" . $ipTV_db->escape($data) . "')";
                }
            }
        }
        return !empty($list) ? $list : false;
    }
    public function LoadEpg($result, $set) {
        $errors = pathinfo($result, PATHINFO_EXTENSION);
        if ($errors == "gz") {
            $content = gzdecode(file_get_contents($result));
            $epgSource = simplexml_load_string($content, "SimpleXMLElement", LIBXML_COMPACT | LIBXML_PARSEHUGE);
        } else {
            if ($errors == "xz") {
                $content = shell_exec("wget -qO- \"" . $result . "\" | unxz -c");
                $epgSource = simplexml_load_string($content, "SimpleXMLElement", LIBXML_COMPACT | LIBXML_PARSEHUGE);
            } else {
                $content = file_get_contents($result);
                $epgSource = simplexml_load_string($content, "SimpleXMLElement", LIBXML_COMPACT | LIBXML_PARSEHUGE);
            }
        }
        if ($epgSource !== false) {
            $this->epgSource = $epgSource;
            if (empty($this->epgSource->programme)) {
                ipTV_lib::saveLog_old("Not A Valid EPG Source Specified or EPG Crashed: " . $result);
            } else {
                $this->validEpg = true;
            }
        } else {
            ipTV_lib::saveLog_old("No XML Found At: " . $result);
        }
        $epgSource = $content = null;
    }
}


if (posix_getpwuid(posix_geteuid())['name'] == 'xc_vm') {
    if (@$argc) {
        shell_exec("kill -9 `ps -ef | grep 'XC_VM\\[EPG\\]' | grep -v grep | awk '{print \$2}'`;");
        require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
        cli_set_process_title("XC_VM[EPG]");
        $ipTV_db->query("SELECT * FROM `epg`");
        foreach ($ipTV_db->get_rows() as $row) {
            $dataEPG = new Epg($row["epg_file"]);
            if ($dataEPG->validEpg) {
                $ipTV_db->query("UPDATE `epg` SET `data` = ? WHERE `id` = ?", json_encode($dataEPG->getData()), $row["id"]);
                $dataEPG = null;
            }
        }
        $ipTV_db->query("SELECT DISTINCT(t1.`epg_id`),t2.* FROM `streams` t1 INNER JOIN `epg` t2 ON t2.id = t1.epg_id WHERE t1.`epg_id` IS NOT NULL");
        $epgs = $ipTV_db->get_rows();
        foreach ($epgs as $epg) {
            if ($epg["days_keep"] == 0) {
                $ipTV_db->query("DELETE FROM `epg_data` WHERE `epg_id` = ?", $epg["epg_id"]);
            }
            $dataEPG = new Epg($epg["epg_file"]);
            if ($dataEPG->validEpg) {
                $ipTV_db->query("SELECT t1.`channel_id`, t1.`epg_lang`, last_row.start FROM `streams` t1 LEFT JOIN ( SELECT channel_id, MAX(`start`) as start FROM epg_data WHERE epg_id = ? GROUP BY channel_id ) last_row ON last_row.channel_id = t1.channel_id WHERE `epg_id` = ?;", $epg["epg_id"], $epg["epg_id"]);
                $chanel_id = $ipTV_db->get_rows(true, "channel_id");
                $programmes = $dataEPG->getProgrammes($epg["epg_id"], $chanel_id);
                $id = 0;
                while ($id < count($programmes)) {
                    $ipTV_db->query("INSERT INTO `epg_data` (`epg_id`,`channel_id`,`start`,`end`,`lang`,`title`,`description`) VALUES " . $programmes[$id]);
                    $id++;
                }
                $ipTV_db->query("UPDATE `epg` SET `last_updated` = ? WHERE `id` = ?", time(), $epg["epg_id"]);
            }
            if (0 < $epg["days_keep"]) {
                $ipTV_db->query("DELETE FROM `epg_data` WHERE `epg_id` = ? AND `start` < ?", $epg["epg_id"], date("Y-m-d H:i:00", strtotime("-" . $epg["days_keep"] . " days")));
            }
        }
        $ipTV_db->query("DELETE n1 FROM `epg_data` n1, `epg_data` n2 WHERE n1.id < n2.id AND n1.epg_id = n2.epg_id AND n1.channel_id = n2.channel_id AND n1.start = n2.start AND n1.title = n2.title");
    } else {
        exit(0);
    }
} else {
    exit('Please run as XC_VM!' . "\n");
}
