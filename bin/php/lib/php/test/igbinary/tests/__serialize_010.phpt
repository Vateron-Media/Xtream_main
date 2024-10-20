--TEST--
__serialize() mechanism (010): handle references in array returned by __serialize
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

class Test {
    public $prop;
    public $prop2;
    public function __serialize() {
        return [$this->prop, $this->prop2];
    }
    public function __unserialize(array $data) {
        $this->prop = $data[0];
        $this->prop2 = $data[1];
        if (!$this->prop) {
            throw new RuntimeException("Threw from __unserialize");
        }
    }

    public function __destruct() {
        // should not be called
        echo "Called destruct\n";
    }
}

$test = new Test;
$test->prop = &$test;
$test->prop2 = [&$test];
var_dump(bin2hex($s = igbinary_serialize($test)));
var_dump(igbinary_unserialize($s));
unset($test->prop);
$test->prop = false;
var_dump(bin2hex($s = igbinary_serialize($test)));
try {
    igbinary_unserialize($s);
} catch (RuntimeException $e) {
    echo "message={$e->getMessage()}\n";

}
echo "Calling gc_collect_cycles\n";
gc_collect_cycles();
echo "After call to gc_collect_cycles\n";

?>
--EXPECT--
string(50) "00000002170454657374140206002200060114010600252200"
object(Test)#2 (2) {
  ["prop"]=>
  *RECURSION*
  ["prop2"]=>
  array(1) {
    [0]=>
    *RECURSION*
  }
}
string(48) "000000021704546573741402060004060114010600252200"
message=Threw from __unserialize
Calling gc_collect_cycles
Called destruct
After call to gc_collect_cycles
Called destruct