<?php declare(strict_types=1);

use App\Util\DatasetInfoReader;
use PHPUnit\Framework\TestCase;

final class DatasetInfoTest extends TestCase {
    public function testGetsMaxRequestDurations() {
        $reader = new DatasetInfoReader("ExampleDataset");
        $info = $reader->GetMetadata();
        $duration = $info->GetMaxRequestDuration();
        $this->assertInstanceOf(DateInterval::class, $duration);
        $this->assertEquals(2, $duration->y);
        $this->assertEquals(0, $duration->m);
        $this->assertEquals(0, $duration->d);
        $this->assertEquals(0, $duration->h);
        $this->assertEquals(0, $duration->i);
        $this->assertEquals(1, $duration->s);
    }
}