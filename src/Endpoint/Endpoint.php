<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Exception\UnimplementedException;
use App\Exception\UserInputException;
use App\Response\HapiCode;
use App\Util\Catalog;
use DateTimeImmutable;

class Endpoint {
    protected $query_params;

    public function __construct() {
        $this->query_params = $_GET;
    }

    public function getRequestParameterWithDefault(string $key, $default) {
        return $this->query_params[$key] ?? $default;
    }

    public function run() {
        throw new UnimplementedException("This function must be overridden by a derived class");
    }

    public function AssertRequestedDatasetIsValid() {
        $dataset = $this->GetRequestedDataset();
        if (!Catalog::hasDataset($dataset)) {
            throw new UserInputException(HapiCode::UNKNOWN_DATASET, "$dataset is not part of this server");
        }
    }

    private function isDateValid(string $date) : bool {
        $parsed = date_parse($date);
        if ($parsed == false || $parsed['error_count'] > 0) {
            return false;
        }
        return true;
    }

    public function AssertStartTimeIsValid() {
        $date = $this->getRequestParameterWithDefault("start", "");
        if ($date == "") {
            throw new UserInputException(HapiCode::ERROR_IN_START_TIME, "Start time not provided");
        }

        if (!$this->isDateValid($date)) {
            throw new UserInputException(HapiCode::ERROR_IN_START_TIME, "Invalid start time");
        }
    }

    public function AssertStopTimeIsValid() {
        $date = $this->getRequestParameterWithDefault("stop", "");
        if ($date == "") {
            throw new UserInputException(HapiCode::ERROR_IN_STOP_TIME, "Stop time not provided");
        }

        if (!$this->isDateValid($date)) {
            throw new UserInputException(HapiCode::ERROR_IN_STOP_TIME, "Invalid stop time");
        }
    }

    public function GetRequestedDataset() : string {
        $dataset = $this->getRequestParameterWithDefault("dataset", "");
        if ($dataset == "") {
            throw new UserInputException(HapiCode::USER_ERROR, "Dataset was not provided");
        }
        return $dataset;
    }

    public function ValidateAndGetRequestedStartTime() : DateTimeImmutable {
        $this->AssertStartTimeIsValid();
        $parsed = new DateTimeImmutable($_GET['start']);
        return $parsed;
    }

    public function ValidateAndGetRequestedStopTime() : DateTimeImmutable {
        $this->AssertStopTimeIsValid();
        $parsed = new DateTimeImmutable($_GET['stop']);
        return $parsed;
    }

    public function ValidateStartDateIsBeforeEndDate(DateTimeImmutable $start, DateTimeImmutable $stop) {
        if ($stop <= $start) {
            throw new UserInputException(HapiCode::ERROR_IN_TIME, "Stop date is before or equal to start date");
        }
    }
}