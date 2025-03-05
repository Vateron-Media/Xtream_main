<?php

use PHPUnit\Framework\TestCase;

class GetStreamingURLTest extends TestCase {
    private $mockIPTVLib;

    // Метод, который вызывается перед каждым тестом
    protected function setUp(): void {
        // Создаём мок для CoreUtilities, чтобы избежать зависимости от настоящих данных
        $this->mockIPTVLib = $this->getMockBuilder('CoreUtilities')
            ->disableOriginalConstructor()  // Не вызываем конструктор
            ->getMock();

        // Подменяем SERVER_ID для тестов, если он не определён
        if (!defined('SERVER_ID')) {
            define('SERVER_ID', 1);
        }
        // Подменяем HOST для тестов
        if (!defined('HOST')) {
            define('HOST', 'example.com');
        }
    }

    // Тестируем случай, когда передан serverID и rForceHTTP == false
    public function testGetStreamingURLWithServerIDAndForceHTTPFalse() {
        // Подготавливаем тестовые данные для серверов
        $serverID = 1;
        $servers = [
            $serverID => [
                'server_protocol' => 'https',  // Протокол
                'https_broadcast_port' => 443, // Порт для https
                'domains' => ['urls' => ['example.com']], // Список доменов
                'random_ip' => false,  // Не используем случайный IP
                'https_url' => 'https://backup.example.com' // Резервный URL
            ]
        ];

        // Мокаем статическое свойство Servers
        $this->mockStaticProperty('CoreUtilities', 'Servers', $servers);

        // Ожидаем, что вернётся правильный URL с протоколом https
        $result = ipTV_streaming::getStreamingURL($serverID, false);
        $this->assertEquals('https://example.com:443', $result);
    }

    // Тестируем случай, когда передан serverID и rForceHTTP == true
    // public function testGetStreamingURLWithServerIDAndForceHTTPTrue() {;
    //     $serverID = 2;
    //     $servers = [;
    //         $serverID => [;
    //             'server_protocol' => 'https',  // Протокол;
    //             'http_broadcast_port' => 80,   // Порт для http;
    //             'domains' => ['urls' => ['example.net']], // Список доменов;
    //             'random_ip' => false,  // Не используем случайный IP;
    //             'http_url' => 'http://backup.example.net' // Резервный URL;
    //         ];
    //     ];

    //     // Мокаем статическое свойство Servers;
    //     $this->mockStaticProperty('CoreUtilities', 'Servers', $servers);

    //     // Ожидаем, что вернётся URL с протоколом http;
    //     $result = ipTV_streaming::getStreamingURL($serverID, true);
    //     $this->assertEquals('http://example.net:80', $result);
    // };

    // Тестируем случай, когда serverID не передан, должен использоваться SERVER_ID
    public function testGetStreamingURLWithoutServerID() {
        // Подготавливаем данные для серверов
        $servers = [
            SERVER_ID => [
                'server_protocol' => 'https',
                'https_broadcast_port' => 443,
                'domains' => ['urls' => ['example.com']],
                'random_ip' => false,
                'https_url' => 'https://backup.example.com'
            ]
        ];

        // Мокаем статическое свойство Servers
        $this->mockStaticProperty('CoreUtilities', 'Servers', $servers);

        // Ожидаем, что будет использован SERVER_ID
        $result = ipTV_streaming::getStreamingURL(null, false);
        $this->assertEquals('https://example.com:443', $result);
    }

    // Тестируем случай, когда используется случайный IP из списка доменов
    public function testGetStreamingURLWithRandomIP() {
        $serverID = 3;
        $servers = [
            $serverID => [
                'server_protocol' => 'https',
                'https_broadcast_port' => 443,
                'domains' => ['urls' => ['random1.com', 'random2.com']],
                'random_ip' => true,  // Используем случайный домен
                'https_url' => 'https://backup.example.com'
            ]
        ];

        // Мокаем статическое свойство Servers
        $this->mockStaticProperty('CoreUtilities', 'Servers', $servers);

        // Ожидаем, что URL будет случайным из списка доменов
        $result = ipTV_streaming::getStreamingURL($serverID, false);
        $this->assertContains($result, [
            'https://random1.com:443',
            'https://random2.com:443'
        ]);
    }

    // Тестируем случай, когда доменов нет и используется fallback URL
    public function testGetStreamingURLFallbackToURL() {
        $serverID = 4;
        $servers = [
            $serverID => [
                'server_protocol' => 'https',
                'https_broadcast_port' => 443,
                'domains' => ['urls' => []], // Нет доменов
                'random_ip' => false,
                'https_url' => 'https://fallback.example.com' // Резервный URL
            ]
        ];

        // Мокаем статическое свойство Servers
        $this->mockStaticProperty('CoreUtilities', 'Servers', $servers);

        // Ожидаем, что будет использован fallback URL
        $result = ipTV_streaming::getStreamingURL($serverID, false);
        $this->assertEquals('https://fallback.example.com', $result);
    }

    /**
     * Вспомогательный метод для мока статического свойства класса
     * Позволяет изменять значение статического свойства
     */
    private function mockStaticProperty($className, $propertyName, $value) {
        // Получаем рефлексию класса для доступа к приватным/защищённым свойствам
        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true); // Делаем свойство доступным для изменения
        $property->setValue(null, $value); // Устанавливаем новое значение
    }
}

class GetBouquetMapTest extends TestCase {
    private $testFilePath;

    protected function setUp(): void {
        // Создаем временный путь для тестового файла
        $this->testFilePath = __DIR__ . '/bouquet_map';

        // Определяем константу, если она не задана
        if (!defined('CACHE_TMP_PATH')) {
            define('CACHE_TMP_PATH', __DIR__ . '/');
        }
    }

    protected function tearDown(): void {
        // Удаляем тестовый файл после тестов
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testGetBouquetMapWhenFileExistsAndStreamIDFound() {
        // Создаем тестовый массив и сериализуем его с igbinary
        $testData = [
            101 => ['bouquet1', 'bouquet2'],
            102 => ['bouquet3']
        ];
        file_put_contents($this->testFilePath, igbinary_serialize($testData));

        // Проверяем, что для streamID=101 возвращается ['bouquet1', 'bouquet2']
        $result = ipTV_streaming::getBouquetMap(101);
        $this->assertEquals(['bouquet1', 'bouquet2'], $result);
    }

    public function testGetBouquetMapWhenFileExistsButStreamIDNotFound() {
        // Файл существует, но streamID 200 нет в данных
        $testData = [
            101 => ['bouquet1', 'bouquet2']
        ];
        file_put_contents($this->testFilePath, igbinary_serialize($testData));

        // Должен вернуться пустой массив
        $result = ipTV_streaming::getBouquetMap(200);
        $this->assertEquals([], $result);
    }

    public function testGetBouquetMapWhenFileDoesNotExist() {
        // Проверяем, что если файла нет, возвращается пустой массив
        $result = ipTV_streaming::getBouquetMap(101);
        $this->assertEquals([], $result);
    }
}

class MatchCIDRTest extends TestCase {
    private $testFilePath;

    protected function setUp(): void {
        // Создаем временный путь для тестового файла
        $this->testFilePath = __DIR__ . '/cidr_test.json';

        // Определяем константу, если она не задана
        if (!defined('CIDR_TMP_PATH')) {
            define('CIDR_TMP_PATH', __DIR__ . '/');
        }
    }

    protected function tearDown(): void {
        // Удаляем тестовый файл после выполнения тестов
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testMatchCIDRWhenFileExistsAndIPInRange() {
        // Подготовка тестового JSON-файла с диапазоном IP
        $testData = json_encode([
            "192.168.1.0/24" => ["range", "192.168.1.0", "192.168.1.255"]
        ]);
        file_put_contents($this->testFilePath, $testData);

        // Проверяем, что IP в диапазоне найден
        $result = ipTV_streaming::matchCIDR('cidr_test.json', '192.168.1.100');
        $this->assertEquals(["range", "192.168.1.0", "192.168.1.255"], $result);
    }

    public function testMatchCIDRWhenFileDoesNotExist() {
        // Проверяем, что если файла нет, возвращается null
        $result = ipTV_streaming::matchCIDR('non_existent.json', '192.168.1.100');
        $this->assertNull($result);
    }

    public function testMatchCIDRWhenIPNotInRange() {
        // Файл с диапазоном, но IP не входит в него
        $testData = json_encode([
            "192.168.1.0/24" => ["range", "192.168.1.0", "192.168.1.255"]
        ]);
        file_put_contents($this->testFilePath, $testData);

        // Проверяем, что IP за пределами диапазона возвращает null
        $result = ipTV_streaming::matchCIDR('cidr_test.json', '192.168.2.50');
        $this->assertNull($result);
    }

    public function testMatchCIDRWithMultipleRanges() {
        // Файл с несколькими диапазонами
        $testData = json_encode([
            "192.168.1.0/24" => ["range1", "192.168.1.0", "192.168.1.255"],
            "192.168.2.0/24" => ["range2", "192.168.2.0", "192.168.2.255"]
        ]);
        file_put_contents($this->testFilePath, $testData);

        // Проверяем, что правильный диапазон возвращается
        $result = ipTV_streaming::matchCIDR('cidr_test.json', '192.168.2.50');
        $this->assertEquals(["range2", "192.168.2.0", "192.168.2.255"], $result);
    }
}

class CheckISPTest extends TestCase {
    protected function setUp(): void {
        // Моделируем список заблокированных ISP
        CoreUtilities::$blockedISP = [
            ['isp' => 'BlockedISP1', 'blocked' => 1],
            ['isp' => 'BlockedISP2', 'blocked' => 1],
            ['isp' => 'AllowedISP', 'blocked' => 0],
        ];
    }

    public function testBlockedISP() {
        // Провайдер есть в списке и заблокирован
        $this->assertEquals(1, ipTV_streaming::checkISP('BlockedISP1'));
        $this->assertEquals(1, ipTV_streaming::checkISP('BlockedISP2'));
    }

    public function testAllowedISP() {
        // Провайдер есть в списке, но не заблокирован
        $this->assertEquals(0, ipTV_streaming::checkISP('AllowedISP'));
    }

    public function testUnknownISP() {
        // Провайдер отсутствует в списке
        $this->assertEquals(0, ipTV_streaming::checkISP('UnknownISP'));
    }

    public function testCaseInsensitiveCheck() {
        // Проверка на регистронезависимость
        $this->assertEquals(1, ipTV_streaming::checkISP('blockedisp1'));
        $this->assertEquals(1, ipTV_streaming::checkISP('bLoCkEdIsP2'));
        $this->assertEquals(0, ipTV_streaming::checkISP('aLlOwEdIsP'));
    }
}
