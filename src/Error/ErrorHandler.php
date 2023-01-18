<?php declare(strict_types=1);

namespace App\Error;

use App\Exception\InvalidEndpointException;
use App\Response\HapiResponse;
use App\Error\ErrorLogger;
use App\Exception\ConfigNotFoundException;
use Exception;

class ErrorHandler {
    public static function HandleInvalidEndpointException(InvalidEndpointException $e) {
        http_response_code(404);
        $response = new HapiResponse(1400, $e->getMessage());
        $response->sendStatusToClient();
    }

    public static function HandleInternalError(Exception $e) {
        ErrorLogger::LogException($e);
        http_response_code(500);
        $response = new HapiResponse(1500, "Internal error");
        $response->sendStatusToClient();
    }

    public static function HandleConfigNotFoundException(ConfigNotFoundException $e) {
        ErrorLogger::LogException($e);
        http_response_code(500);
        $response = new HapiResponse(1500, "This server has not been configured");
        $response->sendStatusToClient();
    }
}