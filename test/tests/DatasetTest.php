<?php declare(strict_types=1);

use App\Util\Dataset;
use PHPUnit\Framework\TestCase;

final class DatasetTest extends TestCase {
    public function testGetDatasetParameters() {
        $dataset = new Dataset('ExampleDataset');
        $parameters = $dataset->GetParameters();
        $this->assertCount(5, $parameters);
    }

    public function testDatasetIsInCatalog() {
        $dataset = new Dataset('ExampleDataset');
        $this->assertTrue($dataset->IsInCatalog());

        $dataset = new Dataset('Bad dataset');
        $this->assertFalse($dataset->IsInCatalog());
    }

    public function testGetTimeParameter() {
        $dataset = new Dataset('ExampleDataset');
        $this->assertEquals("timestamp", $dataset->GetTimeParameter());
    }
}