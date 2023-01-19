<?php declare(strict_types=1);

namespace App\Exception;

use App\Response\HapiCode;
use Exception;
use Throwable;

final class DatabaseException extends Exception implements HapiException {
    public function __construct($message, $errorInfo, $code = 0, Throwable $previous = null) {
        parent::__construct($this->constructErrorMessage($message, $errorInfo), $code, $previous);
    }

    private function constructErrorMessage(string $message, array $errorInfo) {
        return $message . "\n" . $this->stringifyArrayWithNewlines($errorInfo);
    }

    private function stringifyArrayWithNewlines(array $errorInfo) {
        $str = "";
        foreach ($errorInfo as $key => $value) {
            $str .= "$key: $value\n";
        }
        return $str;
    }

    public function GetHapiCode(): int {
        return HapiCode::UPSTREAM_ERROR;
    }
}