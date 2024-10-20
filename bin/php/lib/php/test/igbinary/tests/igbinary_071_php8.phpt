--TEST--
igbinary_unserialize with references to typed properties shall skip the references or fail
--SKIPIF--
<?php if (PHP_VERSION_ID < 80000) { echo "skip __serialize/__unserialize error message different in php < 8"; } ?>
--FILE--
<?php

class A {
	public int $a;
	public $b;
}

class B {
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

class Z {
	public $a;
	public $b;
}
$a = new A();
$a->a = 1234;
$a->b = &$a->a;
var_dump(bin2hex($s = igbinary_serialize($a)));
var_dump(igbinary_unserialize($s));
echo "Test B\n";
$b = new B();
$b->a = -1234;
$b->b = &$b->a;
var_dump(bin2hex($s = igbinary_serialize($b)));
var_dump(igbinary_unserialize($s));

$z = new Z();
$z->a = null;
$z->b = &$z->a;
$s = igbinary_serialize($z);
try {
    var_dump(igbinary_unserialize(str_replace('Z', 'A', $s)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}

try {
    var_dump(igbinary_unserialize(str_replace('Z', 'B', $s)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$z = new Z();
$z->a = 1;
$z->b = &$z->a;
$s = igbinary_serialize($z);
try {
    var_dump(igbinary_unserialize(str_replace('Z', 'C', $s)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$z = new Z();
$z->a = 'x';
$z->b = &$z->a;
$s = igbinary_serialize($z);
try {
    var_dump(igbinary_unserialize(str_replace('Z', 'C', $s)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
$z = new Z();
$z->a = 1;
$z->b = &$z->a;
$s = igbinary_serialize($z);
try {
    var_dump(igbinary_unserialize(str_replace('Z', 'D', $s)));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
/*
try {
    var_dump(unserialize('O:1:"D":2:{s:1:"a";i:1;s:1:"b";R:2;}'));
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}
 */

?>
--EXPECT--
string(44) "000000021701411402110161250804d2110162250101"
object(A)#2 (2) {
  ["a"]=>
  &int(1234)
  ["b"]=>
  &int(1234)
}
Test B
string(44) "000000021701421402110161250904d2110162250101"
object(B)#3 (2) {
  ["a"]=>
  &int(-1234)
  ["b"]=>
  &int(-1234)
}
Cannot assign null to property A::$a of type int
Cannot assign null to property B::$b of type int
Cannot assign int to property C::$b of type string
Cannot assign string to property C::$a of type int
Reference with value of type int held by property D::$a of type int is not compatible with property D::$b of type float