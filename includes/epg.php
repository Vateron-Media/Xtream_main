<?php

class Epg {
    public $validEpg = false;
    public $epgSource;
    public $from_cache = false;
    function __construct($result, $set = false) {
        $this->LoadEpg($result, $set);
    }
    public function getData() {
        $output = array();
        foreach ($this->epgSource->channel as $item) {
            $channel_id = trim((string) $item->attributes()->id);
            $display_name = !empty($item->{'display-name'}) ? trim((string) $item->{'display-name'}) : '';
            if (array_key_exists($channel_id, $output)) {
                continue;
            }
            $output[$channel_id] = array();
            $output[$channel_id]['display_name'] = $display_name;
            $output[$channel_id]['langs'] = array();
        }
        foreach ($this->epgSource->programme as $item) {
            $channel_id = trim((string) $item->attributes()->channel);
            if (!array_key_exists($channel_id, $output)) {
                continue;
            }
            $title = $item->title;
            foreach ($title as $data) {
                $lang = (string) $data->attributes()->lang;
                if (!in_array($lang, $output[$channel_id]['langs'])) {
                    $output[$channel_id]['langs'][] = $lang;
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
            if (!array_key_exists($channel_id, $streams)) {
                continue;
            }
            $desc_data = $data = '';
            $start = strtotime(strval($item->attributes()->start));
            $stop = strtotime(strval($item->attributes()->stop));
            if (empty($item->title)) {
                continue;
            }
            $title = $item->title;
            if (is_object($title)) {
                $epg_lang_check = false;
                foreach ($title as $data) {
                    if ($data->attributes()->lang == $streams[$channel_id]['epg_lang']) {
                        $epg_lang_check = true;
                        $desc_data = base64_encode($data);
                        break;
                    }
                }
                if (!$epg_lang_check) {
                    $desc_data = base64_encode($title[0]);
                }
            } else {
                $desc_data = base64_encode($title);
            }
            if (!empty($item->desc)) {
                $desc = $item->desc;
                if (is_object($desc)) {
                    $epg_lang_check = false;
                    foreach ($desc as $data) {
                        if ($data->attributes()->lang == $streams[$channel_id]['epg_lang']) {
                            $epg_lang_check = true;
                            $data = base64_encode($data);
                            break;
                        }
                    }
                    if (!$epg_lang_check) {
                        $data = base64_encode($desc[0]);
                    }
                } else {
                    $data = base64_encode($item->desc);
                }
            }
            $channel_id = addslashes($channel_id);
            $streams[$channel_id]['epg_lang'] = addslashes($streams[$channel_id]['epg_lang']);
            $date_start = date('Y-m-d H:i:s', $start);
            $date_stop = date('Y-m-d H:i:s', $stop);
            $list[] = '(\'' . $ipTV_db->escape($epg_id) . '\', \'' . $ipTV_db->escape($channel_id) . '\', \'' . $ipTV_db->escape($date_start) . '\', \'' . $ipTV_db->escape($date_stop) . '\', \'' . $ipTV_db->escape($streams[$channel_id]['epg_lang']) . '\', \'' . $ipTV_db->escape($desc_data) . '\', \'' . $ipTV_db->escape($data) . '\')';
        }
        return !empty($list) ? $list : false;
    }
    public function LoadEpg($result, $set) {
        $errors = pathinfo($result, PATHINFO_EXTENSION);
        if (($errors == 'gz')) {
            $content = file_get_contents($result);
            $epgSource = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
            $content = gzdecode(file_get_contents($result));
            $epgSource = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
        } else if ($errors == 'xz') {
            $content = shell_exec("wget -qO- \"{$result}\" | unxz -c");
            $epgSource = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
        }
        if ($epgSource !== false) {
            $this->epgSource = $epgSource;
            if (empty($this->epgSource->programme)) {
                ipTV_lib::saveLog_old('Not A Valid EPG Source Specified or EPG Crashed: ' . $result);
            } else {
                $this->validEpg = true;
            }
        } else {
            ipTV_lib::saveLog_old('No XML Found At: ' . $result);
        }
        $epgSource = $content = null;
    }
}
