<?php declare(strict_types=1);

namespace App\Error;
use Exception;

class ErrorLogger {
    public static function LogException(Exception $e) {
        // By default, logging an exception manually to error_log results in the whole stack trace appearing on one line.
        // This function makes sure the error log is printed cleanly in the error log.
        $error_lines = ErrorLogger::GetExceptionLines($e);
        foreach ($error_lines as $line) {
            error_log($line);
        }
    }

    private static function GetExceptionLines(Exception $e) {
        $message = "$e";
        return explode("\n", $message);
    }
}