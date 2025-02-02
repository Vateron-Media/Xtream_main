<?php

// Register shutdown function to handle cleanup
register_shutdown_function("shutdown");

set_time_limit(0); // Prevent script timeout
require_once "../init.php"; // Initialize necessary libraries

// Unset unnecessary settings for security reasons
unset(ipTV_lib::$settings["watchdog_data"]);
unset(ipTV_lib::$settings["server_hardware"]);

// Set CORS headers to allow access from any origin
header("Access-Control-Allow-Origin: *");

// Optional headers (commented out for flexibility)
//if (ipTV_lib::$settings["send_altsvc_header"]) {
//    $httpsPort = ipTV_lib::$Servers[SERVER_ID]["https_broadcast_port"];
//    header("Alt-Svc: h3-29=\":$httpsPort\"; ma=2592000, h3-T051=\":$httpsPort\"; ma=2592000, h3-Q050=\":$httpsPort\"; ma=2592000, h3-Q046=\":$httpsPort\"; ma=2592000, h3-Q043=\":$httpsPort\"; ma=2592000, quic=\":$httpsPort\"; ma=2592000; v=\"46,43\"");
//}

// Set unique domain header if applicable
//if (empty(ipTV_lib::$settings["send_unique_header_domain"]) && !filter_var(HOST, FILTER_VALIDATE_IP)) {
//    ipTV_lib::$settings["send_unique_header_domain"] = "." . HOST;
//}

// Generate unique authentication token if enabled
//if (!empty(ipTV_lib::$settings["send_unique_header"])) {
//    $expiresAt = new DateTime("+6 months", new DateTimeZone("GMT"));
//    header("Set-Cookie: " . ipTV_lib::$settings["send_unique_header"] . "=" . generateString(11) . "; Domain=" . ipTV_lib::$settings["send_unique_header_domain"] . "; Expires=" . $expiresAt->format(DATE_RFC2822) . "; Path=/; Secure; HttpOnly; SameSite=none");
//}

// Define key variables
$rCreateExpiration = ipTV_lib::$settings["create_expiration"] ?: 5;
$rIP = ipTV_streaming::getUserIP();
$rUserAgent = !empty($_SERVER["HTTP_USER_AGENT"]) ? htmlentities(trim($_SERVER["HTTP_USER_AGENT"])) : "";
$rConSpeedFile = null;
$rDivergence = 0;
$closeCon = false;
$PID = getmypid();
$rStartTime = time();
$rVideoCodec = null;

// Validate and decrypt token data
if (isset(ipTV_lib::$request["token"])) {
    $tokenData = json_decode(decryptData(ipTV_lib::$request["token"], ipTV_lib::$settings["live_streaming_pass"], OPENSSL_EXTRA), true);
    
    if (!is_array($tokenData)) {
        ipTV_streaming::clientLog(0, 0, "LB_TOKEN_INVALID", $rIP);
        generateError("LB_TOKEN_INVALID");
    }

        // Check token expiration
    if (isset($tokenData["expires"]) && $tokenData["expires"] < time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"]) {
        generateError("TOKEN_EXPIRED");
    }
    
    // Extract token data
    if (!isset($tokenData["video_path"])) {
        $rUsername = $tokenData["username"];
        $rPassword = $tokenData["password"];
        $streamID = (int) $tokenData["stream_id"];
        $rExtension = $tokenData["extension"];
        $rChannelInfo = $tokenData["channel_info"];
        $userInfo = $tokenData["user_info"];
        $rActivityStart = $tokenData["activity_start"];
        $rExternalDevice = $tokenData["external_device"];
        $rVideoCodec = $tokenData["video_codec"];
        $rCountryCode = $tokenData["country_code"];
    } else {
        // Serve video file directly if path is provided
        header("Content-Type: video/mp2t");
        readfile($tokenData["video_path"]);
        exit;
    }
} else {
    generateError("NO_TOKEN_SPECIFIED");
}

// Determine the correct streaming format
if (!isset($rExtension['ts']) && !isset($rExtension['m3u8'])) {
    $rExtension = ipTV_lib::$settings["api_container"];
}

// Disable buffering if specified
if (ipTV_lib::$settings["use_buffer"] == 0) {
    header("X-Accel-Buffering: no");
}

// Ensure the channel is available
if ($rChannelInfo) {
    $rServerID = $rChannelInfo["redirect_id"] ?: SERVER_ID;

    // Retrieve PID and monitor PID from file system
    $streamPIDPath = STREAMS_PATH . $streamID . "_.pid";
    $monitorPIDPath = STREAMS_PATH . $streamID . "_.monitor";
    
    if (file_exists($streamPIDPath)) {
        $rChannelInfo["pid"] = (int) file_get_contents($streamPIDPath);
    }
    if (file_exists($monitorPIDPath)) {
        $rChannelInfo["monitor_pid"] = (int) file_get_contents($monitorPIDPath);
    }
    
    // Handle on-demand streaming
    if (ipTV_lib::$settings["on_demand_instant_off"] && $rChannelInfo["on_demand"] == 1) {
        ipTV_streaming::addToQueue($streamID, $PID);
    }
    
    // Ensure the stream is running; otherwise, attempt to start it
    if (!ipTV_streaming::isStreamRunning($rChannelInfo["pid"], $streamID)) {
        $rChannelInfo["pid"] = null;
        
        if ($rChannelInfo["on_demand"] == 1) {
            if (!ipTV_streaming::checkMonitorRunning($rChannelInfo["monitor_pid"], $streamID)) {
                if (time() > $rActivityStart + $rCreateExpiration - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"]) {
                    generateError("TOKEN_EXPIRED");
                }

                ipTV_stream::startMonitor($streamID);

                // Wait for monitor to start
                for ($rRetries = 0; !file_exists($monitorPIDPath) && $rRetries < 300; $rRetries++) {
                    usleep(10000);
                }

                $rChannelInfo["monitor_pid"] = file_exists($monitorPIDPath) ? (int) file_get_contents($monitorPIDPath) : null;
            }
            
            // If monitor fails, show an error video
            if (!$rChannelInfo["monitor_pid"]) {
                ipTV_streaming::ShowVideoServer("show_not_on_air_video", "not_on_air_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
            }
            
            // Wait for stream PID file
            for ($rRetries = 0; !file_exists($streamPIDPath) && $rRetries < 300; $rRetries++) {
                usleep(10000);
            }
            
            $rChannelInfo["pid"] = file_exists($streamPIDPath) ? (int) file_get_contents($streamPIDPath) : null;
            
            // If stream PID is missing, show an error video
            if (!$rChannelInfo["pid"]) {
                ipTV_streaming::ShowVideoServer("show_not_on_air_video", "not_on_air_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
            }
        } else {
            ipTV_streaming::ShowVideoServer("show_not_on_air_video", "not_on_air_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
        }
    }
    
    $rRetries = 0;
    $playlist = STREAMS_PATH . $streamID . "_.m3u8";
    $maxRetries = (int) ipTV_lib::$settings["on_demand_wait_time"] * 10; // Max retries based on wait time
    
    // Check if streaming format is TS
    if ($rExtension === "ts") {
        // First TS segment file
        $rFirstTS = STREAMS_PATH . $streamID . "_0.ts";
        $rFP = null;
    
        // Wait until the TS file appears or max retries reached
        while ($rRetries < $maxRetries) {
            if (file_exists($rFirstTS) && !$rFP) {
                $rFP = fopen($rFirstTS, "r");
            }
    
            // Validate that the monitor and stream are running
            if (!ipTV_streaming::checkMonitorRunning($rChannelInfo["monitor_pid"], $streamID) || 
                !ipTV_streaming::isStreamRunning($rChannelInfo["pid"], $streamID)) {
                ipTV_streaming::ShowVideoServer("show_not_on_air_video", "not_on_air_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
            }
    
            // Check if file can be read
            if ($rFP && fread($rFP, 1)) {
                break; // File is readable, exit loop
            }
    
            usleep(100000);
            $rRetries++;
        }
    
        if ($rFP) {
            fclose($rFP);
        }
    } else {
        // For non-TS streams (e.g., HLS), wait for playlist or first TS file
        while (!file_exists($playlist) && !file_exists(STREAMS_PATH . $streamID . "_0.ts") && $rRetries < $maxRetries) {
            usleep(100000);
            $rRetries++;
        }
    }
    
    // If max retries reached, terminate with an error
    if ($rRetries >= $maxRetries) {
        generateError("WAIT_TIME_EXPIRED");
    }
    
    // Ensure PID is set by reading from the file system if necessary
    if (!$rChannelInfo["pid"] && file_exists(STREAMS_PATH . $streamID . "_.pid")) {
        $rChannelInfo["pid"] = (int) file_get_contents(STREAMS_PATH . $streamID . "_.pid");
    }
    
    // Calculate stream expiration time
    $rExecutionTime = time() - $rStartTime;
    $rExpiresAt = $rActivityStart + $rCreateExpiration + $rExecutionTime - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"];
    
    // Connect to the appropriate data store (Redis or MySQL)
    if (ipTV_lib::$settings["redis_handler"]) {
        ipTV_lib::connectRedis();
    } elseif (is_object($ipTV_db)) {
        $ipTV_db->db_connect();
    }
    
    // Check if second IP connections are disallowed
    if (
        ipTV_lib::$settings["disallow_2nd_ip_con"] && 
        !$userInfo["is_restreamer"] && 
        ($userInfo["max_connections"] < ipTV_lib::$settings["disallow_2nd_ip_max"] && $userInfo["max_connections"] > 0 || 
        ipTV_lib::$settings["disallow_2nd_ip_max"] == 0)
    ) {
        $rAcceptIP = null;
    
        // Retrieve active connections (Redis or MySQL)
        if (ipTV_lib::$settings["redis_handler"]) {
            $rConnections = ipTV_streaming::getConnections($userInfo["id"], true);
            if (!empty($rConnections)) {
                // Sort connections by date (oldest first) and get the first user's IP
                usort($rConnections, fn($a, $b) => $a['date_start'] <=> $b['date_start']);
                $rAcceptIP = $rConnections[0]["user_ip"];
            }
        } else {
            $query = "SELECT `user_ip` FROM `lines_live` WHERE `user_id` = ? AND `hls_end` = 0 ORDER BY `activity_id` DESC LIMIT 1;";
            $ipTV_db->query($query, $userInfo["id"]);
            
            if ($ipTV_db->num_rows() == 1) {
                $rAcceptIP = $ipTV_db->get_row()["user_ip"];
            }
        }
    
        // Validate IP match based on full or subnet comparison
        if ($rAcceptIP) {
            $rIPMatch = ipTV_lib::$settings["ip_subnet_match"]
                ? implode(".", array_slice(explode(".", $rAcceptIP), 0, -1)) === implode(".", array_slice(explode(".", $rIP), 0, -1))
                : $rAcceptIP === $rIP;
    
            // If IP does not match, reject connection
            if (!$rIPMatch) {
                ipTV_streaming::clientLog($streamID, $userInfo["id"], "USER_ALREADY_CONNECTED", $rIP);
                ipTV_streaming::ShowVideoServer("show_connected_video", "connected_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
            }
        }
    }

    switch ($rExtension) {
        case "m3u8":
            if (ipTV_lib::$settings["redis_handler"]) {
                $rConnection = ipTV_streaming::getConnection($tokenData["uuid"]);
            } else {
                if (isset($tokenData["adaptive"])) {
                    $ipTV_db->query("SELECT `activity_id`, `user_ip` FROM `lines_live` WHERE `uuid` = ? AND `user_id` = ? AND `container` = 'hls' AND `hls_end` = 0", $tokenData["uuid"], $userInfo["id"]);
                } else {
                    $ipTV_db->query("SELECT `activity_id`, `user_ip` FROM `lines_live` WHERE `uuid` = ? AND `user_id` = ? AND `server_id` = ? AND `container` = 'hls' AND `stream_id` = ? AND `hls_end` = 0", $tokenData["uuid"], $userInfo["id"], $rServerID, $streamID);
                }
                if ($ipTV_db->num_rows() > 0) {
                    $rConnection = $ipTV_db->get_row();
                }
            }
            if (!$rConnection) {
                if (time() > $rExpiresAt) {
                    generateError("TOKEN_EXPIRED");
                }
                if (ipTV_lib::$settings["redis_handler"]) {
                    $rConnectionData = ["user_id" => $userInfo["id"], "stream_id" => $streamID, "server_id" => $rServerID, "user_agent" => $rUserAgent, "user_ip" => $rIP, "container" => "hls", "pid" => null, "date_start" => $rActivityStart, "geoip_country_code" => $rCountryCode, "isp" => $userInfo["con_isp_name"], "external_device" => $rExternalDevice, "hls_end" => 0, "hls_last_read" => time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"], "on_demand" => $rChannelInfo["on_demand"], "identity" => $userInfo["id"], "uuid" => $tokenData["uuid"]];
                    $rResult = ipTV_streaming::createConnection($rConnectionData);
                } else {
                    $rResult = $ipTV_db->query("INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`uuid`,`date_start`,`geoip_country_code`,`isp`,`external_device`,`hls_last_read`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", $userInfo["id"], $streamID, $rServerID, $rUserAgent, $rIP, "hls", null, $tokenData["uuid"], $rActivityStart, $rCountryCode, $userInfo["con_isp_name"], $rExternalDevice, time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"]);
                }
            } else {
                $rIPMatch = ipTV_lib::$settings["ip_subnet_match"] ? implode(".", array_slice(explode(".", $rConnection["user_ip"]), 0, -1)) == implode(".", array_slice(explode(".", $rIP), 0, -1)) : $rConnection["user_ip"] == $rIP;
                if (!$rIPMatch && ipTV_lib::$settings["restrict_same_ip"]) {
                    ipTV_streaming::clientLog($streamID, $userInfo["id"], "IP_MISMATCH", $rIP);
                    generateError("IP_MISMATCH");
                }
                if (ipTV_lib::$settings["redis_handler"]) {
                    $rChanges = ["server_id" => $rServerID, "hls_last_read" => time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"]];
                    if ($rConnection = ipTV_streaming::updateConnection($rConnection, $rChanges, "open")) {
                        $rResult = true;
                    } else {
                        $rResult = false;
                    }
                } else {
                    $rResult = $ipTV_db->query("UPDATE `lines_live` SET `hls_last_read` = ?, `hls_end` = 0, `server_id` = ? WHERE `activity_id` = ?", time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"], $rServerID, $rConnection["activity_id"]);
                }
            }
            if (!$rResult) {
                ipTV_streaming::clientLog($streamID, $userInfo["id"], "LINE_CREATE_FAIL", $rIP);
                generateError("LINE_CREATE_FAIL");
            }
            ipTV_streaming::validateConnections($userInfo, $rIP, $rUserAgent);
            if (ipTV_lib::$settings["redis_handler"]) {
                ipTV_lib::closeRedis();
            } else {
                $ipTV_db->close_mysql();
            }
            $rHLS = ipTV_streaming::generateHLS($playlist, isset($rUsername) ? $rUsername : null, isset($rPassword) ? $rPassword : null, $streamID, $tokenData["uuid"], $rIP, $rVideoCodec, (int) $rChannelInfo["on_demand"], $rServerID);
            if ($rHLS) {
                touch(CONS_TMP_PATH . $tokenData["uuid"]);
                ob_end_clean();
                header("Content-Type: application/x-mpegurl");
                header("Content-Length: " . strlen($rHLS));
                header("Cache-Control: no-store, no-cache, must-revalidate");
                echo $rHLS;
            } else {
                ipTV_streaming::ShowVideoServer("show_not_on_air_video", "not_on_air_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
            }
            exit;
        default:
            if (ipTV_lib::$settings["redis_handler"]) {
                $rConnection = ipTV_streaming::getConnection($tokenData["uuid"]);
            } else {
                $ipTV_db->query("SELECT `activity_id`, `pid`, `user_ip` FROM `lines_live` WHERE `uuid` = ? AND `user_id` = ? AND `server_id` = ? AND `container` = ? AND `stream_id` = ?;", $tokenData["uuid"], $userInfo["id"], $rServerID, $rExtension, $streamID);

                if ($ipTV_db->num_rows() > 0) {
                    $rConnection = $ipTV_db->get_row();
                }
            }
            if (!$rConnection) {
                if (time() > $rExpiresAt) {
                    generateError("TOKEN_EXPIRED");
                }
                if (ipTV_lib::$settings["redis_handler"]) {
                    $rConnectionData = ["user_id" => $userInfo["id"], "stream_id" => $streamID, "server_id" => $rServerID, "user_agent" => $rUserAgent, "user_ip" => $rIP, "container" => $rExtension, "pid" => $PID, "date_start" => $rActivityStart, "geoip_country_code" => $rCountryCode, "isp" => $userInfo["con_isp_name"], "external_device" => $rExternalDevice, "hls_end" => 0, "hls_last_read" => time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"], "on_demand" => $rChannelInfo["on_demand"], "identity" => $userInfo["id"], "uuid" => $tokenData["uuid"]];
                    $rResult = ipTV_streaming::createConnection($rConnectionData);
                } else {
                    $rResult = $ipTV_db->query("INSERT INTO `lines_live` (`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,`pid`,`uuid`,`date_start`,`geoip_country_code`,`isp`,`external_device`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $userInfo["id"], $streamID, $rServerID, $rUserAgent, $rIP, $rExtension, $PID, $tokenData["uuid"], $rActivityStart, $rCountryCode, $userInfo["con_isp_name"], $rExternalDevice);
                }
            } else {
                $rIPMatch = ipTV_lib::$settings["ip_subnet_match"] ? implode(".", array_slice(explode(".", $rConnection["user_ip"]), 0, -1)) == implode(".", array_slice(explode(".", $rIP), 0, -1)) : $rConnection["user_ip"] == $rIP;
                if (!$rIPMatch && ipTV_lib::$settings["restrict_same_ip"]) {
                    ipTV_streaming::clientLog($streamID, $userInfo["id"], "IP_MISMATCH", $rIP);
                    generateError("IP_MISMATCH");
                }
                if (ipTV_streaming::isProcessRunning($rConnection["pid"], "php-fpm") && $PID != $rConnection["pid"] && is_numeric($rConnection["pid"]) && 0 < $rConnection["pid"]) {
                    posix_kill((int) $rConnection["pid"], 9);
                }
                if (ipTV_lib::$settings["redis_handler"]) {
                    $rChanges = ["pid" => $PID, "hls_last_read" => time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"]];
                    if ($rConnection = ipTV_streaming::updateConnection($rConnection, $rChanges, "open")) {
                        $rResult = true;
                    } else {
                        $rResult = false;
                    }
                } else {
                    $rResult = $ipTV_db->query("UPDATE `lines_live` SET `hls_end` = 0, `hls_last_read` = ?, `pid` = ? WHERE `activity_id` = ?;", time() - (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"], $PID, $rConnection["activity_id"]);
                }
            }
            if (!$rResult) {
                ipTV_streaming::clientLog($streamID, $userInfo["id"], "LINE_CREATE_FAIL", $rIP);
                generateError("LINE_CREATE_FAIL");
            }
            ipTV_streaming::validateConnections($userInfo, $rIP, $rUserAgent);
            if (ipTV_lib::$settings["redis_handler"]) {
                ipTV_lib::closeRedis();
            } else {
                $ipTV_db->close_mysql();
            }
            $closeCon = true;
            if (ipTV_lib::$settings["monitor_connection_status"]) {
                ob_implicit_flush(true);
                while (ob_get_level()) {
                    ob_end_clean();
                }
            }
            touch(CONS_TMP_PATH . $tokenData["uuid"]);
            header("Content-Type: video/mp2t");
            $rConSpeedFile = DIVERGENCE_TMP_PATH . $tokenData["uuid"];
            if (file_exists($playlist)) {
                if ($userInfo["is_restreamer"]) {
                    if ($tokenData["prebuffer"]) {
                        $rPrebuffer = ipTV_lib::$SegmentsSettings["seg_time"];
                    } else {
                        $rPrebuffer = ipTV_lib::$settings["restreamer_prebuffer"];
                    }
                } else {
                    $rPrebuffer = ipTV_lib::$settings["client_prebuffer"];
                }
                if (file_exists(STREAMS_PATH . $streamID . "_.dur")) {
                    $rDuration = (int) file_get_contents(STREAMS_PATH . $streamID . "_.dur");
                    if ($rDuration > ipTV_lib::$SegmentsSettings["seg_time"]) {
                        ipTV_lib::$SegmentsSettings["seg_time"] = $rDuration;
                    }
                }
                $rSegments = ipTV_streaming::getPlaylistSegments($playlist, $rPrebuffer, ipTV_lib::$SegmentsSettings["seg_time"]);
            } else {
                $rSegments = null;
            }
            if (!is_null($rSegments)) {
                if (is_array($rSegments)) {
                    $rBytes = 0;
                    $rStartTime = time();
                    foreach ($rSegments as $rSegment) {
                        $filePath = STREAMS_PATH . $rSegment;
                        if (!file_exists($filePath)) {
                            exit;
                        }
                        $fileSize = readfile($filePath);
                        if ($fileSize === false) {
                            exit;
                        }
                        $rBytes += $fileSize; // Changed from concatenation to addition
                    }
                    $rTotalTime = time() - $rStartTime;
                    $rDivergence = 0;
                    if ($rTotalTime > 0) {
                        $rDivergence = (int) ($rBytes / $rTotalTime / 1024);
                    }
                    file_put_contents($rConSpeedFile, $rDivergence);
                    preg_match("/_(.*)\\./", array_pop($rSegments), $rCurrentSegment);
                    $rCurrent = $rCurrentSegment[1];
                } else {
                    $rCurrent = $rSegments;
                }
            } else {
                if (!file_exists($playlist)) {
                    $rCurrent = -1;
                } else {
                    exit;
                }
            }
            $rFails = 0;
            $rTotalFails = ipTV_lib::$SegmentsSettings["seg_time"] * 2;
            if ($rTotalFails < (int) ipTV_lib::$settings["segment_wait_time"] ?: 20) {
                $rTotalFails = (int) ipTV_lib::$settings["segment_wait_time"] ?: 20;
            }
            $rMonitorCheck = $rLastCheck = time();
            while (true) {
                $rSegmentFile = sprintf("%d_%d.ts", $streamID, $rCurrent + 1);
                $rNextSegment = sprintf("%d_%d.ts", $streamID, $rCurrent + 2);
                for ($rChecks = 0; !file_exists(STREAMS_PATH . $rSegmentFile) && $rChecks < $rTotalFails; $rChecks++) {
                    sleep(1);
                }
                if (file_exists(STREAMS_PATH . $rSegmentFile)) {
                    if (file_exists(SIGNALS_PATH . $tokenData["uuid"])) {
                        $rSignalData = json_decode(file_get_contents(SIGNALS_PATH . $tokenData["uuid"]), true);
                        if ($rSignalData["type"] == "signal") {
                            for ($rChecks = 0; !file_exists(STREAMS_PATH . $rNextSegment) && $rChecks < $rTotalFails; $rChecks++) {
                                sleep(1);
                            }
                            ipTV_streaming::sendSignalFFMPEG($rSignalData, $rSegmentFile, $rVideoCodec ?: "h264");
                            unlink(SIGNALS_PATH . $tokenData["uuid"]);
                            $rCurrent++;
                        }
                    }
                    $rFails = 0;
                    $rTimeStart = time();
                    $rFP = fopen(STREAMS_PATH . $rSegmentFile, "r");
                    while ($rFails < $rTotalFails && !file_exists(STREAMS_PATH . $rNextSegment)) {
                        $rData = stream_get_line($rFP, ipTV_lib::$settings["read_buffer_size"]);
                        if (!empty($rData)) {
                            echo $rData;
                            $rData = "";
                            $rFails = 0;
                        } else {
                            if (ipTV_streaming::isStreamRunning($rChannelInfo["pid"], $streamID)) {
                                sleep(1);
                                $rFails++;
                            }
                        }
                    }
                    if (ipTV_streaming::isStreamRunning($rChannelInfo["pid"], $streamID) && $rFails < $rTotalFails && file_exists(STREAMS_PATH . $rSegmentFile) && is_resource($rFP)) {
                        $rSegmentSize = filesize(STREAMS_PATH . $rSegmentFile);
                        $rRestSize = $rSegmentSize - ftell($rFP);
                        if ($rRestSize > 0) {
                            echo stream_get_line($rFP, $rRestSize);
                        }
                        $rTotalTime = time() - $rTimeStart;
                        if ($rTotalTime > 0) {
                            file_put_contents($rConSpeedFile, (int) ($rSegmentSize / 1024 / $rTotalTime));
                        }
                    } else {
                        if (!($userInfo["is_restreamer"] == 1 || $rTotalFails < $rFails)) {
                            for ($rChecks = 0; $rChecks < ipTV_lib::$SegmentsSettings["seg_time"] && !ipTV_streaming::isStreamRunning($rChannelInfo["pid"], $streamID); $rChecks++) {
                                if (file_exists(STREAMS_PATH . $streamID . "_.pid")) {
                                    $rChannelInfo["pid"] = (int) file_get_contents(STREAMS_PATH . $streamID . "_.pid");
                                }
                            }
                            sleep(1);
                            if ($rChecks < ipTV_lib::$SegmentsSettings["seg_time"] && ipTV_streaming::isStreamRunning($rChannelInfo["pid"], $streamID)) {
                                if (!file_exists(STREAMS_PATH . $rNextSegment)) {
                                    $rCurrent = -2;
                                }
                            } else {
                                exit;
                            }
                        } else {
                            exit;
                        }
                    }
                    fclose($rFP);
                    $rFails = 0;
                    $rCurrent++;
                    if (ipTV_lib::$settings["monitor_connection_status"] && 5 < time() - $rMonitorCheck) {
                        if (connection_status() == CONNECTION_NORMAL) {
                            $rMonitorCheck = time();
                        } else {
                            exit;
                        }
                    }
                    if (time() - $rLastCheck > 300) {
                        $rLastCheck = time();
                        $rConnection = null;
                        ipTV_lib::getCache("settings");
                        ipTV_lib::$settings;
                        if (ipTV_lib::$settings["redis_handler"]) {
                            ipTV_lib::connectRedis();
                            $rConnection = ipTV_streaming::getConnection($tokenData["uuid"]);
                            ipTV_lib::closeRedis();
                        } else {
                            $ipTV_db->db_connect();
                            $ipTV_db->query("SELECT `pid`, `hls_end` FROM `lines_live` WHERE `uuid` = ?", $tokenData["uuid"]);
                            if ($ipTV_db->num_rows() == 1) {
                                $rConnection = $ipTV_db->get_row();
                            }
                            $ipTV_db->close_mysql();
                        }
                        if (!is_array($rConnection) || $rConnection["hls_end"] != 0 || $rConnection["pid"] != $PID) {
                            exit;
                        }
                    }
                } else {
                    exit;
                }
            }
    }
} else {
    ipTV_streaming::ShowVideoServer("show_not_on_air_video", "not_on_air_video_path", $rExtension, $userInfo, $rIP, $rCountryCode, $userInfo["con_isp_name"], $rServerID);
}

function shutdown() {
    
    global $closeCon, $tokenData, $PID, $rChannelInfo, $streamID, $ipTV_db;

    // Ensure settings are loaded (uncomment if needed)
    // ipTV_lib::getCache("settings");

    // Check if connection needs to be closed
    if ($closeCon) {
        $timeOffset = (int) ipTV_lib::$Servers[SERVER_ID]["time_offset"];
        $hlsLastRead = time() - $timeOffset;

        // Handle Redis-based session tracking
        if (ipTV_lib::$settings["redis_handler"]) {
            if (!is_object(ipTV_lib::$redis)) {
                ipTV_lib::connectRedis();
            }

            // Fetch the connection details using the token's UUID
            $rConnection = ipTV_streaming::getConnection($tokenData["uuid"]);
            if ($rConnection && $rConnection["pid"] == $PID) {
                $rChanges = ["hls_last_read" => $hlsLastRead];
                ipTV_streaming::updateConnection($rConnection, $rChanges, "close");
            }
        } 
        // Handle MySQL-based session tracking
        else {
            if (!is_object($ipTV_db)) {
                $ipTV_db->db_connect();
            }
            // Mark stream as ended and update last read timestamp
            $ipTV_db->query(
                "UPDATE `lines_live` SET `hls_end` = 1, `hls_last_read` = ? WHERE `uuid` = ? AND `pid` = ?;",
                $hlsLastRead, 
                $tokenData["uuid"], 
                $PID
            );
        }

        // Clean up temporary files associated with the streaming session
        ipTV_lib::unlinkFile(CONS_TMP_PATH . $tokenData["uuid"]);
        ipTV_lib::unlinkFile(CONS_TMP_PATH . $streamID . "/" . $tokenData["uuid"]);
    }

    // Handle On-Demand instant shutdown (if enabled)
    if (ipTV_lib::$settings["on_demand_instant_off"] && $rChannelInfo["on_demand"] == 1) {
        ipTV_streaming::removeFromQueue($streamID, $PID);
    }

    // Close database or Redis connections if they were used
    if (!ipTV_lib::$settings["redis_handler"] && is_object($ipTV_db)) {
        $ipTV_db->close_mysql();
    } elseif (ipTV_lib::$settings["redis_handler"] && is_object(ipTV_lib::$redis)) {
        ipTV_lib::closeRedis();
    }
}
