--TEST--
igbinary with hash collision serializing strings
--FILE--
<?php
$var=['id'=>"3010480803", 'user_id'=>12346];
$serialized = igbinary_serialize($var);
$unserialized = igbinary_unserialize($serialized);
var_dump($unserialized);
$var=['id'=>"3010480803", 'user_id'=>"3010480804"];
$serialized = igbinary_serialize($var);
$unserialized = igbinary_unserialize($serialized);
var_dump($unserialized);
?>
--EXPECT--
array(2) {
  ["id"]=>
  string(10) "3010480803"
  ["user_id"]=>
  int(12346)
}
array(2) {
  ["id"]=>
  string(10) "3010480803"
  ["user_id"]=>
  string(10) "3010480804"
}
