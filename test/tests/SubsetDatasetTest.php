<?php declare(strict_types=1);

use App\Util\Dataset;
use PHPUnit\Framework\TestCase;

final class SubsetDatasetTest extends TestCase {
    public function testGetDatasetParameters() {
        $dataset = Dataset::fromName('SubsetDataset');
        $parameters = $dataset->GetParameters();
        $this->assertCount(4, $parameters);
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
}