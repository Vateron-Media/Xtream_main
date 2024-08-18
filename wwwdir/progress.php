<?php

ignore_user_abort(true);
require_once 'constants.php';
$post = trim(file_get_contents('php://input'));

if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' || empty($_GET['stream_id']) || empty($post)) {
    generate404();
}

$streamID = intval($_GET['stream_id']);
$data = array_filter(array_map('trim', explode("\n", $post)));
$output = array();

foreach ($data as $row) {
    list($rKey, $value) = explode('=', $row);
    $output[trim($rKey)] = trim($value);
}
file_put_contents(STREAMS_PATH . $streamID . '_.progress', json_encode($output));
