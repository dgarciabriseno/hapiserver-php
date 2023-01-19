<?php declare(strict_types=1);

namespace App\Exception;
use Exception;

interface HapiException {
    public function GetHapiCode() : int;
}