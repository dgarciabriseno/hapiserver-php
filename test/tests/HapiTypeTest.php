<?php declare(strict_types=1);

use App\Exception\UnimplementedException;
use App\Util\HapiType;
use PHPUnit\Framework\TestCase;

final class HapiTypeTest extends TestCase {
    public function testFloatToDoubleMapping() {
        $type = HapiType::GetTypeFor("float");
        $this->assertEquals("double", $type);
    }

    public function testThrowsExceptionForUnknownType() {
        $this->expectException(UnimplementedException::class);
        HapiType::GetTypeFor("random type that doesn't exist");
    }
}