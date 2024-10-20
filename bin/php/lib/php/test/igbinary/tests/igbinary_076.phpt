--TEST--
igbinary and edge cases unserializing array keys
--FILE--
<?php
set_error_handler(function ($errno, $errstr) {
    echo "$errstr\n";
});
var_dump(bin2hex($s = igbinary_serialize(['key' => true])));
// 3-byte string truncated in the middle of the array key
var_dump(igbinary_unserialize("\x00\x00\x00\x02\x14\x01\x11\x03\x6b\x65"));
// null instead of a string - skip over the entry
var_dump(igbinary_unserialize("\x00\x00\x00\x02\x14\x01\x00"));
?>
--EXPECTF--
string(24) "00000002140111036b657905"
igbinary_unserialize_chararray: end-of-data
NULL
array(0) {
}
