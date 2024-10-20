--TEST--
Check for serialization handler
--SKIPIF--
<?php
if (!extension_loaded('session')) {
	exit('skip session extension not loaded');
}

ob_start();
phpinfo(INFO_MODULES);
$str = ob_get_clean();

$array = explode("\n", $str);
$array = preg_grep('/^igbinary session support.*yes/', $array);
if (!$array) {
	exit('skip igbinary session handler not available');
}
?>
--FILE--
<?php

$output = '';

function open($path, $name) {
	return true;
}

function close() {
	return true;
}

function read($id) {
    global $output;
    $output .= "read($id)\n";
	return '';
}

function write($id, $data) {
	global $output;
	$output .= "write($id): data:(" . bin2hex($data) . ")\n";
	return true;
}

function destroy($id) {
	return true;
}

function gc($time) {
	return true;
}

ini_set('session.serialize_handler', 'igbinary');

@session_set_save_handler('open', 'close', 'read', 'write', 'destroy', 'gc');
session_id('abcdef10231512dfaz_12311');

session_start();

// save an empty session
session_write_close();

// See https://github.com/igbinary/igbinary/issues/231
// - Redis expects a non-empty string to be serialized
// - When igbinary serializes data, many applications expect it to begin with \x00\x00\x00\x02.
// - \x14\x00 represents an array(0x14) of size 0 (0x00)
echo $output;
?>
--EXPECT--
read(abcdef10231512dfaz_12311)
write(abcdef10231512dfaz_12311): data:(000000021400)
