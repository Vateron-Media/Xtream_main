--TEST--
__serialize() mechanism (001): Basics
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

class Test {
    public $prop;
    public function __serialize() {
        return ["prop" => $this->prop];
    }
    public function __unserialize(array $data) {
        echo "In __unserialize\n";
        if (!$data['prop']) {
            throw new RuntimeException("bad prop");
        }
    }
    public function __destruct() {
        echo "In __destruct\n";
    }
}

$test = new Test;
$test->prop = new Test();
var_dump(bin2hex($s = igbinary_serialize($test)));
try {
    igbinary_unserialize($s);
} catch (RuntimeException $e) {
    echo "Caught {$e->getMessage()}\n";
}

?>
--EXPECT--
string(50) "000000021704546573741401110470726f701a0014010e0100"
In __unserialize
Caught bad prop
In __destruct
In __destruct