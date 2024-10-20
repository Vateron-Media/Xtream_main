--TEST--
Accessing unserialized numbers.
--SKIPIF--
<?php
if(!extension_loaded('igbinary')) {
	echo "skip no igbinary";
}
if (PHP_VERSION_ID < 70200) {
    echo "Skip php 7.2+ required";
}
?>
--FILE--
<?php

$data = (object)array(1,2,3, -1 => 'x', 1234 => 33);
var_dump($data);
$x = "1";
$y = 1;
$z = "1234";
$w = 1234;
var_dump(isset($data->{$x}) ? $data->{$x} : "unset");
error_reporting(0);
$str = igbinary_serialize($data);

$unserialized = igbinary_unserialize($str);
var_dump($unserialized);
var_dump(isset($unserialized->{$x}) ? $unserialized->{$x} : "unset str");
var_dump(isset($unserialized->{$y}) ? $unserialized->{$y} : "unset int");
var_dump(isset($unserialized->{$z}) ? $unserialized->{$z} : "unset str 1234");
var_dump(isset($unserialized->{$w}) ? $unserialized->{$w} : "unset int 1234");
var_dump(isset($unserialized->{-1}) ? $unserialized->{-1} : "unset int -1");
?>
--EXPECT--
object(stdClass)#1 (5) {
  ["0"]=>
  int(1)
  ["1"]=>
  int(2)
  ["2"]=>
  int(3)
  ["-1"]=>
  string(1) "x"
  ["1234"]=>
  int(33)
}
int(2)
object(stdClass)#2 (5) {
  ["0"]=>
  int(1)
  ["1"]=>
  int(2)
  ["2"]=>
  int(3)
  ["-1"]=>
  string(1) "x"
  ["1234"]=>
  int(33)
}
int(2)
int(2)
int(33)
int(33)
string(1) "x"
