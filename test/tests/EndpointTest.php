<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Endpoint\Endpoint;
use App\Exception\UserInputException;

final class EndpointTest extends TestCase {
    public function testParsesQueryParameters() {
        $this->specifyQueryParameter("id", 10);

        $endpoint = new Endpoint();
        $this->assertEquals(
            10,
            $endpoint->getRequestParameterWithDefault('id', 0)
        );
    }

    private function specifyQueryParameter(string $key, $value) {
        $_GET[$key] = $value;
    }

    public function testProvidesDefaultValues() {
        $endpoint = new Endpoint();
        $this->assertEquals(
            22,
            $endpoint->getRequestParameterWithDefault('id', 22)
        );
    }

    public function testThrowsExceptionIfStartTimeIsInvalid() {
        $_GET["start"] = "Invalid Time";
        $endpoint = new Endpoint();
        $this->expectException(UserInputException::class);
        $endpoint->AssertStartTimeIsValid();
    }

    public function testReturnsStartDatesForValidInputs() {
        $_GET["start"] = "2022-08-11T12:34:11.987Z";
        $endpoint = new Endpoint();
        $time = $endpoint->ValidateAndGetRequestedStartTime();
        $this->assertEquals("2022-08-11 12:34:11.987", $time->format('Y-m-d H:i:s.v'));
    }

    public function testWorksForPartialStartDates() {
        $_GET["start"] = "2022-08-11";
        $endpoint = new Endpoint();
        $time = $endpoint->ValidateAndGetRequestedStartTime();
        $this->assertEquals("2022-08-11 00:00:00.000", $time->format('Y-m-d H:i:s.v'));
    }

    public function testThrowsExceptionIfStopTimeIsInvalid() {
        $_GET["stop"] = "Invalid Time";
        $endpoint = new Endpoint();
        $this->expectException(UserInputException::class);
        $endpoint->AssertStartTimeIsValid();
    }

    public function testReturnsStopDatesForValidInputs() {
        $_GET["stop"] = "2022-08-11T12:34:11.987Z";
        $endpoint = new Endpoint();
        $time = $endpoint->ValidateAndGetRequestedStopTime();
        $this->assertEquals("2022-08-11 12:34:11.987", $time->format('Y-m-d H:i:s.v'));
    }

    public function testWorksForPartialStopDates() {
        $_GET["stop"] = "2022-08-11";
        $endpoint = new Endpoint();
        $time = $endpoint->ValidateAndGetRequestedStopTime();
        $this->assertEquals("2022-08-11 00:00:00.000", $time->format('Y-m-d H:i:s.v'));
    }

    public function testCanThrowExceptionIfDatesAreNotSequential() {
        // notice start is before stop
        $_GET["start"] = "2022-09-11";
        $_GET["stop"] = "2022-08-11";
        $endpoint = new Endpoint();
        $start = $endpoint->ValidateAndGetRequestedStartTime();
        $stop = $endpoint->ValidateAndGetRequestedStopTime();
        $this->expectException(UserInputException::class);
        $endpoint->ValidateStartDateIsBeforeEndDate($start, $stop);
    }

    public function testNoExceptionIfDatesAreValid() {
        // notice start is before stop
        $_GET["start"] = "2022-08-11T00:00:00.000Z";
        $_GET["stop"] = "2022-08-11T00:00:00.001Z";
        $endpoint = new Endpoint();
        $start = $endpoint->ValidateAndGetRequestedStartTime();
        $stop = $endpoint->ValidateAndGetRequestedStopTime();
        $endpoint->ValidateStartDateIsBeforeEndDate($start, $stop);
    }

    public function testGetRequestedDataset() {
        $_GET["dataset"] = "This is a test";
        $endpoint = new Endpoint();
        $this->assertEquals("This is a test", $endpoint->GetRequestedDataset());
    }

    public function testGetRequestedDatasetWithIdParameter() {
        $_GET["id"] = "This is a test";
        $endpoint = new Endpoint();
        $this->assertEquals("This is a test", $endpoint->GetRequestedDataset());
    }

    public function testThrowsExceptionIfDatasetIsNotGiven() {
        $_GET["bad"] = "This is a test";
        $endpoint = new Endpoint();
        $this->expectException(UserInputException::class);
        $endpoint->GetRequestedDataset();
    }

    protected function tearDown() : void {
        $this->clearQueryParameters();
    }

    private function clearQueryParameters() {
        $_GET = [];
    }
}