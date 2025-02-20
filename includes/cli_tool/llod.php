<?php
function startLLOD($rStreamID, $rStreamSources, $rStreamArguments, $rRequestPrebuffer, $rSegListSize, $rSegDeleteThreshold)
{
    global $rSegmentStatus, $rSegmentFile, $rFP, $rCurPTS, $rLastPTS;

    if (!file_exists(STREAMS_PATH . $rStreamID . '/')) {
        mkdir(STREAMS_PATH . $rStreamID, 0777, true);
    }

    $rUserAgent = isset($rStreamArguments['user_agent']) ? 
        ($rStreamArguments['user_agent']['value'] ?: $rStreamArguments['user_agent']['argument_default_value']) : 
        'Mozilla/5.0';

    $rOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ],
        'http' => [
            'method' => 'GET',
            'user_agent' => $rUserAgent,
            'timeout' => TIMEOUT,
            'header' => ''
        ]
    ];

    // Add proxy if specified
    if (isset($rStreamArguments['proxy'])) {
        $rOptions['http']['proxy'] = 'tcp://' . $rStreamArguments['proxy']['value'];
        $rOptions['http']['request_fulluri'] = true;
    }

    // Add cookie if specified
    if (isset($rStreamArguments['cookie'])) {
        $rOptions['http']['header'] .= 'Cookie: ' . $rStreamArguments['cookie']['value'] . "\r\n";
    }

    // Add prebuffer header if requested
    if ($rRequestPrebuffer) {
        $rOptions['http']['header'] .= 'X-XC-Prebuffer: 1' . "\r\n";
    }

    $rContext = stream_context_create($rOptions);
    $rFP = getActiveStream($rStreamID, $rStreamSources, $rContext);
    
    if (!$rFP) {
        writeError($rStreamID, '[LLOD] Failed to get active stream');
        return false;
    }

    // Clean up existing segments
    shell_exec('rm -f ' . STREAMS_PATH . intval($rStreamID) . '_*.ts');

    // Initialize stream
    stream_set_blocking($rFP, true);
    $rExcessBuffer = $rPrebuffer = $rBuffer = $rPacket = '';
    $rHasPrebuffer = $rPATHeaders = [];

    return true;
}

function getActiveStream($rStreamID, $rURLs, $rContext)
{
    foreach ($rURLs as $rURL) {
        $rFP = @fopen($rURL, 'rb', false, $rContext);

        if ($rFP) {
            $rMetadata = stream_get_meta_data($rFP);
            $rHeaders = [];

            foreach ($rMetadata['wrapper_data'] as $rLine) {
                if (strpos($rLine, 'HTTP') === 0) {
                    $rHeaders[0] = $rLine;
                } else {
                    list($rKey, $rValue) = explode(': ', $rLine, 2);
                    $rHeaders[$rKey] = $rValue;
                }
            }

            $rContentType = isset($rHeaders['Content-Type']) ? 
                (is_array($rHeaders['Content-Type']) ? 
                    $rHeaders['Content-Type'][count($rHeaders['Content-Type']) - 1] : 
                    $rHeaders['Content-Type']) : '';

            if (strtolower($rContentType) == 'video/mp2t') {
                return $rFP;
            } else {
                writeError($rStreamID, '[LLOD] Source isn\'t MPEG-TS: ' . $rURL . ' - ' . $rContentType);
            }

            fclose($rFP);
        } else {
            $rError = null;
            if (isset($http_response_header)) {
                foreach ($http_response_header as $rHeader) {
                    if (preg_match('#HTTP/[0-9\\.]+\\s+([0-9]+)#', $rHeader, $rOutput)) {
                        $rError = $rHeader;
                        break;
                    }
                }
            }
            writeError($rStreamID, '[LLOD] ' . (!empty($rError) ? $rError : 'Invalid source') . ': ' . $rURL);
        }
    }

    return null;
}

function checkRunning($rStreamID)
{
    clearstatcache(true);
    $rMonitorFile = STREAMS_PATH . $rStreamID . '_.monitor';
    
    if (file_exists($rMonitorFile)) {
        $rPID = intval(file_get_contents($rMonitorFile));
        
        if ($rPID > 0) {
            if (file_exists('/proc/' . $rPID)) {
                $rCommand = trim(file_get_contents('/proc/' . $rPID . '/cmdline'));
                if ($rCommand == 'LLOD[' . $rStreamID . ']' && is_numeric($rPID)) {
                    posix_kill($rPID, 9);
                }
            }
        }
    }

    // Kill any remaining processes
    shell_exec("kill -9 `ps -ef | grep 'LLOD\\[" . intval($rStreamID) . "\\]' | grep -v grep | awk '{print \$2}'`;");
}

function deleteOldSegments($rStreamID, $rKeep, $rThreshold)
{
    global $rSegmentStatus;
    $rReturn = [];
    
    if (!empty($rSegmentStatus)) {
        $rCurrentSegment = max(array_keys($rSegmentStatus));

        foreach ($rSegmentStatus as $rSegmentID => $rStatus) {
            if (!$rStatus) {
                continue;
            }

            if ($rSegmentID < $rCurrentSegment - ($rKeep + $rThreshold) + 1) {
                $rSegmentStatus[$rSegmentID] = false;
                @unlink(STREAMS_PATH . $rStreamID . '_' . $rSegmentID . '.ts');
            } elseif ($rSegmentID != $rCurrentSegment) {
                $rReturn[] = $rSegmentID;
            }
        }

        if ($rKeep < count($rReturn)) {
            $rReturn = array_slice($rReturn, count($rReturn) - $rKeep, $rKeep);
        }
    }

    return $rReturn;
}

function updateSegments($rStreamID, $rSegmentsRemaining)
{
    global $rSegmentDuration, $rLastPTS, $rCurPTS;

    $rHLS = '#EXTM3U' . "\n" .
            '#EXT-X-VERSION:3' . "\n" .
            '#EXT-X-TARGETDURATION:4' . "\n" .
            '#EXT-X-MEDIA-SEQUENCE:';

    $rSequence = false;

    foreach ($rSegmentsRemaining as $rSegment) {
        if (file_exists(STREAMS_PATH . $rStreamID . '_' . $rSegment . '.ts')) {
            if (!$rSequence) {
                $rHLS .= $rSegment . "\n";
                $rSequence = true;
            }

            if (!isset($rSegmentDuration[$rSegment]) && $rLastPTS) {
                $rSegmentDuration[$rSegment] = ($rCurPTS - $rLastPTS) / 90000.0;
            }

            $duration = round(isset($rSegmentDuration[$rSegment]) ? $rSegmentDuration[$rSegment] : 4, 3);
            $rHLS .= '#EXTINF:' . $duration . ',' . "\n" .
                     $rStreamID . '_' . $rSegment . '.ts' . "\n";
        }
    }

    file_put_contents(STREAMS_PATH . $rStreamID . '_.m3u8', $rHLS);
}

function writeError($rStreamID, $rError)
{
    echo $rError . "\n";
    file_put_contents(STREAMS_PATH . $rStreamID . '.errors', date('[Y-m-d H:i:s] ') . $rError . "\n", FILE_APPEND | LOCK_EX);
}

function shutdown()
{
    global $rFP, $rSegmentFile, $rStreamID;
    
    if (is_resource($rSegmentFile)) {
        @fclose($rSegmentFile);
    }
    if (is_resource($rFP)) {
        @fclose($rFP);
    }
}

// Main execution
if (posix_getpwuid(posix_geteuid())['name'] != 'xtreamcodes') {
    exit('Please run as xtreamcodes!' . "\n");
}

if (!@$argc || $argc <= 3) {
    exit('LLOD cannot be directly run!' . "\n");
}

$rStreamID = intval($argv[1]);
$rStreamSources = json_decode(base64_decode($argv[2]), true);
$rStreamArguments = json_decode(base64_decode($argv[3]), true);

// Initialize settings
if (!file_exists(CACHE_TMP_PATH . 'settings')) {
    exit('Settings not cached!' . "\n");
}

checkRunning($rStreamID);
register_shutdown_function('shutdown');
set_time_limit(0);
error_reporting(E_ERROR | E_PARSE);
cli_set_process_title('LLOD[' . $rStreamID . ']');

require_once(INCLUDES_PATH . 'ts.php');

$rFP = $rSegmentFile = null;
$rSegmentDuration = $rSegmentStatus = [];
$rSettings = igbinary_unserialize(file_get_contents(CACHE_TMP_PATH . 'settings'));
$rSegListSize = $rSettings['seg_list_size'];
$rSegDeleteThreshold = $rSettings['seg_delete_threshold'];
$rRequestPrebuffer = $rSettings['request_prebuffer'];
$rLastPTS = $rCurPTS = null;

startLLOD($rStreamID, $rStreamSources, $rStreamArguments, $rRequestPrebuffer, $rSegListSize, $rSegDeleteThreshold);
