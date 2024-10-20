--TEST--
Test unserializing valid enums
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) { echo "skip enums requires php 8.1"; } ?>
--FILE--
<?php

enum Suit {
    case Hearts;
    case Diamonds;
    case Spades;
    case Clubs;
}
$ser = igbinary_serialize(Suit::Hearts);
echo urlencode($ser), "\n";
var_dump(igbinary_unserialize($ser));
var_dump(igbinary_unserialize($ser));
$serArray = igbinary_serialize([Suit::Hearts, Suit::Diamonds, Suit::Spades, Suit::Clubs, Suit::Clubs, 'Diamonds' => 'Diamonds']);
echo urlencode($serArray), "\n";
var_dump(igbinary_unserialize($serArray));

?>
--EXPECT--
%00%00%00%02%17%04Suit%27%11%06Hearts
enum(Suit::Hearts)
enum(Suit::Hearts)
%00%00%00%02%14%06%06%00%17%04Suit%27%11%06Hearts%06%01%1A%00%27%11%08Diamonds%06%02%1A%00%27%11%06Spades%06%03%1A%00%27%11%05Clubs%06%04%22%04%0E%02%0E%02
array(6) {
  [0]=>
  enum(Suit::Hearts)
  [1]=>
  enum(Suit::Diamonds)
  [2]=>
  enum(Suit::Spades)
  [3]=>
  enum(Suit::Clubs)
  [4]=>
  enum(Suit::Clubs)
  ["Diamonds"]=>
  string(8) "Diamonds"
}
