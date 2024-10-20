--TEST--
__serialize() mechanism (021): Temporary references in serialized arrays should be properly deduplicated
--FILE--
<?php

// This test will also pass before php 7.4 - it will just not call __serialize in older php versions.
// (not a good example, the formats are incompatible)
class Test {
    /** @var int */
    public $i;

    public function __construct(int $i) {
        $this->i = $i;
    }

    public function __serialize() {
        $j = $this->i + 0x7700;
        return [&$j, &$j];
    }

    public function __unserialize(array $data) {
        list($this->i) = $data;
    }
}

$values = [];
for ($i = 0; $i < 256; $i++) {
    $values[] = new Test($i);
}
$ser = igbinary_serialize($values);
$result = igbinary_unserialize($ser);

printf("Count=%d\n", count($values));
$j = 0;
foreach ($values as $i => $v) {
    if ($i !== $j) {
        echo "Unexpected key $i, expected $j\n";
    }
    if ($v->i !== $j) {
        echo "Unexpected value {$v->i}, expected $j\n";
    }
    $j++;
}
echo "Done\n";

?>
--EXPECT--
Count=256
Done
