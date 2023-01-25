<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Error\ErrorHandler;
use App\Error\ErrorLogger;
use App\Exception\DatabaseException;
use App\Exception\UserInputException;
use App\Response\HapiCode;

final class ErrorHandlerTest extends TestCase {
    protected function setUp() : void {
        ErrorHandler::SetErrorLogger(LoggerStub::class);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRaisesBadRequests() {
        $exception = new UserInputException(HapiCode::USER_ERROR, "Test error");
        $response = ErrorHandler::HandleUserInputException($exception);
        $this->assertEquals(404, http_response_code());
        $data = $response->getStatusArray();
        $this->assertEquals(HapiCode::USER_ERROR, $data['status']['code']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testUsesProvidedHapiCode() {
        $exception = new UserInputException(HapiCode::INTERNAL_ERROR, "Test error");
        $response = ErrorHandler::HandleUserInputException($exception);
        $this->assertEquals(404, http_response_code());
        $data = $response->getStatusArray();
        $this->assertEquals(HapiCode::INTERNAL_ERROR, $data['status']['code']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHandlesDatabaseExceptions() {
        $exception = new DatabaseException("DB Exception", array());
        $response = ErrorHandler::HandleDatabaseException($exception);
        $this->assertEquals(500, http_response_code());
        $data = $response->getStatusArray();
        $this->assertEquals(1501, $data['status']['code']);
    }
}

class LoggerStub extends ErrorLogger {
    public static function LogException(Throwable $e) {}
}
