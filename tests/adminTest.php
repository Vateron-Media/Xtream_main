<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the overwriteData function.
 */
class OverwriteDataTest extends TestCase
{
    /**
     * Test that values are correctly overwritten.
     */
    public function testOverwriteData()
    {
        $data = ['name' => 'John', 'age' => 30, 'city' => 'New York'];
        $overwrite = ['age' => 35, 'city' => 'Los Angeles'];
        $expected = ['name' => 'John', 'age' => 35, 'city' => 'Los Angeles'];

        $this->assertEquals($expected, overwriteData($data, $overwrite));
    }

    /**
     * Test that keys not in the original array are ignored.
     */
    public function testSkipUnknownKeys()
    {
        $data = ['name' => 'Alice', 'age' => 25];
        $overwrite = ['age' => 26, 'country' => 'USA']; // 'country' is not in $data
        $expected = ['name' => 'Alice', 'age' => 26];

        $this->assertEquals($expected, overwriteData($data, $overwrite));
    }

    /**
     * Test that keys in the skip list are not modified.
     */
    public function testSkipCertainKeys()
    {
        $data = ['name' => 'Bob', 'age' => 40, 'city' => 'Berlin'];
        $overwrite = ['age' => 45, 'city' => 'Munich'];
        $skip = ['age']; // 'age' should not be overwritten
        $expected = ['name' => 'Bob', 'age' => 40, 'city' => 'Munich'];

        $this->assertEquals($expected, overwriteData($data, $overwrite, $skip));
    }

    /**
     * Test that empty overwrite values do not change existing null values.
     */
    public function testEmptyValueWithNull()
    {
        $data = ['name' => 'Charlie', 'age' => null];
        $overwrite = ['age' => '']; // Empty value should not replace null
        $expected = ['name' => 'Charlie', 'age' => null];

        $this->assertEquals($expected, overwriteData($data, $overwrite));
    }
}
