--TEST--
Overwriting a typed property reference
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) die("skip php 7.4+"); ?>
--FILE--
<?php

class Test {
    public ?object $prop;
    public $drop;
}
$orig = new Test();
$orig->prop = $orig;
$orig->drop = null;
$ser = igbinary_serialize($orig);
unset($orig);
gc_collect_cycles(); // also tests that igbinary properly marks unserialized data as a possible gc cycle root.

var_dump(igbinary_unserialize($ser));
gc_collect_cycles();

echo "Test overwrite reference group\n";
$ser2 = str_replace('drop', 'prop', $ser);
var_dump(igbinary_unserialize($ser2));

?>
--EXPECT--
object(Test)#1 (2) {
  ["prop"]=>
  *RECURSION*
  ["drop"]=>
  NULL
}
Test overwrite reference group
object(Test)#1 (2) {
  ["prop"]=>
  NULL
  ["drop"]=>
  NULL
}
