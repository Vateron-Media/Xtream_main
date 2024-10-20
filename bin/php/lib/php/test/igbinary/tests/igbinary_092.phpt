--TEST--
Object test, unserialize_callback_func
--INI--
error_reporting=E_ALL
--FILE--
<?php
if(!extension_loaded('igbinary')) {
	dl('igbinary.' . PHP_SHLIB_SUFFIX);
}

function test($type, $variable, $test) {
    try {
        $serialized = pack('H*', $variable);
        $unserialized = igbinary_unserialize($serialized);

        echo $type, "\n";
        echo substr(bin2hex($serialized), 8), "\n";
        echo $test || $unserialized->b == 2 ? 'OK' : 'ERROR';
        echo "\n";
    } catch (Throwable $e) {
        printf("Caught %s: %s\n", get_class($e), $e->getMessage());
    }
}

class MyUnserializer {
    public static function handleUnserialize(string $class) {
        throw new RuntimeException('handleUnserialize: Class not found: ' . $class);
    }
}

ini_set('unserialize_callback_func', strtoupper('MyUnserializer::handleUnserialize'));
test('throwing_autoload', '0000000217034f626a140211016106011101620602', false);
?>
--EXPECTF--
Caught RuntimeException: handleUnserialize: Class not found: Obj
