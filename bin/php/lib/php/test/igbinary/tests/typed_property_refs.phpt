--TEST--
unserialize with references to typed properties shall skip the references or fail
--SKIPIF--
<?php if (PHP_VERSION_ID < 80000) die("skip requires php 8.0+"); ?>
--FILE--
<?php

class X {
    public $a;
    public $b;
}

class A {
    public int $a;
    public $b;
}

class B {
    public $a;
    public int $b;
}

class E {
    public $a;
    public int $b;
}

class C {
    public int $a;
    public string $b;
}

class D {
    public int $a;
    public float $b;
}
function create_ser($value): string {
    $v = new X();
    $v->a = $value;
    $v->b = &$v->a;
    $ser = igbinary_serialize($v);
    printf("for %s: %s\n", var_export($value, true), urlencode($ser));
    return $ser;
}
$serInt = create_ser(1);
$serNull = create_ser(null);
$serStr = create_ser('x');
var_dump(igbinary_unserialize(str_replace('X', 'A', $serInt)));
var_dump(igbinary_unserialize(str_replace('X', 'B', $serInt)));
var_dump(igbinary_unserialize(str_replace('X', 'E', $serInt)));

try {
    var_dump(igbinary_unserialize(str_replace('X', 'A', $serNull)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    var_dump(igbinary_unserialize(str_replace('X', 'B', $serNull)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    var_dump(igbinary_unserialize(str_replace('X', 'C', $serInt)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    var_dump(igbinary_unserialize(str_replace('X', 'C', $serStr)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
try {
    var_dump(igbinary_unserialize(str_replace('X', 'D', $serInt)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}

?>
--EXPECT--
for 1: %00%00%00%02%17%01X%14%02%11%01a%25%06%01%11%01b%25%01%01
for NULL: %00%00%00%02%17%01X%14%02%11%01a%25%00%11%01b%25%01%01
for 'x': %00%00%00%02%17%01X%14%02%11%01a%25%11%01x%11%01b%25%01%01
object(A)#1 (2) {
  ["a"]=>
  &int(1)
  ["b"]=>
  &int(1)
}
object(B)#1 (2) {
  ["a"]=>
  &int(1)
  ["b"]=>
  &int(1)
}
object(E)#1 (2) {
  ["a"]=>
  &int(1)
  ["b"]=>
  &int(1)
}
Cannot assign null to property A::$a of type int
Cannot assign null to property B::$b of type int
Cannot assign int to property C::$b of type string
Cannot assign string to property C::$a of type int
Reference with value of type int held by property D::$a of type int is not compatible with property D::$b of type float
