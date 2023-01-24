<?php declare(strict_types=1);

use App\Database\PDODatabase;
use App\Exception\UnsafeDataException;
use App\Exception\UserInputException;
use PHPUnit\Framework\TestCase;

final class PDODatabaseTest extends TestCase {
    public function testGetsDatasetParameters() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $parameters = $db->GetParametersForDataset("ExampleDataset");
        $this->assertIsArray($parameters);
        $this->assertCount(5, $parameters);
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
        $this->assertEquals('2022-01-01T00:00:00.000Z', $startDate);
    }

    public function testCanGetStopDate() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $stopDate = $db->GetStopDate("ExampleDataset");
        $this->assertEquals('2022-01-31T00:00:00.789Z', $stopDate);
    }

    public function testCanQueryData() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $start = new DateTimeImmutable("2021-01-01T00:00:00Z");
        $stop = new DateTimeImmutable("2023-01-01T00:00:00Z");
        $data = $db->QueryData("ExampleDataset", array(), $start, $stop);
        $this->assertEquals(3, count($data));
        $this->assertCount(5, $data[0]);
    }

    public function testCanQueryMetaparametersExclusively() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $start = new DateTimeImmutable("2021-01-01T00:00:00Z");
        $stop = new DateTimeImmutable("2023-01-01T00:00:00Z");
        $data = $db->QueryData("ExampleDataset", array('example_metaparameter'), $start, $stop);
        $this->assertEquals(3, count($data));
        $this->assertCount(1, $data[0]);
    }

    public function testThrowsExceptionIfStopTimeIsBeforeStartOfDatasetTimeRange() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $start = new DateTimeImmutable("2020-01-01");
        $stop = new DateTimeImmutable("2020-02-02");
        $this->expectException(UserInputException::class);
        $db->ValidateDatasetDates("ExampleDataset", $start, $stop);
    }

    public function testThrowsExceptionIfStartTimeIsAfterEndOfDatasetTimeRange() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $start = new DateTimeImmutable("2025-01-01");
        $stop = new DateTimeImmutable("2025-02-02");
        $this->expectException(UserInputException::class);
        $db->ValidateDatasetDates("ExampleDataset", $start, $stop);
    }

    public function testThrowsExceptionForUnsafeColumnNames() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $start = new DateTimeImmutable("2021-01-01T00:00:00Z");
        $stop = new DateTimeImmutable("2023-01-01T00:00:00Z");
        $this->expectException(UserInputException::class);
        $db->QueryData("ExampleDataset", array("column_name' --"), $start, $stop);
    }

    public function testAbleToCountData() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        // Covers the entire test dataset
        $count = $db->QueryDataCount("ExampleDataset", new DateTimeImmutable('2020-01-01'), new DateTimeImmutable('2022-02-01'));
        $this->assertEquals(3, $count);

        // Covers part of the test dataset
        $count = $db->QueryDataCount("ExampleDataset", new DateTimeImmutable('2022-01-16'), new DateTimeImmutable('2022-02-01'));
        $this->assertEquals(1, $count);

    }

    public function testReadsTheWhitelist() {
        $db = new PDODatabase("App\Database\MySQLStatements");
        $whitelist = $db->GetColumnWhitelist("TestDataset");
        $this->assertCount(4, $whitelist);
        $this->assertFalse(in_array('id', $whitelist));
        $this->assertTrue(in_array('decimal_data', $whitelist));
        $this->assertTrue(in_array('float_data', $whitelist));
        $this->assertTrue(in_array('string_data', $whitelist));
        $this->assertTrue(in_array('timestamp', $whitelist));
    }
}