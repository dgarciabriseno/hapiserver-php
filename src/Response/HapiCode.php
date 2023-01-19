<?php declare(strict_types=1);

namespace App\Response;

class HapiCode {
    const OK = 1200;
    const OK_NO_DATA = 1201;
    const USER_ERROR = 1400;
    const UNKNOWN_PARAMETER = 1401;
    const ERROR_IN_START_TIME = 1402;
    const ERROR_IN_STOP_TIME = 1403;
    const ERROR_IN_TIME = 1404;
    const TIME_OUTSIDE_RANGE = 1405;
    const UNKNOWN_DATASET = 1406;
    const UNKNOWN_DATASET_PARAMETER = 1407;
    const TOO_MUCH_DATA = 1408;
    const UNSUPPORTED_OUTPUT = 1409;
    const UNSUPPORTED_INCLUDE = 1410;
    const INTERNAL_ERROR = 1500;
    const UPSTREAM_ERROR = 1501;
}