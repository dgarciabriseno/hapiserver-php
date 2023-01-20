<?php declare(strict_types=1);

use App\Util\DatasetInfo;
use PHPUnit\Framework\TestCase;
use App\Util\DatasetInfoReader;

final class DatasetInfoReaderTest extends TestCase {
    public function testReadsMetadataFromConfigAsArray() {
        $reader = new DatasetInfoReader("ExampleDataset");
        $metadata = $reader->GetMetadata()->asArray();
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey("sampleStartDate", $metadata);
        $this->assertArrayHasKey("sampleStopDate", $metadata);
        $this->assertArrayHasKey("maxRequestDuration", $metadata);
        $this->assertArrayHasKey("description", $metadata);
        $this->assertArrayHasKey("resourceURL", $metadata);
        $this->assertArrayHasKey("citation", $metadata);
        $this->assertArrayHasKey("contact", $metadata);
    }

    public function testReadsMetadataFromConfig() {
        $reader = new DatasetInfoReader("ExampleDataset");
        $metadata = $reader->GetMetadata();
        $this->assertInstanceOf(DatasetInfo::class, $metadata);
    }
}