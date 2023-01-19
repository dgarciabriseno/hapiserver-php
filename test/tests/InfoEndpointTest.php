<?php declare(strict_types=1);

use App\Endpoint\InfoEndpoint;
use App\Exception\UserInputException;
use PHPUnit\Framework\TestCase;

final class InfoEndpointTest extends TestCase {
    public function testGetRequestedDataset() {
        $_GET["dataset"] = "ExampleDataset";
        $info = new InfoEndpoint();
        $dataset = $info->GetRequestedDataset();
        $this->assertEquals("ExampleDataset", $dataset);
    }

    public function testGetsDatasetInfo() {
        $_GET["dataset"] = "ExampleDataset";
        $info = new InfoEndpoint();
        $data = $info->GetDatasetInfo();
        $this->assertIsArray($data);
        $this->assertArrayHasKey("sampleStartDate", $data);
        $this->assertArrayHasKey("sampleStopDate", $data);
        $this->assertArrayHasKey("maxRequestDuration", $data);
        $this->assertArrayHasKey("description", $data);
        $this->assertArrayHasKey("resourceURL", $data);
        $this->assertArrayHasKey("citation", $data);
        $this->assertArrayHasKey("contact", $data);
        $this->assertArrayHasKey("startDate", $data);
        $this->assertArrayHasKey("stopDate", $data);
        $this->assertEquals("2022-01-01T05:00:00.123Z", $data['startDate']);
        $this->assertEquals("2022-01-31T00:00:00.789Z", $data['stopDate']);
    }

    public function testThrowsExceptionForNonexistentDataset() {
        $_GET["dataset"] = "One that doesn't exist";
        $this->expectException(UserInputException::class);
        $info = new InfoEndpoint();
        $info->ValidateRequestedDataset();
    }

    public function testDoesNotThrowExceptionForRealDataset() {
        $_GET["dataset"] = "ExampleDataset";
        $info = new InfoEndpoint();
        $info->ValidateRequestedDataset();
    }
}