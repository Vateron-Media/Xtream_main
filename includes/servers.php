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
        if (!is_null($rPID) && is_numeric($rPID) && array_key_exists($rServerID, CoreUtilities::$Servers)) {
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
            if (array_key_exists($serverID, CoreUtilities::$Servers)) {
                $esponse = self::serverRequest($serverID, CoreUtilities::$Servers[$serverID]['api_url_ip'] . '&action=pidsAreRunning', array('program' => $eXE, 'pids' => $PIDs));
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
        if (CoreUtilities::$Servers[$serverID]['server_online']) {
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
                    CoreUtilities::saveLog_old("[MAIN->LB] Response from Server ID {$serverID} was Invalid ( ERROR: {$error} | Response Code: {$esponseCode} | Try: {$i} )");
                    break;
                }
            }
            return $output;
        }
        return false;
    }

    /**
     * Calculates the total CPU usage across all processes.
     *
     * This function retrieves the CPU usage of all running processes and sums them up.
     * It then normalizes the total CPU usage based on the number of CPU cores.
     *
     * @return float The total CPU usage percentage across all CPU cores.
     */
    function getTotalCPU() {
        $rTotalLoad = 0; // Initialize variable to store the total CPU load.

        // Execute `ps -Ao pid,pcpu` to retrieve the list of all processes and their CPU usage.
        exec('ps -Ao pid,pcpu', $processes);

        // Iterate through each process.
        foreach ($processes as $process) {
            // Remove extra spaces and split the string into columns.
            $cols = explode(' ', preg_replace('!\\s+!', ' ', trim($process)));

            // Sum the CPU usage, converting the value to a float.
            $rTotalLoad += floatval($cols[1]);
        }

        // Get the number of CPU cores using `grep -P '^processor' /proc/cpuinfo | wc -l`.
        $cpuCores = intval(shell_exec("grep -P '^processor' /proc/cpuinfo | wc -l"));

        // Normalize the CPU load by dividing it by the number of CPU cores.
        return ($cpuCores > 0) ? $rTotalLoad / $cpuCores : 0;
    }

    /**
     * Calculates the total size of tmpfs (temporary filesystem) in use.
     *
     * This function retrieves the disk usage statistics for tmpfs filesystems
     * and sums up the used space in kilobytes.
     *
     * @return int Total tmpfs usage in kilobytes.
     */
    function getTotalTmpfs() {
        $rTotal = 0; // Initialize variable to store the total tmpfs usage.

        // Execute `df | grep tmpfs` to list tmpfs filesystems and their usage.
        exec('df | grep tmpfs', $rOutput);

        foreach ($rOutput as $rLine) {
            // Normalize spacing and split the output into an array.
            $rSplit = explode(' ', preg_replace('!\s+!', ' ', trim($rLine)));

            // Ensure we are processing a valid tmpfs entry.
            if ($rSplit[0] === 'tmpfs') {
                $rTotal += intval($rSplit[2]); // Add used space (in KB).
            }
        }

        return $rTotal; // Return total tmpfs usage.
    }

    /**
     * Retrieves a list of active network interfaces.
     *
     * This function lists all available network interfaces except the loopback (`lo`)
     * and bonded interfaces (`bond*`).
     *
     * @return array List of network interface names.
     */
    function getNetworkInterfaces() {
        $rReturn = array(); // Initialize an array to store network interfaces.

        // Execute `ls /sys/class/net/` to list network interfaces.
        exec('ls /sys/class/net/', $rOutput, $rReturnVar);

        foreach ($rOutput as $rInterface) {
            // Trim unnecessary characters from the interface name.
            $rInterface = trim(rtrim($rInterface, ':'));

            // Exclude the loopback interface (`lo`) and bonded interfaces (`bond*`).
            if ($rInterface !== 'lo' && substr($rInterface, 0, 4) != 'bond') {
                $rReturn[] = $rInterface;
            }
        }

        return $rReturn; // Return the list of network interfaces.
    }

    /**
     * Retrieves network statistics from a cached log file.
     *
     * This function reads the network statistics from a cached JSON file and
     * returns relevant data for all network interfaces or a specific interface.
     *
     * @param string|null $Interface The name of the network interface to filter (optional).
     * @return array Network statistics for the requested interface(s).
     */
    function getNetwork($Interface = null) {
        $Return = array(); // Initialize an array to store network data.

        // Check if the network log file exists.
        if (file_exists(LOGS_TMP_PATH . 'network')) {
            // Read and decode network statistics from the JSON file.
            $Network = json_decode(file_get_contents(LOGS_TMP_PATH . 'network'), true);

            foreach ($Network as $Key => $Line) {
                // Filter results based on the provided interface name.
                // Exclude loopback (lo) and bonded (bond*) interfaces unless explicitly requested.
                if (!($Interface && $Key != $Interface) && !($Key == 'lo' || !$Interface && substr($Key, 0, 4) == 'bond')) {
                    $Return[$Key] = $Line;
                }
            }
        }

        return $Return; // Return network statistics.
    }

    /**
     * Retrieves system I/O statistics using the `iostat` command.
     *
     * This function executes `iostat` in JSON format to obtain disk and CPU I/O usage
     * statistics and extracts relevant information.
     *
     * @return array Parsed I/O statistics, or an empty array if retrieval fails.
     */
    function getIO() {
        // Execute `iostat -o JSON -m` to get I/O statistics in JSON format.
        exec('iostat -o JSON -m', $rOutput, $rReturnVar);

        // Combine output lines into a single JSON string.
        $rOutput = implode('', $rOutput);
        $rJSON = json_decode($rOutput, true);

        // Validate and return extracted statistics, or an empty array if unavailable.
        if (isset($rJSON['sysstat'])) {
            return $rJSON['sysstat']['hosts'][0]['statistics'][0];
        }

        return array();
    }

    /**
     * Retrieves information about the system's NVIDIA GPUs using `nvidia-smi`.
     *
     * @return array An array containing GPU details such as driver version, CUDA version, and utilization.
     */
    function getGPUInfo() {
        exec('nvidia-smi -x -q', $rOutput, $rReturnVar);
        $rOutput = implode('', $rOutput);

        // Check if the output contains valid XML data
        if (stripos($rOutput, '<?xml') === false) {
            return array();
        }

        // Convert XML output to JSON and then decode it into an associative array
        $rJSON = json_decode(json_encode(simplexml_load_string($rOutput)), true);

        if (!isset($rJSON['driver_version'])) {
            return array();
        }

        // Initialize the result array with general GPU information
        $rGPU = array(
            'attached_gpus'  => $rJSON['attached_gpus'],
            'driver_version' => $rJSON['driver_version'],
            'cuda_version'   => $rJSON['cuda_version'],
            'gpus'           => array(),
        );

        // If there's only one GPU, convert it into an array
        if (isset($rJSON['gpu']['board_id'])) {
            $rJSON['gpu'] = array($rJSON['gpu']);
        }

        // Iterate through each GPU and extract relevant information
        foreach ($rJSON['gpu'] as $rInstance) {
            $rArray = array(
                'name'           => $rInstance['product_name'] ?? 'Unknown',
                'power_readings' => $rInstance['power_readings'] ?? array(),
                'utilisation'    => $rInstance['utilization'] ?? array(),
                'memory_usage'   => $rInstance['fb_memory_usage'] ?? array(),
                'fan_speed'      => $rInstance['fan_speed'] ?? 'N/A',
                'temperature'    => $rInstance['temperature'] ?? array(),
                'clocks'         => $rInstance['clocks'] ?? array(),
                'uuid'           => $rInstance['uuid'] ?? '',
                'id'             => isset($rInstance['pci']['pci_device']) ? intval($rInstance['pci']['pci_device']) : 0,
                'processes'      => array(),
            );

            // Extract running processes on the GPU
            if (!empty($rInstance['processes']['process_info'])) {
                $processes = is_array($rInstance['processes']['process_info']) ? $rInstance['processes']['process_info'] : array($rInstance['processes']['process_info']);
                foreach ($processes as $rProcess) {
                    $rArray['processes'][] = array(
                        'pid'    => isset($rProcess['pid']) ? intval($rProcess['pid']) : 0,
                        'memory' => $rProcess['used_memory'] ?? 'N/A',
                    );
                }
            }

            $rGPU['gpus'][] = $rArray;
        }

        return $rGPU;
    }

    /**
     * Retrieves a list of available video devices using `v4l2-ctl`.
     *
     * @return array An array containing video device names and corresponding device paths.
     */
    function getVideoDevices() {
        $rReturn = array();
        $rID = 0;

        try {
            // Get the list of video devices
            $rDevices = array_values(array_filter(explode("\n", shell_exec('v4l2-ctl --list-devices'))));

            if (!is_array($rDevices)) {
                return $rReturn;
            }

            // Process the device list
            foreach ($rDevices as $rKey => $rValue) {
                if ($rKey % 2 == 0 && isset($rDevices[$rKey + 1])) {
                    $rReturn[$rID]['name'] = trim($rValue);
                    list(, $rReturn[$rID]['video_device']) = explode('/dev/', trim($rDevices[$rKey + 1]));
                    $rID++;
                }
            }
        } catch (Exception $e) {
            return array();
        }

        return $rReturn;
    }

    /**
     * Retrieves a list of available audio devices using `arecord`.
     *
     * @return array An array of detected audio devices.
     */
    function getAudioDevices() {
        try {
            // Use `arecord -L` to list all available audio devices
            return array_filter(explode("\n", shell_exec('arecord -L | grep "hw:CARD="')));
        } catch (Exception $e) {
            return array();
        }
    }

    public static function getStats() {
        $rJSON = array();
        $rJSON['cpu'] = round(self::getTotalCPU(), 2);
        $rJSON['cpu_cores'] = intval(shell_exec('cat /proc/cpuinfo | grep "^processor" | wc -l'));
        $rJSON['cpu_avg'] = round((sys_getloadavg()[0] * 100) / (($rJSON['cpu_cores'] ?: 1)), 2);
        $rJSON['cpu_name'] = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | uniq | awk -F: '{print \$2}'"));
        if ($rJSON['cpu_avg'] > 100) {
            $rJSON['cpu_avg'] = 100;
        }
        $rFree = explode("\n", trim(shell_exec('free')));
        $rMemory = preg_split('/[\\s]+/', $rFree[1]);
        $rTotalUsed = intval($rMemory[2]);
        $rTotalRAM = intval($rMemory[1]);
        $rJSON['total_mem'] = $rTotalRAM;
        $rJSON['total_mem_free'] = $rTotalRAM - $rTotalUsed;
        $rJSON['total_mem_used'] = $rTotalUsed + self::getTotalTmpfs();
        $rJSON['total_mem_used_percent'] = round($rJSON['total_mem_used'] / $rJSON['total_mem'] * 100, 2);
        $rJSON['total_disk_space'] = disk_total_space(IPTV_ROOT_PATH);
        $rJSON['free_disk_space'] = disk_free_space(IPTV_ROOT_PATH);
        $rJSON['kernel'] = trim(shell_exec('uname -r'));
        $rJSON['uptime'] = CoreUtilities::getUptime();
        $rJSON['total_running_streams'] = (int) trim(shell_exec('ps ax | grep -v grep | grep -c ffmpeg'));
        $rJSON['bytes_sent'] = 0;
        $rJSON['bytes_sent_total'] = 0;
        $rJSON['bytes_received'] = 0;
        $rJSON['bytes_received_total'] = 0;
        $rJSON['network_speed'] = 0;
        $rJSON['interfaces'] = self::getNetworkInterfaces();
        $rJSON['network_speed'] = 0;
        if ($rJSON['cpu'] > 100) {
            $rJSON['cpu'] = 100;
        }
        if ($rJSON['total_mem'] < $rJSON['total_mem_used']) {
            $rJSON['total_mem_used'] = $rJSON['total_mem'];
        }
        if ($rJSON['total_mem_used_percent'] > 100) {
            $rJSON['total_mem_used_percent'] = 100;
        }

        $rJSON['network_info'] = self::getNetwork((CoreUtilities::$Servers[SERVER_ID]['network_interface'] == 'auto' ? null : CoreUtilities::$Servers[SERVER_ID]['network_interface']));
        foreach ($rJSON['network_info'] as $rInterface => $rData) {
            if (file_exists('/sys/class/net/' . $rInterface . '/speed')) {
                $NetSpeed = intval(file_get_contents('/sys/class/net/' . $rInterface . '/speed'));
                if (0 < $NetSpeed && $rJSON['network_speed'] == 0) {
                    $rJSON['network_speed'] = $NetSpeed;
                }
            }
            $rJSON['bytes_sent_total'] = (intval(trim(file_get_contents('/sys/class/net/' . $rInterface . '/statistics/tx_bytes'))) ?: 0);
            $rJSON['bytes_received_total'] = (intval(trim(file_get_contents('/sys/class/net/' . $rInterface . '/statistics/tx_bytes'))) ?: 0);
            $rJSON['bytes_sent'] += $rData['bytes_sent'];
            $rJSON['bytes_received'] += $rData['bytes_received'];
        }
        $rJSON['audio_devices'] = array();
        $rJSON['video_devices'] = $rJSON['audio_devices'];
        $rJSON['gpu_info'] = $rJSON['video_devices'];
        $rJSON['iostat_info'] = $rJSON['gpu_info'];
        if (shell_exec('which iostat')) {
            $rJSON['iostat_info'] = self::getIO();
        }
        if (shell_exec('which nvidia-smi')) {
            $rJSON['gpu_info'] = self::getGPUInfo();
        }
        if (shell_exec('which v4l2-ctl')) {
            $rJSON['video_devices'] = self::getVideoDevices();
        }
        if (shell_exec('which arecord')) {
            $rJSON['audio_devices'] = self::getAudioDevices();
        }
        list($rJSON['cpu_load_average']) = sys_getloadavg();
        return $rJSON;
    }
}
