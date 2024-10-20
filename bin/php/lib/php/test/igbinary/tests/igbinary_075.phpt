--TEST--
igbinary and not enough data for array
--FILE--
<?php
set_error_handler(function ($errno, $errstr) {
    echo "$errstr\n";
});
class X {}
var_dump(bin2hex($s = igbinary_serialize(new X())));
echo "One byte\n";
var_dump(igbinary_unserialize("\x00\x00\x00\x02\x17\x01\x58\x14"));
echo "Two byte\n";
var_dump(igbinary_unserialize("\x00\x00\x00\x02\x17\x01\x58\x15"));
igbinary_unserialize("\x00\x00\x00\x02\x17\x01\x58\x15\xff");
echo "Four byte\n";
var_dump(igbinary_unserialize("\x00\x00\x00\x02\x17\x01\x58\x16"));
igbinary_unserialize("\x00\x00\x00\x02\x17\x01\x58\x16\x00\x00\x01");
?>
--EXPECTF--
string(18) "000000021701581400"
One byte
igbinary_unserialize_object_properties: end-of-data
NULL
Two byte
igbinary_unserialize_object_properties: end-of-data
NULL
igbinary_unserialize_object_properties: end-of-data
Four byte
igbinary_unserialize_object_properties: end-of-data
NULL
igbinary_unserialize_object_properties: end-of-data