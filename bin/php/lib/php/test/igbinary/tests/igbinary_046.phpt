--TEST--
Correctly unserialize scalar refs.
--INI--
igbinary.compact_strings = On
--FILE--
<?php
$a = array("A");
$a[1] = &$a[0];
$a[2] = &$a[1];
$a[3] = &$a[2];

$ig_ser = igbinary_serialize($a);
echo bin2hex($ig_ser) . "\n";
$ig = igbinary_unserialize($ig_ser);
$f = &$ig[3];
$f = 'V';
var_dump($ig);
--EXPECT--
000000021404060025110141060125010106022501010603250101
array(4) {
  [0]=>
  &string(1) "V"
  [1]=>
  &string(1) "V"
  [2]=>
  &string(1) "V"
  [3]=>
  &string(1) "V"
}
