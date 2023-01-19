<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Util\Catalog;

final class CatalogTest extends TestCase {
    public function testRetrievesTheCatalog() {
        $data = Catalog::getArray();
        $this->assertIsArray($data);
        $this->assertArrayHasKey("catalog", $data);
    }

    public function testValidatesDatasets() {
        $this->assertTrue(Catalog::hasDataset("ExampleDataset"));
        $this->assertFalse(Catalog::hasDataset("Nonexistent Dataset"));
    }
}