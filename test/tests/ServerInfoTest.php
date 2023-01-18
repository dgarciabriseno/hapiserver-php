<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Util\ServerInfo;

final class ServerInfoTest extends TestCase {
    public function testGetsServerInfoFromConfig() {
        $info = new ServerInfo();
        $data = $info->getArray();
        $this->assertArrayHasKey("id", $data);
        $this->assertArrayHasKey("title", $data);
        $this->assertArrayHasKey("contact", $data);
        $this->assertArrayNotHasKey("contactID", $data);
    }
}