--TEST--
Check for handling of IS_INDIRECT in arrays
--FILE--
<?php
$globalVar = 123;
$otherGlobalVar = &$globalVar;

call_user_func(function () {
    $x = $GLOBALS;
    foreach ($x as $key => $value) {
        if (!in_array($key, ['globalVar', 'otherGlobalVar'])) {
            unset($x[$key]);
        }
    }
    var_dump($x);
    $ser = igbinary_serialize($x);
    echo urlencode($ser) . "\n";
    var_dump(igbinary_unserialize($ser));

});
--EXPECT--
array(2) {
  ["globalVar"]=>
  &int(123)
  ["otherGlobalVar"]=>
  &int(123)
}
%00%00%00%02%14%02%11%09globalVar%25%06%7B%11%0EotherGlobalVar%25%01%01
array(2) {
  ["globalVar"]=>
  &int(123)
  ["otherGlobalVar"]=>
  &int(123)
}
