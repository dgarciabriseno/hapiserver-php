<?php declare(strict_types=1);

use App\Util\Dataset;
use PHPUnit\Framework\TestCase;

final class DatasetTest extends TestCase {
    public function testGetDatasetParameters() {
        $dataset = Dataset::fromName('ExampleDataset');
        $parameters = $dataset->GetParameters();
        $this->assertCount(5, $parameters);
    }

    public function testDatasetIsInCatalog() {
        $dataset = Dataset::fromName('ExampleDataset');
        $this->assertTrue($dataset->IsInCatalog());

        $dataset = Dataset::fromName('Bad dataset');
        $this->assertFalse($dataset->IsInCatalog());
    }

    public function testGetTimeParameter() {
        $dataset = Dataset::fromName('ExampleDataset');
        $this->assertEquals("timestamp", $dataset->GetTimeParameter());
    }

    public function testIsInDataset() {
        $dataset = Dataset::fromName('SubsetDataset');
        $this->assertTrue($dataset->IsSubset());
    }

    public function testGetParentDataset() {
        $dataset = Dataset::fromName('SubsetDataset');
        $this->assertEquals('ExampleDataset', $dataset->GetParentDataset()->GetName());
    }
}