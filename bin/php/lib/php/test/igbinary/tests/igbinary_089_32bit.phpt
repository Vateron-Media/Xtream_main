--TEST--
Test unserializing invalid 64-bit string header on 32-bit platform
--INI--
display_errors=stderr
error_reporting=E_ALL
--CONFLICTS--
high_memory
--SKIPIF--
<?php
if (!extension_loaded("igbinary")) print "skip\n";
if (PHP_INT_SIZE > 4) { print "skip requires 32-bit\n"; }
?>
--FILE--
<?php
$ser_invalid = hex2bin('0000000213fa56ea002a');
var_dump(igbinary_unserialize($ser_invalid));

?>
--EXPECTF--
Warning: igbinary_unserialize_chararray: %s in %sigbinary_089_32bit.php on line 3
NULL