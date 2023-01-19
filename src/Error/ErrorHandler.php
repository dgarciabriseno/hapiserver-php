<?php declare(strict_types=1);

namespace App\Error;

use App\Response\HapiResponse;
use App\Error\ErrorLogger;
use App\Exception\ConfigNotFoundException;
use App\Exception\DatabaseException;
use App\Exception\UserInputException;
use Exception;

class ErrorHandler {
    protected static string $logger = ErrorLogger::class;

    public static function SetErrorLogger(string $logger_class) {
        self::$logger = $logger_class;
    }

    public static function HandleUserInputException(UserInputException $e) {
        // No need to log user errors. These are purely a user error and would just be noise in the error log.
        http_response_code(404);
        $response = new HapiResponse($e->GetHapiCode(), $e->getMessage());
        $response->sendStatusToClient();
        return $response;
    }

    public static function HandleInternalError(Exception $e) {
        self::$logger::LogException($e);
        http_response_code(500);
        $response = new HapiResponse(1500, "Internal error");
        $response->sendStatusToClient();
        return $response;
    }

    public static function HandleConfigNotFoundException(ConfigNotFoundException $e) {
        self::$logger::LogException($e);
        http_response_code(500);
        $response = new HapiResponse(1500, "This server has not been configured");
        $response->sendStatusToClient();
        return $response;
    }

    public static function HandleDatabaseException(DatabaseException $e) {
        self::$logger::LogException($e);
        http_response_code(500);
        $response = new HapiResponse(1501, "Unable to communicate with the backend database");
        $response->sendStatusToClient();
        return $response;
    }
}