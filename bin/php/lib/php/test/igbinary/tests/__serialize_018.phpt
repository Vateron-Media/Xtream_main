--TEST--
__serialize() freed on unserialize exception without calling destructor.
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--INI--
; Note that php 8.1 deprecates using Serializable without __serialize/__unserialize but we are testing Serialize for igbinary. Suppress deprecations.
error_reporting=E_ALL & ~E_DEPRECATED
--FILE--
<?php
class ThrowsInUnserialize implements Serializable {
    public function serialize() {
        return "test data";
    }

    public function unserialize($ser) {
        echo "Unserializing $ser\n";
        throw new Error($ser);
    }

    public function __destruct() {
        echo "In __destruct ThrowsInUnserialize\n";
    }
}

class Test {
    public $prop;
    public $prop2;
    public function __serialize() {
        return [0 => $this->prop, "value" => $this->prop2];
    }
    public function __unserialize(array $data) {
        echo "In __unserialize Test\n";
    }

    public function __destruct() {
        echo "In __destruct Test\n";
    }
}

$test = new Test;
$test->prop = new ThrowsInUnserialize();
$test->prop2 = "barfoo";
var_dump(bin2hex($s = igbinary_serialize($test)));
try {
    var_dump(igbinary_unserialize($s));
} catch (Error $e) {
    echo "Caught: {$e->getMessage()}\n";
}
gc_collect_cycles();
echo "After gc_collect_cycles\n";
unset($test);
gc_collect_cycles();

echo "And for serialize/unserialize\n";
$test = new Test;
$test->prop = new ThrowsInUnserialize();
$test->prop2 = "barfoo";

var_dump(bin2hex($s = serialize($test)));
try {
    var_dump(unserialize($s));
} catch (Error $e) {
    echo "Caught: {$e->getMessage()}\n";
}
?>
--EXPECT--
string(122) "000000021704546573741402060017135468726f7773496e556e73657269616c697a651d09746573742064617461110576616c75651106626172666f6f"
Unserializing test data
In __destruct ThrowsInUnserialize
Caught: test data
After gc_collect_cycles
In __destruct Test
In __destruct ThrowsInUnserialize
And for serialize/unserialize
string(168) "4f3a343a2254657374223a323a7b693a303b433a31393a225468726f7773496e556e73657269616c697a65223a393a7b7465737420646174617d733a353a2276616c7565223b733a363a22626172666f6f223b7d"
Unserializing test data
In __destruct ThrowsInUnserialize
Caught: test data
In __destruct Test
In __destruct ThrowsInUnserialize