<?php

class ipTV_servers {
    public static function isPIDsRunning($serverIDS, $PIDs, $eXE) {
        if (!is_array($serverIDS)) {
            $serverIDS = array(intval($serverIDS));
        }
        $PIDs = array_map('intval', $PIDs);
        $output = array();
        foreach ($serverIDS as $serverID) {
            if (array_key_exists($serverID, ipTV_lib::$Servers)) {
                $esponse = self::serverRequest($serverID, ipTV_lib::$Servers[$serverID]['api_url_ip'] . '&action=pidsAreRunning', array('program' => $eXE, 'pids' => $PIDs));
                if ($esponse) {
                    $output[$serverID] = array_map('trim', json_decode($esponse, true));
                } else {
                    $output[$serverID] = false;
                }
            }
        }
        return $output;
    }
    static function PidsChannels($createdChannelLocation, $pid, $ffmpeg_path) {
        if (is_null($pid) || !is_numeric($pid) || !array_key_exists($createdChannelLocation, ipTV_lib::$Servers)) {
            return false;
        }
        if ($output = self::isPIDsRunning($createdChannelLocation, array($pid), $ffmpeg_path)) {
            return $output[$createdChannelLocation][$pid];
        }
        return false;
    }
    static function getPidFromProcessName($serverIDS, $ffmpeg_path) {
        $command = 'ps ax | grep \'' . basename($ffmpeg_path) . '\' | awk \'{print $1}\'';
        return self::RunCommandServer($serverIDS, $command);
    }
    public static function RunCommandServer($serverIDS, $cmd, $type = 'array') {
        $output = array();
        if (!is_array($serverIDS)) {
            $serverIDS = array(intval($serverIDS));
        }
        if (empty($cmd)) {
            foreach ($serverIDS as $server_id) {
                $output[$server_id] = '';
            }
            return $output;
        }
        foreach ($serverIDS as $server_id) {
            if (!($server_id == SERVER_ID)) {
                if (!array_key_exists($server_id, ipTV_lib::$Servers)) {
                    continue;
                }
                $esponse = self::serverRequest($server_id, ipTV_lib::$Servers[$server_id]['api_url_ip'] . '&action=runCMD', array('command' => $cmd));
                if ($esponse) {
                    $esult = json_decode($esponse, true);
                    $output[$server_id] = $type == 'array' ? $esult : implode('', $esult);
                } else {
                    $output[$server_id] = false;
                }
            } else {
                exec($cmd, $outputCMD);
                $output[$server_id] = $type == 'array' ? $outputCMD : implode('', $outputCMD);
            }
        }
        return $output;
    }
    public static function serverRequest($serverID, $rURL, $postData = array()) {
        if (ipTV_lib::$Servers[$serverID]['server_online']) {
            $output = false;
            $i = 1;
            while ($i <= 2) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $rURL);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0) Gecko/20100101 Firefox/9.0');
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                if (!empty($postData)) {
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                }
                $output = curl_exec($ch);
                $esponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_errno($ch);
                @curl_close($ch);
                if ($error != 0 || $esponseCode != 200) {
                    $i++;
                    ipTV_lib::saveLog_old("[MAIN->LB] Response from Server ID {$serverID} was Invalid ( ERROR: {$error} | Response Code: {$esponseCode} | Try: {$i} )");
                    break;
                }
            }
            return $output;
        }
        return false;
    }
}
