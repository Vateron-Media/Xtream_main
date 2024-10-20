--TEST--
igbinary and __PHP_INCOMPLETE_CLASS
--FILE--
<?php
// TODO: Remove temporary workaround for __PHP_Incomplete_Class missing #[AllowDynamicProperties]
if (PHP_VERSION_ID >= 80200) { require_once __DIR__ . '/php82_suppress_dynamic_properties_warning.inc'; }
class Test {}
function test_ser_unser($obj) {
    var_dump(bin2hex($s = igbinary_serialize($obj)));
    $s = str_replace('Test', 'Best', $s);
    $obj2 = igbinary_unserialize($s);
    var_dump($obj2);
    var_dump(bin2hex($s = igbinary_serialize($obj2)));
    var_dump(igbinary_unserialize($s));
}
test_ser_unser(new Test());
echo "Testing with properties\n";
$obj = new Test();
$obj->dynamicProp = 'value';
$obj->nullProp = null;
test_ser_unser($obj);
?>
--EXPECT--
string(24) "000000021704546573741400"
object(__PHP_Incomplete_Class)#2 (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(4) "Best"
}
string(24) "000000021704426573741400"
object(__PHP_Incomplete_Class)#3 (1) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(4) "Best"
}
Testing with properties
string(86) "000000021704546573741402110b64796e616d696350726f70110576616c756511086e756c6c50726f7000"
object(__PHP_Incomplete_Class)#1 (3) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(4) "Best"
  ["dynamicProp"]=>
  string(5) "value"
  ["nullProp"]=>
  NULL
}
string(86) "000000021704426573741402110b64796e616d696350726f70110576616c756511086e756c6c50726f7000"
object(__PHP_Incomplete_Class)#3 (3) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(4) "Best"
  ["dynamicProp"]=>
  string(5) "value"
  ["nullProp"]=>
  NULL
}