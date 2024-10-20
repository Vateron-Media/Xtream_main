--TEST--
Overwriting a typed property that is not yet a reference
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) die("skip php 7.4+"); ?>
--FILE--
<?php

class Test {
    public $drop;
    public ?Test $prop;
}
$orig = new Test();
$orig->drop = null;
$orig->prop = new Test();
$orig->prop->prop = &$orig->prop;
$ser = igbinary_serialize($orig);
echo urlencode($ser), "\n";

$ser2 = str_replace('drop', 'prop', $ser);
$result = igbinary_unserialize($ser2);
var_dump($result);
try {
    $result->prop = 1;
} catch (TypeError $e) {
    printf("Caught %s\n", get_class($e));
}

?>
--EXPECT--
%00%00%00%02%17%04Test%14%02%11%04drop%00%11%04prop%25%1A%00%14%02%0E%01%00%0E%02%25%22%01
object(Test)#3 (2) {
  ["drop"]=>
  NULL
  ["prop"]=>
  &object(Test)#4 (2) {
    ["drop"]=>
    NULL
    ["prop"]=>
    *RECURSION*
  }
}
Caught TypeError
