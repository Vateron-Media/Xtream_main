<?php

class LibTest extends PHPUnit\Framework\TestCase {
    protected $dbMock;
    protected $redisMock;

    public static function setUpBeforeClass(): void {
        // Ensure cache directory exists
        if (!file_exists(CACHE_TMP_PATH)) {
            mkdir(CACHE_TMP_PATH, 0777, true);
        }
    }

    protected function setUp(): void {
        // Create database mock
        $this->dbMock = $this->createMock(Database::class);
        ipTV_lib::$ipTV_db = $this->dbMock;

        // Create Redis mock
        $this->redisMock = $this->createMock(\Redis::class);

        // Setup test environment
        ipTV_lib::$settings = array(
            'default_timezone' => 'UTC',
            'segment_type' => 'ts',
            'seg_time' => '10',
            'seg_list_size' => '5',
            'seg_delete_threshold' => '20',
            'redis_port' => 6379,
            'redis_host' => 'localhost'
        );
    }

    protected function tearDown(): void {
        ipTV_lib::$redis = null;
        ipTV_lib::closeRedis();
    }

    public function testGetDiffTimezone() {
        // Test timezone difference calculation
        $result = ipTV_lib::getDiffTimezone('America/New_York');
        $this->assertIsInt($result);
        // Note: Exact value will vary based on daylight savings
        $this->assertTrue(abs($result) <= 86400); // Should be within 24 hours
    }

    public function testCalculateSegNumbers() {
        $result = ipTV_lib::calculateSegNumbers();

        $this->assertIsArray($result);
        $this->assertEquals("ts", $result["seg_type"]);
        $this->assertEquals(10, $result["seg_time"]);
        $this->assertEquals(5, $result["seg_list_size"]);
        $this->assertEquals(20, $result["seg_delete_threshold"]);
    }

    public function testGenerateString() {
        $result1 = ipTV_lib::generateString(10);
        $result2 = ipTV_lib::generateString(10);

        $this->assertIsString($result1);
        $this->assertEquals(10, strlen($result1));
        $this->assertNotEquals($result1, $result2); // Should be random
    }

    public function testConfirmIDs() {
        $validIDs = array(1, 2, 3);
        $mixedIDs = array(1, 'abc', '2', -1, 0);

        $result1 = ipTV_lib::confirmIDs($validIDs);
        $this->assertEquals($validIDs, $result1);

        $result2 = ipTV_lib::confirmIDs($mixedIDs);
        $this->assertEquals(array(1, 2), $result2);
    }

    public function testArrayValuesRecursive() {
        $input = array(
            'key1' => 'value1',
            'key2' => array(
                'nested1' => 'value2',
                'nested2' => array('deep' => 'value3')
            ),
            'key3' => 123
        );

        $result = ipTV_lib::arrayValuesRecursive($input);

        $this->assertEquals(
            array('value1', 'value2', 'value3', 123),
            $result
        );
    }
}