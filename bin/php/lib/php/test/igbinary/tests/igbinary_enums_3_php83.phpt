--TEST--
Test unserializing valid enums inferring value
--SKIPIF--
<?php if (PHP_VERSION_ID < 80300) { echo "skip non-compile time constant in enums requires php 8.3"; } ?>
--FILE--
<?php

enum X: string {
    case X = X;
}
$x = 'X';
define('X', "dynamic$x");
$value = urldecode('%00%00%00%02%17%01X%27%0E%00');
var_dump(igbinary_unserialize($value));
$ser = igbinary_serialize(X::X);
echo urlencode($ser), "\n";
var_dump(X::X->value);
?>
--EXPECT--
enum(X::X)
%00%00%00%02%17%01X%27%0E%00
string(8) "dynamicX"
