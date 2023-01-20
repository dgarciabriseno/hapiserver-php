<?php declare(strict_types=1);

use App\Endpoint\DataEndpoint;
use App\Exception\UserInputException;
use PHPUnit\Framework\TestCase;

final class DataEndpointTest extends TestCase {
    public function testGetsSpecifiedData() {
        $endpoint = new DataEndpoint();
        $data = $endpoint->QueryData("ExampleDataset", array('string_data'), new DateTimeImmutable('2022-01-01 00:00:00'), new DateTimeImmutable('2022-02-01'));
        $first_item = $data[0];
        $this->assertCount(1, $first_item);
    }

    public function testVerifyMaxRequestDurationIsHonored() {
        $endpoint = new DataEndpoint();
        $start = new DateTimeImmutable('2020-01-01');
        $stop = new DateTimeImmutable('2023-01-01');
        $this->expectException(UserInputException::class);
        $endpoint->VerifyStartStopIsWithinMaxRequestDuration("ExampleDataset", $start, $stop);
    }
    public function testValidDurationsAreAllowed() {
        $endpoint = new DataEndpoint();
        $start = new DateTimeImmutable('2021-01-01');
        $stop = new DateTimeImmutable('2022-01-01');
        $endpoint->VerifyStartStopIsWithinMaxRequestDuration("ExampleDataset", $start, $stop);
    }

    public function testCanCompareDateIntervals() {
        $one_year = new DateInterval('P1Y');
        $two_years = new DateInterval('P2Y');
        $two_years_and_one_second = new DateInterval('P2YT1S');
        $endpoint = new DataEndpoint();
        // 2 years exceeds 1 year
        $this->assertTrue($endpoint->DurationExceedsDuration($two_years, $one_year));
        // 1 year does not exceed 2 years
        $this->assertFalse($endpoint->DurationExceedsDuration($one_year, $two_years));

        // 2 years + 1 second exceeds 2 years
        $this->assertTrue($endpoint->DurationExceedsDuration($two_years_and_one_second, $two_years));
        // 2 years does not exceed 2 years + 1 second
        $this->assertFalse($endpoint->DurationExceedsDuration($two_years, $two_years_and_one_second));

        // 2 years does not exceed 2 years
        $this->assertFalse($endpoint->DurationExceedsDuration($two_years, $two_years));
    }

    public function testBlocksRequestsThatWouldReturnTooMuchData() {
        $endpoint = new DataEndpoint();
        $this->expectException(UserInputException::class);
        $endpoint->BlockRequestIfQueryExceedsRecordLimit("ExampleDataset", new DateTimeImmutable('2020-01-01'), new DateTimeImmutable('2023-01-01'));
    }

    public function testAllowsRequestsThatWontReturnTooMuchData() {
        $endpoint = new DataEndpoint();
        $endpoint->BlockRequestIfQueryExceedsRecordLimit("ExampleDataset", new DateTimeImmutable('2022-01-16'), new DateTimeImmutable('2023-01-01'));
    }
}