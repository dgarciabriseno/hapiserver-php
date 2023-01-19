<?php declare(strict_types=1);

namespace App\Exception;

use App\Response\HapiCode;
use Exception;

final class UnimplementedException extends Exception implements HapiException {
    public function GetHapiCode(): int {
        return HapiCode::INTERNAL_ERROR;
    }
}