<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Response\HapiResponse;

final class HapiResponseTest extends TestCase {
    public function testReturnsStatusArrays() {
        $response = new HapiResponse(1400, "Bad Request");
        $result = $response->getStatusArray();
        $this->assertEquals(1400, $result['status']['code']);
        $this->assertEquals("Bad Request", $result['status']['message']);
    }

    public function testReturnsStatusJson() {
        $response = new HapiResponse(1405, "Bad Request Again");
        $json = $response->getStatusJson();
        $result = json_decode($json);
        $this->assertEquals(1405, $result->status->code);
        $this->assertEquals("Bad Request Again", $result->status->message);
    }
}