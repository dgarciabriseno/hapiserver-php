<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Error\ErrorHandler;
use App\Exception\InvalidEndpointException;

final class ErrorHandlerTest extends TestCase {
    /**
     * @runInSeparateProcess
     */
    public function testRaisesBadRequests() {
        $exception = new InvalidEndpointException("TestEndpoint");
        $response = ErrorHandler::HandleInvalidEndpointException($exception);
        $this->assertEquals(404, http_response_code());
    }
}