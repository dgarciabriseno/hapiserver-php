<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Util\DatasetMetadataReader;

final class DatasetMetadataReaderTest extends TestCase {
    public function testReadsMetadataFromConfig() {
        $reader = new DatasetMetadataReader("ExampleDataset");
        $metadata = $reader->GetMetadata();
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey("sampleStartDate", $metadata);
        $this->assertArrayHasKey("sampleStopDate", $metadata);
        $this->assertArrayHasKey("maxRequestDuration", $metadata);
        $this->assertArrayHasKey("description", $metadata);
        $this->assertArrayHasKey("resourceURL", $metadata);
        $this->assertArrayHasKey("citation", $metadata);
        $this->assertArrayHasKey("contact", $metadata);
    }
}