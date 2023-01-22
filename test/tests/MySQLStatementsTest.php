<?php declare(strict_types=1);

use App\Database\MySQLStatements;
use PHPUnit\Framework\TestCase;

final class MySQLStatementsTest extends TestCase {
    public function testBuildsMetadatacolumnSql() {
        $stub = $this->createStub(PDO::class);
        $provider = new MySQLStatements($stub);
        $sql = $provider->GetMetacolumnSQL(array("metacolumn" => "column,names"));
        $this->assertEquals(", CONCAT_WS('~', column,names) as metacolumn", $sql);
    }

    public function testBuildsMultipleMetacolumns() {
        $stub = $this->createStub(PDO::class);
        $provider = new MySQLStatements($stub);
        $sql = $provider->GetMetacolumnSQL(array("metacolumn" => "column,names", "othercolumn" => 'boop'));
        $this->assertEquals(", CONCAT_WS('~', column,names) as metacolumn, CONCAT_WS('~', boop) as othercolumn", $sql);
    }

    public function testIgnoresMetacolumnsForNoMetaparameters() {
        $stub = $this->createStub(PDO::class);
        $provider = new MySQLStatements($stub);
        $sql = $provider->GetMetacolumnSQL(array());
        $this->assertEquals("", $sql);
    }
}