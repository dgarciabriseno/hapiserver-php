<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Util\Catalog;

final class CatalogTest extends TestCase {
    public function testRetrievesTheCatalog() {
        $catalog = new Catalog();
        $data = $catalog->getArray();
        $this->assertIsArray($data);
        $this->assertArrayHasKey("catalog", $data);
    }
}