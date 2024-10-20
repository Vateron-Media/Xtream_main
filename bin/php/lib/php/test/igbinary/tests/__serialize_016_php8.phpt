--TEST--
__serialize() mechanism (016): Properties are still typed after unserialization (references)
--SKIPIF--
<?php
if (PHP_VERSION_ID < 80000) { echo "skip __serialize/__unserialize error message different in php < 8"; }
if (PHP_VERSION_ID >= 90000) { echo "skip requires php < 9.0 when testing that the deprecation has no impact on igbinary functionality\n"; }
?>
--FILE--
<?php
declare(strict_types=1);

// Asserts that this will emit a warning about dynamic properties due to the lack of #[AllowDynamicProperties] in PHP 8.2+, and suppresses the warning
if (PHP_VERSION_ID >= 80200) { require_once __DIR__ . '/php82_suppress_dynamic_properties_warning.inc'; }

/** @property mixed $std undeclared dynamic property */
class Test {
    public int $i = 0;
    public ?string $s = 's';
    public object $o;
    public stdClass $stdClass;
    public array $a = [];
}
$t = new Test();
$t->i = 1;
$t->s = 'other';
$t->o = $t;
$t->std = (object)['key' => 'value'];
$t->a = [&$t->std, &$t->i, &$t->s, &$t->o];
$t->std->key = &$t->a;
var_dump($t);

var_dump(bin2hex($s = igbinary_serialize($t)));
$t2 = igbinary_unserialize($s);
var_dump($t2);
try {
    $t2->i = 'x';
} catch (Error $e) {
    echo "i: " . $e->getMessage() . "\n";
}
$t2->s = null;
try {
    $t2->s = false;
} catch (Error $e) {
    // The error message is "Cannot assign false" in php 8.3+
    echo "s: " . str_replace('false', 'bool', $e->getMessage()) . "\n";
}
$t2->s = 'other';
try {
    $t2->o = null;
} catch (Error $e) {
    echo "o: " . $e->getMessage() . "\n";
}
try {
    $t2->a = null;
} catch (Error $e) {
    echo "a: " . $e->getMessage() . "\n";
}
try {
    $t2->stdClass = $t;
} catch (Error $e) {
    echo "stdClass: " . $e->getMessage() . "\n";
}
try {
    $t2->a = $t2;
} catch (Error $e) {
    echo "a: " . $e->getMessage() . "\n";
}
var_dump($t2);
?>
--EXPECT--
object(Test)#1 (5) {
  ["i"]=>
  &int(1)
  ["s"]=>
  &string(5) "other"
  ["o"]=>
  *RECURSION*
  ["stdClass"]=>
  uninitialized(stdClass)
  ["a"]=>
  &array(4) {
    [0]=>
    &object(stdClass)#2 (1) {
      ["key"]=>
      *RECURSION*
    }
    [1]=>
    &int(1)
    [2]=>
    &string(5) "other"
    [3]=>
    *RECURSION*
  }
  ["std"]=>
  &object(stdClass)#2 (1) {
    ["key"]=>
    &array(4) {
      [0]=>
      *RECURSION*
      [1]=>
      &int(1)
      [2]=>
      &string(5) "other"
      [3]=>
      *RECURSION*
    }
  }
}
string(176) "0000000217045465737414061101692506011101732511056f7468657211016f252200001101612514040600251708737464436c617373140111036b65792501030601250101060225010206032522001103737464252204"
object(Test)#3 (5) {
  ["i"]=>
  &int(1)
  ["s"]=>
  &string(5) "other"
  ["o"]=>
  *RECURSION*
  ["stdClass"]=>
  uninitialized(stdClass)
  ["a"]=>
  &array(4) {
    [0]=>
    &object(stdClass)#4 (1) {
      ["key"]=>
      *RECURSION*
    }
    [1]=>
    &int(1)
    [2]=>
    &string(5) "other"
    [3]=>
    *RECURSION*
  }
  ["std"]=>
  &object(stdClass)#4 (1) {
    ["key"]=>
    &array(4) {
      [0]=>
      *RECURSION*
      [1]=>
      &int(1)
      [2]=>
      &string(5) "other"
      [3]=>
      *RECURSION*
    }
  }
}
i: Cannot assign string to property Test::$i of type int
s: Cannot assign bool to property Test::$s of type ?string
o: Cannot assign null to property Test::$o of type object
a: Cannot assign null to property Test::$a of type array
stdClass: Cannot assign Test to property Test::$stdClass of type stdClass
a: Cannot assign Test to property Test::$a of type array
object(Test)#3 (5) {
  ["i"]=>
  &int(1)
  ["s"]=>
  &string(5) "other"
  ["o"]=>
  *RECURSION*
  ["stdClass"]=>
  uninitialized(stdClass)
  ["a"]=>
  &array(4) {
    [0]=>
    &object(stdClass)#4 (1) {
      ["key"]=>
      *RECURSION*
    }
    [1]=>
    &int(1)
    [2]=>
    &string(5) "other"
    [3]=>
    *RECURSION*
  }
  ["std"]=>
  &object(stdClass)#4 (1) {
    ["key"]=>
    &array(4) {
      [0]=>
      *RECURSION*
      [1]=>
      &int(1)
      [2]=>
      &string(5) "other"
      [3]=>
      *RECURSION*
    }
  }
}
