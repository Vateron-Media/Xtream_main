--TEST--
__serialize() mechanism (007): handle __unserialize throwing
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
        throw new RuntimeException($this->prop);
    }

    public function __destruct() {
        // should not be called
        echo "Called destruct prop=$this->prop\n";
    }
}

$test = new Test;
$test->prop = 'XX';
$test->prop2 = [$test];
// 00000002          - igbinary header
// 17 04 54657374    - object of class with name "Test"
//   14 02           - 2 properties
//     06 00         - uint8(0) =>
//       11 02 5858  -   'XX'
//     06 01         - uint8(1) =>
//       14 01       -   array(size=2)
//         06 00     - uint8(0) =>
//         22 00     - igbinary_type_objref8 (pointer to the first referenceable item, i.e. the instance of "Test"
var_dump(bin2hex($s = igbinary_serialize($test)));
try {
    var_dump(igbinary_unserialize($s));
} catch (RuntimeException $e) {
    echo "Caught: {$e->getMessage()}\n";
}
$test->prop = 'not from igbinary_unserialize';

?>
--EXPECT--
string(52) "0000000217045465737414020600110258580601140106002200"
Caught: XX
Called destruct prop=not from igbinary_unserialize
