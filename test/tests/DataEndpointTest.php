<?php declare(strict_types=1);

use App\Endpoint\DataEndpoint;
use PHPUnit\Framework\TestCase;

final class DataEndpointTest extends TestCase {
    public function testGetsSpecifiedData() {
        $endpoint = new DataEndpoint();
        $data = $endpoint->QueryData("ExampleDataset", array('string_data'), new DateTimeImmutable('2022-01-01 00:00:00'), new DateTimeImmutable('2022-02-01'));
        $first_item = $data[0];
        $this->assertCount(1, $first_item);
    }
}