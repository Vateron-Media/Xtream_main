--TEST--
Test unserializing valid and invalid enums
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) { echo "skip enums requires php 8.1"; } ?>
--FILE--
<?php

class ABCD {
}

enum Suit {
    case Hearts;
    case Diamonds;
    case Spades;
    case Clubs;
    const HEARTS = self::Hearts;
}
$arr = ['Hearts' => Suit::Hearts];
$arr[1] = &$arr['Hearts'];
$serArray = igbinary_serialize($arr);
// PHP 8.1 added support for %0 as a null byte in EXPECTF in https://github.com/php/php-src/pull/7069
// Igbinary's use case of urlencode on binary data is rare.
// So replace % with \x
echo str_replace(['\\', '%'], ['\\\\', '\x'], urlencode($serArray)), "\n";
$result = igbinary_unserialize($serArray);
var_dump($result);
$result[1] = 'new';
var_dump($result);
$serInvalid = str_replace('Hearts', 'HEARTS', $serArray);
var_dump(igbinary_unserialize($serInvalid));

$serInvalidConst = str_replace('Hearts', 'vvvvvv', $serArray);
var_dump(igbinary_unserialize($serInvalidConst));

$serMissingClass = str_replace('Suit', 'Club', $serArray);
var_dump(igbinary_unserialize($serMissingClass));

$serInvalidClass = str_replace('Suit', 'ABCD', $serArray);
var_dump(igbinary_unserialize($serInvalidClass));
?>
--EXPECTF--
\x00\x00\x00\x02\x14\x02\x11\x06Hearts\x25\x17\x04Suit\x27\x0E\x00\x06\x01\x25\x22\x01
array(2) {
  ["Hearts"]=>
  &enum(Suit::Hearts)
  [1]=>
  &enum(Suit::Hearts)
}
array(2) {
  ["Hearts"]=>
  &string(3) "new"
  [1]=>
  &string(3) "new"
}

Warning: igbinary_unserialize_object_enum_case: Suit::HEARTS is not an enum case in %s on line 25
NULL

Warning: igbinary_unserialize_object_enum_case: Undefined constant Suit::vvvvvv in %s on line 28
NULL

Warning: igbinary_unserialize_object_enum_case: Class 'Club' does not exist in %s on line 31
NULL

Warning: igbinary_unserialize_object_enum_case: Class 'ABCD' is not an enum in %s on line 34
NULL