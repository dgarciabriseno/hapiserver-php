<?php declare(strict_types=1);

namespace App\Error;

use App\Exception\InvalidEndpointException;
use App\Response\HapiResponse;
use Exception;

class ErrorHandler {
    public static function HandleInvalidEndpointException(InvalidEndpointException $e) {
        http_response_code(404);
        $response = new HapiResponse(1400, $e->getMessage());
        $response->sendStatusToClient();
    }
}