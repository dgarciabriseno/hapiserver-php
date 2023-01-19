<?php declare(strict_types=1);

use App\Database\PDODatabase;
use PHPUnit\Framework\TestCase;

final class PDODatabaseTest extends TestCase {
    public function testGetColumnNames() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $names = $db->GetParametersForDataset("data");
        $this->assertIsArray($names);
    }

    public function testGetsDatasetMetadata() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $metadata = $db->GetDatasetMetadata("ExampleDataset");
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey("sampleStartDate", $metadata);
        $this->assertArrayHasKey("sampleStopDate", $metadata);
        $this->assertArrayHasKey("maxRequestDuration", $metadata);
        $this->assertArrayHasKey("description", $metadata);
        $this->assertArrayHasKey("resourceURL", $metadata);
        $this->assertArrayHasKey("citation", $metadata);
        $this->assertArrayHasKey("contact", $metadata);
        $this->assertArrayHasKey("startDate", $metadata);
        $this->assertArrayHasKey("stopDate", $metadata);
    }

    public function testCanGetStartDate() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $startDate = $db->GetStartDate("ExampleDataset");
        $this->assertEquals('2022-01-01T05:00:00.123Z', $startDate);
    }

    public function testCanGetStopDate() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $stopDate = $db->GetStopDate("ExampleDataset");
        $this->assertEquals('2022-01-31T00:00:00.789Z', $stopDate);
    }
}