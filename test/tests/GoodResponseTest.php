<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Response\GoodResponse;

final class GoodResponseTest extends TestCase {
    public function testGoodResponseIsGood() {
        $response = new GoodResponse();
        $status = $response->getStatusArray();
        $this->assertEquals(1200, $status['status']['code']);
        $this->assertEquals("OK", $status['status']['message']);
    }
}