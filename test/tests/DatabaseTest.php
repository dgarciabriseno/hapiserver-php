<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Database\Database;
use App\Database\DataRetrievalInterface;

final class DatabaseTest extends TestCase {
    public function testCreatesDatabase() {
        $db = Database::getInstance();
        $this->assertInstanceOf(DataRetrievalInterface::class, $db);
    }
}