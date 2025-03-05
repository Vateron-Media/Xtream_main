<?php

require 'init.php';
header('Content-Type: application/json');
$remote_addr = $_SERVER['REMOTE_ADDR'];
$user_agent = trim($_SERVER['HTTP_USER_AGENT']);
if (!empty(CoreUtilities::$request['action']) && CoreUtilities::$request['action'] == 'gen_mac' && !empty(CoreUtilities::$request['pversion'])) {
    if (CoreUtilities::$request['pversion'] != '0.0.1') {
        echo json_encode(strtoupper(implode(':', str_split(substr(md5(mt_rand()), 0, 12), 2))));
    }
    die;
}
if (!empty(CoreUtilities::$request['action']) && CoreUtilities::$request['action'] == 'auth') {
    $mac = isset(CoreUtilities::$request['mac']) ? htmlentities(CoreUtilities::$request['mac']) : '';
    $mmac = isset(CoreUtilities::$request['mmac']) ? htmlentities(CoreUtilities::$request['mmac']) : '';
    $ip = isset(CoreUtilities::$request['ip']) ? htmlentities(CoreUtilities::$request['ip']) : '';
    $version = isset(CoreUtilities::$request['version']) ? htmlentities(CoreUtilities::$request['version']) : '';
    $type = isset(CoreUtilities::$request['type']) ? htmlentities(CoreUtilities::$request['type']) : '';
    $pversion = isset(CoreUtilities::$request['pversion']) ? htmlentities(CoreUtilities::$request['pversion']) : '';
    $lversion = isset(CoreUtilities::$request['lversion']) ? base64_decode(CoreUtilities::$request['lversion']) : '';
    $dn = !empty(CoreUtilities::$request['dn']) ? htmlentities(CoreUtilities::$request['dn']) : '-';
    $cmac = !empty(CoreUtilities::$request['cmac']) ? htmlentities(strtoupper(CoreUtilities::$request['cmac'])) : '';
    $json = array();
    if ($enigma_devices = ipTV_streaming::enigmaDevices(array('device_id' => null, 'mac' => strtoupper($mac)))) {
        if ($enigma_devices['enigma2']['lock_device'] == 1) {
            if (!empty($enigma_devices['enigma2']['modem_mac']) && $enigma_devices['enigma2']['modem_mac'] !== $mmac) {
                die(json_encode(array()));
            }
        }
        $token = strtoupper(md5(uniqid(rand(), true)));
        $seconds = mt_rand(60, 70);
        $ipTV_db->query('UPDATE `enigma2_devices` SET `original_mac` = ?,`dns` = ?,`key_auth` = ?,`lversion` = ?,`watchdog_timeout` = ?,`modem_mac` = ?,`local_ip` = ?,`public_ip` = ?,`enigma_version` = ?,`cpu` = ?,`version` = ?,`token` = ?,`last_updated` = ? WHERE `device_id` = ?', $cmac, $dn, $user_agent, $lversion, $seconds, $mmac, $ip, $remote_addr, $version, $type, $pversion, $token, time(), $enigma_devices['enigma2']['device_id']);
        $json['details'] = array();
        $json['details']['token'] = $token;
        $json['details']['username'] = $enigma_devices['user_info']['username'];
        $json['details']['password'] = $enigma_devices['user_info']['password'];
        $json['details']['watchdog_seconds'] = $seconds;
    }
    echo json_encode($json);
    die;
}
if (empty(CoreUtilities::$request['token'])) {
    die(json_encode(array('valid' => false)));
}
$token = CoreUtilities::$request['token'];
$ipTV_db->query('SELECT * FROM enigma2_devices WHERE `token` = ? AND `public_ip` = ? AND `key_auth` = ? LIMIT 1', $token, $remote_addr, $user_agent);
if ($ipTV_db->num_rows() <= 0) {
    die(json_encode(array('valid' => false)));
}
$device_info = $ipTV_db->get_row();
if (time() - $device_info['last_updated'] > $device_info['watchdog_timeout'] + 20) {
    die(json_encode(array('valid' => false)));
}
$page = isset(CoreUtilities::$request['page']) ? CoreUtilities::$request['page'] : '';
if (!empty($page)) {
    if ($page == 'file') {
        if (!empty($_FILES['f']['name'])) {
            if ($_FILES['f']['error'] == 0) {
                $tmp_name = strtolower($_FILES['f']['tmp_name']);
                $type = CoreUtilities::$request['t'];
                switch ($type) {
                    case 'screen':
                        move_uploaded_file($_FILES['f']['tmp_name'], ENIGMA2_IMAGES_PATH . $device_info['device_id'] . '_screen_' . time() . '_' . uniqid() . '.jpg');
                        break;
                }
            }
        }
    } else {
        $ipTV_db->query('UPDATE `enigma2_devices` SET `last_updated` = ?,`rc` = ? WHERE `device_id` = ?', time(), CoreUtilities::$request['rc'], $device_info['device_id']);
        $ipTV_db->query('SELECT * FROM `enigma2_actions` WHERE `device_id` = ?', $device_info['device_id']);
        $result = array();
        if ($ipTV_db->num_rows() > 0) {
            $device = $ipTV_db->get_row();
            switch ($device['key']) {
                case 'message':
                    $result['message'] = array(
                        'title' => $device['command2'],
                        'message' => $device['command']
                    );
                    break;
                case 'ssh':
                    $result['ssh'] = $device['command'];
                    break;
                case 'screen':
                    $result['screen'] = '1';
                    break;
                case 'reboot_gui':
                    $result['reboot_gui'] = 1;
                    break;
                case 'reboot':
                    $result['reboot'] = 1;
                    break;
                case 'update':
                    $result['update'] = $device['command'];
                    break;
                case 'block_ssh':
                case 'block_telnet':
                case 'block_ftp':
                case 'block_all':
                case 'block_plugin':
                    $result[$device['key']] = (int) $device['type'];
                    break;
            }
            $ipTV_db->query('DELETE FROM enigma2_actions where id = ?', $device['id']);
        }
        die(json_encode(array('valid' => true, 'data' => $result)));
    }
}
