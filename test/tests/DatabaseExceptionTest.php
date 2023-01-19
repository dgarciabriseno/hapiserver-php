<?php declare(strict_types=1);

use App\Error\ErrorLogger;
use App\Exception\DatabaseException;
use PHPUnit\Framework\TestCase;

final class DatabaseExceptionTest extends TestCase {
    public function testCreatesDatabase() {
        $exception = new DatabaseException("Test exception", array("SQLSTATE", "1", "Driver Error Message"));
        $error_lines = ErrorLogger::GetExceptionLines($exception);
        $this->assertContains("0: SQLSTATE", $error_lines);
        $this->assertContains("1: 1", $error_lines);
        $this->assertContains("2: Driver Error Message", $error_lines);
    }
}