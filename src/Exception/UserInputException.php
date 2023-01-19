<?php declare(strict_types=1);

namespace App\Exception;
use Exception;
use Throwable;

final class UserInputException extends Exception implements HapiException{
    protected int $hapi_code;

    public function __construct($hapi_code, $message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->hapi_code = $hapi_code;
    }

    public function GetHapiCode() : int {
        return $this->hapi_code;
    }
}