<?php

function checkMessageName($messagesType, $error)
{
    foreach ($messagesType as $message) {
        if (stristr($error, $message)) {
            return true;
        }
    }
    return false;
}
set_time_limit(0);
if (!@$argc) {
    die(0);
}
require str_replace('\\', '/', dirname($argv[0])) . '/../wwwdir/init.php';
cli_set_process_title('XtreamCodes[Stream Error Parser]');
$unique_id = TMP_DIR . md5(UniqueID() . __FILE__);
KillProcessCmd($unique_id);
$typeMessageError = array('the user-agent option is deprecated', 'Last message repeated', 'deprecated', 'Packets poorly interleaved');
if ($handle = opendir(STREAMS_PATH)) {
    while (false !== ($d1af25585916b0062524737f183dfb22 = readdir($handle))) {
        if ($d1af25585916b0062524737f183dfb22 != '.' && $d1af25585916b0062524737f183dfb22 != '..' && is_file(STREAMS_PATH . $d1af25585916b0062524737f183dfb22)) {
            $Ca434bcc380e9dbd2a3a588f6c32d84f = STREAMS_PATH . $d1af25585916b0062524737f183dfb22;
            list($stream_id, $F1350a5569e4b73d2f9cb26483f2a0c1) = explode('.', $d1af25585916b0062524737f183dfb22);
            if ($F1350a5569e4b73d2f9cb26483f2a0c1 == 'errors') {
                $errors = array_values(array_unique(array_map('trim', explode('/n', file_get_contents($Ca434bcc380e9dbd2a3a588f6c32d84f)))));
                foreach ($errors as $error) {
                    if (empty($error) || checkMessageName($typeMessageError, $error)) {
                        continue;
                    }
                    $ipTV_db->query('INSERT INTO `stream_logs` (`stream_id`,`server_id`,`date`,`error`) VALUES(\'%d\',\'%d\',\'%d\',\'%s\')', $stream_id, SERVER_ID, time(), $error);
                }
                unlink($Ca434bcc380e9dbd2a3a588f6c32d84f);
            }
        }
    }
    closedir($handle);
}
$ipTV_db->query('DELETE FROM `stream_logs` WHERE `date` <= \'%d\' AND `server_id` = \'%d\'', strtotime('-3 hours'), SERVER_ID);
?>