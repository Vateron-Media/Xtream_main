--TEST--
Object test, unserialize_callback_func (PHP < 8.2)
--SKIPIF--
<?php if (PHP_VERSION_ID >= 80200) echo "skip requires php < 8.2\n"; ?>
--INI--
error_reporting=E_ALL
unserialize_callback_func=autoload
--FILE--
<?php
if(!extension_loaded('igbinary')) {
	dl('igbinary.' . PHP_SHLIB_SUFFIX);
}

function test($type, $variable, $test) {
	$serialized = pack('H*', $variable);
	$unserialized = igbinary_unserialize($serialized);

	echo $type, "\n";
	echo substr(bin2hex($serialized), 8), "\n";
	echo $test || $unserialized->b == 2 ? 'OK' : 'ERROR';
	echo "\n";
}

function autoload($classname) {
	echo "Autoloading $classname\n";
	if (!class_exists(Obj::class)) {
		class Obj {
			var $a;
			var $b;

			function __construct($a, $b) {
				$this->a = $a;
				$this->b = $b;
			}
		}
	}
}

test('autoload', '0000000217034f626a140211016106011101620602', false);
test('autoload', '0000000217034f626b140211016106011101620602', false);
ini_set('unserialize_callback_func', strtolower('Missing_autoload'));
test('missing_autoload', '0000000217034f626c140211016106011101620602', false);

/*
 * you can add regression tests for your extension here
 *
 * the output of your test code has to be equal to the
 * text in the --EXPECT-- section below for the tests
 * to pass, differences between the output and the
 * expected text are interpreted as failure
 *
 * see TESTING.md for further information on
 * writing regression tests
 */
?>
--EXPECTF--
Autoloading Obj
autoload
17034f626a140211016106011101620602
OK
Autoloading Obk

%s: igbinary_unserialize(): Function autoload() hasn't defined the class it was called for in %sigbinary_022.php on line 8
autoload
17034f626b140211016106011101620602

%s: test(): %s to load the class definition %Sin %sigbinary_022.php on line 12
ERROR

%s: igbinary_unserialize(): defined (missing_autoload) but not found in %sigbinary_022.php on line 8
missing_autoload
17034f626c140211016106011101620602

%s: test(): %s to load the class definition %Sin %sigbinary_022.php on line 12
ERROR
