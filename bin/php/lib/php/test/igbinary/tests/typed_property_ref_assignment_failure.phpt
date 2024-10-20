--TEST--
Failure to assign ref to typed property
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) die("skip php 7.4+"); ?>
--FILE--
<?php

class Best {
    public $prop;
}
class Test {
    public int $prop;
}

$b = new Best();
$b->prop = new stdClass();
$b->prop->y = &$b->prop;
$ser = igbinary_serialize($b);
echo str_replace('%', '\\x', urlencode($ser)), "\n";

// Should reject stdClass reference
$ser2 = str_replace('Best', 'Test', $ser);
try {
    var_dump(igbinary_unserialize($ser2));
} catch (Error $e) {
    printf("Caught %s: %s\n", get_class($e), $e->getMessage());
}
?>
--EXPECTF--
\x00\x00\x00\x02\x17\x04Best\x14\x01\x11\x04prop\x25\x17\x08stdClass\x14\x01\x11\x01y\x25\x22\x01
Caught TypeError: %s property Test::$prop %s
