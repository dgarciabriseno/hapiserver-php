<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Util\Config;

final class ConfigTest extends TestCase {
    public function testCanReadIniFile() {
        $config = Config::getInstance();
        $id = $config->getWithDefault("server_id", null);
        $this->assertIsString($id);
    }

    public function testReturnsDefaultValues() {
        $config = Config::getInstance();
        $id = $config->getWithDefault("This key is not in the ini file", "default");
        $this->assertEquals("default", $id);
    }
}