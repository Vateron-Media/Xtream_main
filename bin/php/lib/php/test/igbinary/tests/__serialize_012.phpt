--TEST--
Test unserialization of classes derived from ArrayIterator
--SKIPIF--
<?php if (PHP_VERSION_ID < 70406) { echo "Skip requires php 7.4.6+"; } ?>
--FILE--
<?php
// based on bug45706.phpt from php-src
//
// NOTE: ArrayIterator::__debugInfo adds a fake private property that doesn't actually exist, which affects var_dump.
// This isn't a bug in the unserializer.
class Foo1 extends ArrayIterator
{
}
class Foo2 {
}
$x = array(new Foo1(),new Foo2);
$s = igbinary_serialize($x);
var_dump(igbinary_unserialize($s));
$s = str_replace("Foo", "Bar", $s);
$y = igbinary_unserialize($s);
var_dump($y);
--EXPECTF--
array(2) {
  [0]=>
  object(Foo1)#3 (1) {
    ["storage":"ArrayIterator":private]=>
    array(0) {
    }
  }
  [1]=>
  object(Foo2)#4 (0) {
  }
}
array(2) {
  [0]=>
  object(__PHP_Incomplete_Class)#4 (5) {
    ["__PHP_Incomplete_Class_Name"]=>
    string(4) "Bar1"
    ["0"]=>
    int(0)
    ["1"]=>
    array(0) {
    }
    ["2"]=>
    array(0) {
    }
    ["3"]=>
    NULL
  }
  [1]=>
  object(__PHP_Incomplete_Class)#3 (1) {
    ["__PHP_Incomplete_Class_Name"]=>
    string(4) "Bar2"
  }
}
