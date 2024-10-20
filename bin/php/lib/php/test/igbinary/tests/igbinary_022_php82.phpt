--TEST--
Object test, unserialize_callback_func (PHP >= 8.2)
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) echo "skip requires php < 8.2\n"; ?>
--INI--
error_reporting=E_ALL
unserialize_callback_func=autoload
--FILE--
<?php
if(!extension_loaded('igbinary')) {
	dl('igbinary.' . PHP_SHLIB_SUFFIX);
}

function test(string $type, string $variable) {
	$serialized = pack('H*', $variable);
	try {
		$unserialized = igbinary_unserialize($serialized);
	} catch (Error $e) {
		echo $type, "\n";
		echo "Caught {$e->getMessage()}\n";
		return;
	}

	echo $type, "\n";
	echo substr(bin2hex($serialized), 8), "\n";
	var_dump($unserialized);
	// The read_property handler is set to incomplete_class_get_property,
	// making this always return null for __PHP_Incomplete_Class
	var_dump($unserialized->b);
	echo $unserialized->b == 2 ? 'OK' : 'ERROR';
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

test('autoload', '0000000217034f626a140211016106011101620602');  // "Obj"
test('autoload', '0000000217034f626b140211016106011101620602');  // "Obk" failing to autoload
ini_set('unserialize_callback_func', strtolower('Missing_autoload'));
test('missing_autoload', '0000000217034f626c140211016106011101620602');  // Obk" with missing autoload function

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
object(Obj)#1 (2) {
  ["a"]=>
  int(1)
  ["b"]=>
  int(2)
}
int(2)
OK
Autoloading Obk

Warning: igbinary_unserialize(): Function autoload() hasn't defined the class it was called for in %sigbinary_022_php82.php on line 9
autoload
17034f626b140211016106011101620602
object(__PHP_Incomplete_Class)#1 (3) {
  ["__PHP_Incomplete_Class_Name"]=>
  string(3) "Obk"
  ["a"]=>
  int(1)
  ["b"]=>
  int(2)
}

Warning: test(): The script tried to access a property on an incomplete object. Please ensure that the class definition "Obk" of the object you are trying to operate on was loaded _before_ unserialize() gets called or provide an autoloader to load the class definition in %sigbinary_022_php82.php on line 21
NULL

Warning: test(): The script tried to access a property on an incomplete object. Please ensure that the class definition "Obk" of the object you are trying to operate on was loaded _before_ unserialize() gets called or provide an autoloader to load the class definition in %sigbinary_022_php82.php on line 22
ERROR
missing_autoload
Caught Invalid callback missing_autoload, function "missing_autoload" not found or invalid function name
