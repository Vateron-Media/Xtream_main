--TEST--
Test PHP 8.2 deprecation of creation of dynamic properties
--SKIPIF--
<?php
if (!extension_loaded("igbinary")) print "skip\n";
if (PHP_VERSION_ID < 80200) print "skip php < 8.2\n";
?>
--FILE--
<?php
class C {
}

$c = new C();
$invalidValues = [
    "\x00\x00\x00\x02\x17\x01C\x14\x01\x11\x01a\x06\x08",
    "\x00\x00\x00\x02\x17\x01C\x14\x01\x11\x04\x00*\x00a\x06\x08",
];
foreach ($invalidValues as $invalid) {
    try {
        var_dump(igbinary_unserialize($invalid));
    } catch (Error $e) {
        printf("%s: %s\n", $e::class, $e->getMessage());
    }
}
--EXPECTF--
Deprecated: igbinary_unserialize(): Creation of dynamic property C::$a is deprecated in %sigbinary_099.php on line 12
object(C)#2 (1) {
  ["a"]=>
  int(8)
}

Deprecated: igbinary_unserialize(): Creation of dynamic property C::$a is deprecated in %sigbinary_099.php on line 12
object(C)#2 (1) {
  ["a":protected]=>
  int(8)
}
