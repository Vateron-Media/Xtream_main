--TEST--
__serialize() mechanism (002): TypeError on invalid return type
--SKIPIF--
<?php if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with serialize()"; } ?>
--FILE--
<?php

class Test {
    public function __serialize() {
        return $this;
    }
}

try {
    igbinary_serialize(new Test);
} catch (TypeError $e) {
    echo $e->getMessage(), "\n";
}

?>
--EXPECT--
Test::__serialize() must return an array
