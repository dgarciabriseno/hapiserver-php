<?php declare(strict_types=1);

use App\Util\Dataset;
use PHPUnit\Framework\TestCase;

final class SubsetDatasetTest extends TestCase {
    public function testGetDatasetParameters() {
        $dataset = Dataset::fromName('SubsetDataset');
        $parameters = $dataset->GetParameters();
        $this->assertCount(5, $parameters);
    }

    public function testDatasetIsInCatalog() {
        $dataset = Dataset::fromName('SubsetDataset');
        $this->assertTrue($dataset->IsInCatalog());
    }

    public function testGetTimeParameter() {
        $dataset = Dataset::fromName('SubsetDataset');
        $this->assertEquals("timestamp", $dataset->GetTimeParameter());
    }

    public function testGetParentDataset() {
        $dataset = Dataset::fromName('SubsetDataset');
        $this->assertEquals('ExampleDataset', $dataset->GetParentDataset()->GetName());
    }

    public function testMetadataUsesCorrectDescription() {
        $dataset = Dataset::fromName('SubsetDataset');
        $metadata = $dataset->GetMetadata();
        $this->assertEquals('Subset of ExampleDataset used for testing', $metadata['description']);
    }

    public function testMetadataIsRetrievedCorrectly() {
        $dataset = Dataset::fromName('SubsetDataset');
        $metadata = $dataset->GetMetadata();
        $metadata['startDate'] = '2022-01-31T00:00:00.789Z';
        $metadata['stopDate'] = '2022-01-31T00:00:00.789Z';
    }

    public function testParametersHaveDescriptions() {
        $dataset = Dataset::fromName('SubsetDataset');
        $parameters = $dataset->GetParameters();
        var_dump($parameters);
    }
}