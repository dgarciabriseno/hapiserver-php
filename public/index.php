<?php declare(strict_types=1);

require_once "../vendor/autoload.php";

use App\Router;
use App\Error\ErrorHandler;

try {
    $path = get_url_path();
    Router::route($path);
} catch (Throwable $e) {
    ErrorHandler::HandleInternalError($e);
}

function get_url_path() : string {
    return parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH);
}