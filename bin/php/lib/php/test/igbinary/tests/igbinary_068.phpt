--TEST--
Test serializing and unserializing PHP_INT_MIN
--FILE--
<?php

if (!defined('PHP_INT_MIN')) {
	define('PHP_INT_MIN', ~PHP_INT_MAX);
}
var_export(PHP_INT_MAX === igbinary_unserialize(igbinary_serialize(PHP_INT_MAX)));
echo "\n";

var_export(PHP_INT_MIN === igbinary_unserialize(igbinary_serialize(PHP_INT_MIN)));
echo "\n";
var_export(PHP_INT_MIN+1 === igbinary_unserialize(igbinary_serialize(PHP_INT_MIN + 1)));
echo "\n";
?>
--EXPECT--
true
true
true
