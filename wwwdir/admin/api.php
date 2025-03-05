<?php

set_time_limit(0);
require '../init.php';
$user_ip = $_SERVER['REMOTE_ADDR'];
if (!in_array($user_ip, ipTV_streaming::getAllowedIPsAdmin()) && !in_array($user_ip, CoreUtilities::$settings['api_ips'])) {
    die(json_encode(array('result' => false, 'IP FORBIDDEN')));
}
if (!empty(CoreUtilities::$settings['api_pass']) && CoreUtilities::$request['api_pass'] != CoreUtilities::$settings['api_pass']) {
    die(json_encode(array('result' => false, 'KEY WRONG')));
}
$action = !empty(CoreUtilities::$request['action']) ? CoreUtilities::$request['action'] : '';
$sub = !empty(CoreUtilities::$request['sub']) ? CoreUtilities::$request['sub'] : '';
switch ($action) {
    case 'server':
        switch ($sub) {
            case 'list':
                $output = array();
                foreach (CoreUtilities::$Servers as $server_id => $server) {
                    $output[] = array('id' => $server_id, 'server_name' => $server['server_name'], 'online' => $server['server_online'], 'info' => json_decode($server['server_hardware'], true));
                }
                echo json_encode($output);
                break;
        }
        break;
    case 'vod':
        $stream_ids = array_map('intval', CoreUtilities::$request['stream_ids']);
        switch ($sub) {
            case 'start':
            case 'stop':
                $servers = empty(CoreUtilities::$request['servers']) ? array_keys(CoreUtilities::$Servers) : array_map('intval', CoreUtilities::$request['servers']);
                foreach ($servers as $server_id) {
                    $urls[$server_id] = array('url' => CoreUtilities::$Servers[$server_id]['api_url_ip'] . '&action=vod', 'postdata' => array('function' => $sub, 'stream_ids' => $stream_ids));
                }
                CoreUtilities::getMultiCURL($urls);
                echo json_encode(array('result' => true));
                die;
                break;
        }
        break;
    case 'stream':
        switch ($sub) {
            case 'start':
            case 'stop':
                $stream_ids = array_map('intval', CoreUtilities::$request['stream_ids']);
                $servers = empty(CoreUtilities::$request['servers']) ? array_keys(CoreUtilities::$Servers) : array_map('intval', CoreUtilities::$request['servers']);
                foreach ($servers as $server_id) {
                    $urls[$server_id] = array('url' => CoreUtilities::$Servers[$server_id]['api_url_ip'] . '&action=stream', 'postdata' => array('function' => $sub, 'stream_ids' => $stream_ids));
                }
                // $urls = array(
                //     1 => array(
                //         'url' => 'http://192.168.0.124:25461/api.php?password=XXXXXXXXXXXXXXXXXXXX&action=stream',
                //         'postdata' => array(
                //             'function' => 'start',
                //             'stream_ids' => array(1)
                //         )
                //     )
                // );
                CoreUtilities::getMultiCURL($urls);
                echo json_encode(array('result' => true));
                die;
                break;
            case 'list':
                $output = array();
                $ipTV_db->query('SELECT id,stream_display_name FROM `streams` WHERE type <> 2');
                foreach ($ipTV_db->get_rows() as $row) {
                    $output[] = array('id' => $row['id'], 'stream_name' => $row['stream_display_name']);
                }
                echo json_encode($output);
                break;
            case 'offline':
                $ipTV_db->query('SELECT t1.stream_status,t1.server_id,t1.stream_id FROM `streams_servers` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.type <> 2 WHERE t1.stream_status = 1');
                $streamSys = $ipTV_db->get_rows(true, 'stream_id', false, 'server_id');
                $output = array();
                foreach ($streamSys as $stream_id => $server_id) {
                    $output[$stream_id] = array_keys($server_id);
                }
                echo json_encode($output);
                break;
            case 'online':
                $ipTV_db->query('SELECT t1.stream_status,t1.server_id,t1.stream_id FROM `streams_servers` t1 INNER JOIN `streams` t2 ON t2.id = t1.stream_id AND t2.type <> 2 WHERE t1.pid > 0 AND t1.stream_status = 0');
                $streamSys = $ipTV_db->get_rows(true, 'stream_id', false, 'server_id');
                $output = array();
                foreach ($streamSys as $stream_id => $server_id) {
                    $output[$stream_id] = array_keys($server_id);
                }
                echo json_encode($output);
                break;
        }
        break;
    case 'stb':
        switch ($sub) {
            case 'info':
                if (!empty(CoreUtilities::$request['mac'])) {
                    $mac = CoreUtilities::$request['mac'];
                    $user_info = ipTV_streaming::getMagInfo(false, $mac, true, false, true);
                    if (!empty($user_info)) {
                        echo json_encode(array_merge(array('result' => true), $user_info));
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'NOT EXISTS'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR (mac)'));
                }
                break;
            case 'edit':
                if (!empty(CoreUtilities::$request['mac'])) {
                    $mac = CoreUtilities::$request['mac'];
                    $user_data = empty(CoreUtilities::$request['user_data']) ? array() : CoreUtilities::$request['user_data'];
                    $user_data['is_mag'] = 1;
                    $query = GetColumnNames($user_data);
                    if ($ipTV_db->query("UPDATE `lines` SET {$query} WHERE id = ( SELECT user_id FROM mag_devices WHERE `mac` = ? )", base64_encode(strtoupper($mac)))) {
                        if ($ipTV_db->affected_rows() > 0) {
                            echo json_encode(array('result' => true));
                            $ipTV_db->query('INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` ) VALUES( ?, ?, ?, ?, ? )', "SYSTEM API[{$user_ip}]", $mac, '-', time(), '[API->Edit MAG Device]');
                        } else {
                            echo json_encode(array('result' => false));
                        }
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR (user/pass)'));
                }
                break;
            case 'create':
                $user_data = empty(CoreUtilities::$request['user_data']) ? array() : CoreUtilities::$request['user_data'];
                if (!empty($user_data['mac'])) {
                    $output_formats_types = array(1, 2, 3);
                    $mac = base64_encode(strtoupper($user_data['mac']));
                    unset($user_data['mac']);
                    $user_data['username'] = CoreUtilities::generateString(10);
                    $user_data['password'] = CoreUtilities::generateString(10);
                    if (!array_key_exists('allowed_ips', $user_data) || !parseJson($user_data['allowed_ips'])) {
                        $user_data['allowed_ips'] = json_encode(array());
                    }
                    $user_data['allowed_ua'] = json_encode(array());
                    $user_data['created_at'] = time();
                    $user_data['created_by'] = 0;
                    $user_data['exp_date'] = empty($user_data['exp_date']) ? null : intval($user_data['exp_date']);
                    $user_data['bouquet'] = empty($user_data['bouquet']) || !parseJson($user_data['bouquet']) ? array() : array_map('intval', json_decode($user_data['bouquet'], true));
                    $user_data['is_mag'] = 1;
                    if (array_key_exists('mac', $user_data)) {
                        unset($user_data['mac']);
                    }
                    if (array_key_exists('output_formats', $user_data)) {
                        unset($user_data['output_formats']);
                    }
                    if (!searchQuery('mag_devices', 'mac', $mac)) {
                        $query = queryParse($user_data);
                        if ($ipTV_db->simple_query("INSERT INTO `lines` {$query}")) {
                            if ($ipTV_db->affected_rows() > 0) {
                                $user_id = $ipTV_db->last_insert_id();
                                foreach ($output_formats_types as $type) {
                                    $ipTV_db->query('INSERT INTO `user_output` ( `user_id`, `access_output_id` )VALUES( ?, ? )', $user_id, $type);
                                }
                                $ipTV_db->query('INSERT INTO `mag_devices` ( `user_id`, `mac`, `created` )VALUES( ?, ?, ? )', $user_id, $mac, time());
                                echo json_encode(array('result' => true));
                                $ipTV_db->query('INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( ?, ?, ?, ?, ? )', "SYSTEM API[{$user_ip}]", base64_decode($mac), '-', time(), '[API->New MAG Device]');
                            } else {
                                echo json_encode(array('result' => false));
                            }
                        } else {
                            echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR'));
                        }
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'EXISTS'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR (mac)'));
                }
                break;
        }
        break;
    case 'user':
        switch ($sub) {
            case 'info':
                if (!empty(CoreUtilities::$request['username']) && !empty(CoreUtilities::$request['password'])) {
                    $username = CoreUtilities::$request['username'];
                    $password = CoreUtilities::$request['password'];
                    $user_info = ipTV_streaming::getUserInfo(false, $username, $password, true, false, true);
                    if (!empty($user_info)) {
                        echo json_encode(array('result' => true, 'user_info' => $user_info));
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'NOT EXISTS'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR (user/pass)'));
                }
                break;
            case 'edit':
                if (!empty(CoreUtilities::$request['username']) && !empty(CoreUtilities::$request['password'])) {
                    $username = CoreUtilities::$request['username'];
                    $password = CoreUtilities::$request['password'];
                    $user_data = empty(CoreUtilities::$request['user_data']) ? array() : CoreUtilities::$request['user_data'];
                    $ipTV_db->query('SELECT * FROM `lines` WHERE `username` = ? and `password` = ?', $username, $password);
                    if ($ipTV_db->num_rows() > 0) {
                        $query = GetColumnNames($user_data);
                        if ($ipTV_db->query("UPDATE `lines` SET {$query} WHERE `username` = ? and `password` = ?", $username, $password)) {
                            echo json_encode(array('result' => true));
                            $ipTV_db->query('INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( ?, ?, ?, ?, ? )', "SYSTEM API[{$user_ip}]", $username, $password, time(), '[API->Edit Line]');
                        } else {
                            echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR'));
                        }
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'NOT EXISTS'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR (user/pass)'));
                }
                break;
            case 'create':
                $output_formats_types = array(1, 2, 3);
                $user_data = empty(CoreUtilities::$request['user_data']) ? array() : CoreUtilities::$request['user_data'];
                if (!array_key_exists('username', $user_data)) {
                    $user_data['username'] = CoreUtilities::generateString(10);
                }
                if (!array_key_exists('password', $user_data)) {
                    $user_data['password'] = CoreUtilities::generateString(10);
                }
                if (!array_key_exists('allowed_ips', $user_data) || !parseJson($user_data['allowed_ips'])) {
                    $user_data['allowed_ips'] = json_encode(array());
                }
                if (!array_key_exists('allowed_ua', $user_data) || !parseJson($user_data['allowed_ua'])) {
                    $user_data['allowed_ua'] = json_encode(array());
                }
                $user_data['created_at'] = time();
                $user_data['created_by'] = 0;
                $user_data['exp_date'] = empty($user_data['exp_date']) ? null : intval($user_data['exp_date']);
                $user_data['bouquet'] = empty($user_data['bouquet']) || !parseJson($user_data['bouquet']) ? array() : array_map('intval', json_decode($user_data['bouquet'], true));
                $output_formats_types = empty($user_data['output_formats']) || !parseJson($user_data['output_formats']) ? $output_formats_types : array_map('intval', $user_data['output_formats']);
                if (array_key_exists('output_formats', $user_data)) {
                    unset($user_data['output_formats']);
                }
                $ipTV_db->query('SELECT id FROM `lines` WHERE `username` = ? AND `password` = ? LIMIT 1', $user_data['username'], $user_data['password']);
                if ($ipTV_db->num_rows() == 0) {
                    $query = queryParse($user_data);
                    if ($ipTV_db->simple_query("INSERT INTO `lines` {$query}")) {
                        if ($ipTV_db->affected_rows() > 0) {
                            $user_id = $ipTV_db->last_insert_id();
                            foreach ($output_formats_types as $type) {
                                $ipTV_db->query('INSERT INTO `user_output` ( `user_id`, `access_output_id` ) VALUES( ?, ? )', $user_id, $type);
                            }
                            echo json_encode(array('result' => true, 'created_id' => $user_id, 'username' => $user_data['username'], 'password' => $user_data['password']));
                            $ipTV_db->query('INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( ?, ?, ?, ?, ? )', "SYSTEM API[{$user_ip}]", $user_data['username'], $user_data['password'], time(), '[API->New Line]');
                        } else {
                            echo json_encode(array('result' => false));
                        }
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'EXISTS'));
                }
                break;
        }
        break;
    case 'reg_user':
        switch ($sub) {
            case 'list':
                $ipTV_db->query('SELECT id,username,credits,group_id,group_name,last_login,date_registered,email,ip,status FROM `reg_users` t1 INNER JOIN `member_groups` t2 ON t1.member_group_id = t2.group_id');
                $results = $ipTV_db->get_rows();
                echo json_encode($results);
                break;
            case 'credits':
                if (!empty(CoreUtilities::$request['amount']) && (!empty(CoreUtilities::$request['id']) || !empty(CoreUtilities::$request['username']))) {
                    $amount = sprintf('%.2f', CoreUtilities::$request['amount']);
                    if (!empty(CoreUtilities::$request['id'])) {
                        $ipTV_db->query('SELECT * FROM reg_users WHERE `id` = ?', CoreUtilities::$request['id']);
                    } else {
                        $ipTV_db->query('SELECT * FROM reg_users WHERE `username` = ?', CoreUtilities::$request['username']);
                    }
                    if ($ipTV_db->num_rows()) {
                        $RegUser = $ipTV_db->get_row();
                        $credits = $amount + $RegUser['credits'];
                        if ($credits < 0) {
                            echo json_encode(array('result' => true, 'error' => 'NOT ENOUGH CREDITS'));
                        } else {
                            $ipTV_db->query('UPDATE reg_users SET `credits` = \'%.2f\' WHERE `id` = ?', $credits, $RegUser['id']);
                            echo json_encode(array('result' => true));
                            $ipTV_db->query('INSERT INTO `reg_userlog` ( `owner`, `username`, `password`, `date`, `type` )VALUES( ?, ?, ?, ?, ? )', "SYSTEM API[{$user_ip}]", $user_data['username'], $user_data['password'], time(), "[API->ADD Credits {$amount}]");
                        }
                    } else {
                        echo json_encode(array('result' => false, 'error' => 'NOT EXISTS'));
                    }
                } else {
                    echo json_encode(array('result' => false, 'error' => 'PARAMETER ERROR (amount & id||username)'));
                }
                break;
        }
        break;
}

function GetColumnNames($data) {
    global $ipTV_db;
    $query = '';
    foreach ($data as $columnName => $value) {
        $columnName = preg_replace('/[^a-zA-Z0-9\\_]+/', '', $columnName);
        if (is_array($value)) {
            $query .= "`{$columnName}` = '" . $ipTV_db->escape(json_encode($value)) . '\',';
        } elseif (is_null($value)) {
            $query .= "`{$columnName}` = null,";
        } else {
            $query .= "`{$columnName}` = '" . $ipTV_db->escape($value) . '\',';
        }
    }
    return rtrim($query, ',');
}

function queryParse($data) {
    global $ipTV_db;
    $query = '(';
    foreach (array_keys($data) as $columnName) {
        $columnName = preg_replace('/[^a-zA-Z0-9\\_]+/', '', $columnName);
        $query .= "`{$columnName}`,";
    }
    $query = rtrim($query, ',') . ') VALUES (';
    foreach (array_values($data) as $value) {
        if (is_array($value)) {
            $query .= '\'' . $ipTV_db->escape(json_encode($value)) . '\',';
        } elseif (is_null($value)) {
            $query .= 'NULL,';
        } else {
            $query .= '\'' . $ipTV_db->escape($value) . '\',';
        }
    }
    $query = rtrim($query, ',') . ');';
    return $query;
}

function parseJson($string) {
    return is_array(json_decode($string, true));
}

function searchQuery($tableName, $columnName, $value) {
    global $ipTV_db;
    $ipTV_db->query("SELECT * FROM `{$tableName}` WHERE `{$columnName}` = ?", $value);
    if ($ipTV_db->num_rows() > 0) {
        return true;
    }
    return false;
}