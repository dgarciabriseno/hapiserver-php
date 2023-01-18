<?php declare(strict_types=1);

namespace App\Exception;
use Exception;
use Throwable;

final class InvalidEndpointException extends Exception {
    public function __construct($endpoint, $code = 0, Throwable $previous = null) {
        parent::__construct("Invalid HAPI endpoint: " . $endpoint, $code, $previous);
    }
}