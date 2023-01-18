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

    public function testCombinesDataAndStatusArray() {
        $response = new HapiResponse(1200, "Good job");
        $data = array(
            "custom" => "data",
            "exists" => "in",
            "this" => "array"
        );
        $result = $response->getDataArray($data);
        $this->assertEquals(1200, $result['status']['code']);
        $this->assertEquals("Good job", $result['status']['message']);
        $this->assertEquals("data", $result['custom']);
        $this->assertEquals("in", $result['exists']);
        $this->assertEquals("array", $result['this']);
    }

    public function testCombinesDataAndStatusJson() {
        $response = new HapiResponse(1200, "Good job");
        $data = array(
            "custom" => "data",
            "exists" => "in",
            "this" => "array"
        );
        $result = json_decode($response->getDataJson($data));
        $this->assertEquals(1200, $result->status->code);
        $this->assertEquals("Good job", $result->status->message);
        $this->assertEquals("data", $result->custom);
        $this->assertEquals("in", $result->exists);
        $this->assertEquals("array", $result->this);
    }
}