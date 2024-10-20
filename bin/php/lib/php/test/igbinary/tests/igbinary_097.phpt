--TEST--
Test serialize globals
--SKIPIF--
<?php
if (!extension_loaded("igbinary")) print "skip\n";
if (PHP_VERSION_ID >= 80100) print "skip php >= 8.1\n"; // https://wiki.php.net/rfc/restrict_globals_usage
?>
--FILE--
<?php
call_user_func(function () {
    foreach ($GLOBALS as $key => $_) {
        if ($key !== 'GLOBALS') {
            unset($GLOBALS[$key]);
        }
    }
    $ser = igbinary_serialize($GLOBALS);
    echo urlencode($ser) . "\n";
    var_dump(igbinary_unserialize($ser));
    $GLOBALS['globalVar'] = new stdClass();
    $ser = igbinary_serialize($GLOBALS);
    echo urlencode($ser) . "\n";
    var_dump(igbinary_unserialize($ser));
});
--EXPECTF--
%00%00%00%02%14%01%11%07GLOBALS%14%01%0E%00%01%01
array(1) {
  ["GLOBALS"]=>
  array(1) {
    ["GLOBALS"]=>
    *RECURSION*
  }
}
%00%00%00%02%14%02%11%07GLOBALS%14%02%0E%00%01%01%11%09globalVar%17%08stdClass%14%00%0E%01%22%02
array(2) {
  ["GLOBALS"]=>
  array(2) {
    ["GLOBALS"]=>
    *RECURSION*
    ["globalVar"]=>
    object(stdClass)#3 (0) {
    }
  }
  ["globalVar"]=>
  object(stdClass)#3 (0) {
  }
}
