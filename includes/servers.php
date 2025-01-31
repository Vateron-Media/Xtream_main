<?php

class ipTV_servers {
    /**
     * Checks if a process with a given PID is running on a specific server.
     *
     * @param int|string $rServerID The ID of the server where the process is running.
     * @param int|null $rPID The Process ID (PID) to check.
     * @param string $rEXE The executable name to verify against the PID.
     *
     * @return bool Returns true if the process is running, otherwise false.
     */
    public static function isPIDRunning($rServerID, $rPID, $rEXE) {
        if (!is_null($rPID) && is_numeric($rPID) && array_key_exists($rServerID, self::$rServers)) {
            if (!($rOutput = self::isPIDsRunning($rServerID, array($rPID), $rEXE))) {
                return false;
            }
            return $rOutput[$rServerID][$rPID];
        }
        return false;
    }
    
    /**
     * Checks if multiple processes (PIDs) are running on one or more servers.
     *
     * @param int|array $serverIDS The ID(s) of the server(s) where the processes should be checked.
     * @param array $PIDs An array of Process IDs (PIDs) to check.
     * @param string $eXE The executable name to verify against the PIDs.
     *
     * @return array Returns an associative array where the keys are server IDs 
     *               and values are arrays of running PIDs or `false` if the request failed.
     */
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
