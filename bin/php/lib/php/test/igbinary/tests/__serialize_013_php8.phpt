--TEST--
__serialize() mechanism (013): Properties are still typed after unserialization (php8)
--SKIPIF--
<?php if (PHP_VERSION_ID < 80000) { echo "skip __serialize/__unserialize error message different in php < 8"; } ?>
--FILE--
<?php
declare(strict_types=1);

/** @property mixed $std */
#[AllowDynamicProperties]
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
$t->a = [$t->std];
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
  int(1)
  ["s"]=>
  string(5) "other"
  ["o"]=>
  *RECURSION*
  ["stdClass"]=>
  uninitialized(stdClass)
  ["a"]=>
  array(1) {
    [0]=>
    object(stdClass)#2 (1) {
      ["key"]=>
      string(5) "value"
    }
  }
  ["std"]=>
  object(stdClass)#2 (1) {
    ["key"]=>
    string(5) "value"
  }
}
string(142) "000000021704546573741406110169060111017311056f7468657211016f220000110161140106001708737464436c617373140111036b6579110576616c756511037374642202"
object(Test)#3 (5) {
  ["i"]=>
  int(1)
  ["s"]=>
  string(5) "other"
  ["o"]=>
  *RECURSION*
  ["stdClass"]=>
  uninitialized(stdClass)
  ["a"]=>
  array(1) {
    [0]=>
    object(stdClass)#4 (1) {
      ["key"]=>
      string(5) "value"
    }
  }
  ["std"]=>
  object(stdClass)#4 (1) {
    ["key"]=>
    string(5) "value"
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
  int(1)
  ["s"]=>
  string(5) "other"
  ["o"]=>
  *RECURSION*
  ["stdClass"]=>
  uninitialized(stdClass)
  ["a"]=>
  array(1) {
    [0]=>
    object(stdClass)#4 (1) {
      ["key"]=>
      string(5) "value"
    }
  }
  ["std"]=>
  object(stdClass)#4 (1) {
    ["key"]=>
    string(5) "value"
  }
}
