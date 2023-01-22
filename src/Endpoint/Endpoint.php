<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Exception\UnimplementedException;
use App\Exception\UserInputException;
use App\Response\HapiCode;
use App\Util\Catalog;
use App\Util\Config;
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

    /**
     * Handles backwards compatibility for HAPI, allows the user to send either "start" or "time.min" as the input.
     */
    private function GetRawStartTime() {
        $date = $this->getRequestParameterWithDefault("start", "");
        if ($date != "") {
            return $date;
        } else {
            // time.min appears as time_min in the request
            return $this->getRequestParameterWithDefault("time_min", "");
        }
    }

    private function GetRawStopTime() {
        $date = $this->getRequestParameterWithDefault("stop", "");
        if ($date != "") {
            return $date;
        } else {
            // time.max appears as time_max in the request
            return $this->getRequestParameterWithDefault("time_max", "");
        }
    }

    public function AssertStartTimeIsValid() {
        $date = $this->GetRawStartTime();
        if ($date == "") {
            throw new UserInputException(HapiCode::ERROR_IN_START_TIME, "Start time not provided");
        }

        if (!$this->isDateValid($date)) {
            throw new UserInputException(HapiCode::ERROR_IN_START_TIME, "Invalid start time");
        }
    }

    public function AssertStopTimeIsValid() {
        $date = $this->GetRawStopTime();
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
            // Support for HAPI version < 3
            $dataset = $this->getRequestParameterWithDefault("id", "");
            if ($dataset == "") {
                throw new UserInputException(HapiCode::USER_ERROR, "Dataset was not provided");
            }
        }
        return $dataset;
    }

    public function ValidateAndGetRequestedStartTime() : DateTimeImmutable {
        $this->AssertStartTimeIsValid();
        $parsed = new DateTimeImmutable($this->GetRawStartTime());
        return $parsed;
    }

    public function ValidateAndGetRequestedStopTime() : DateTimeImmutable {
        $this->AssertStopTimeIsValid();
        $parsed = new DateTimeImmutable($this->GetRawStopTime());
        return $parsed;
    }

    public function ValidateStartDateIsBeforeEndDate(DateTimeImmutable $start, DateTimeImmutable $stop) {
        if ($stop <= $start) {
            throw new UserInputException(HapiCode::ERROR_IN_TIME, "Stop date is before or equal to start date");
        }
    }

    public function GetRequestedParameters() : array {
        $parameters = $this->getRequestParameterWithDefault("parameters", "");
        if ($parameters == "") {
            return array();
        } else {
            $parameter_list = explode(',', $parameters);
            $parameter_list = $this->InsertTimeParameterIfMissing($parameter_list);
            $parameter_list = $this->PlaceTimestampFirst($parameter_list);
            return $parameter_list;
        }
    }

    private function InsertTimeParameterIfMissing(array $parameter_list) : array {
        $dataset = $this->GetRequestedDataset();
        $time_parameter = $this->GetDatasetTimeParameter($dataset);
        if (in_array($time_parameter, $parameter_list)) {
            return $parameter_list;
        } else {
            array_unshift($parameter_list, $time_parameter);
            return $parameter_list;
        }
    }

    private function GetDatasetTimeParameter(string $dataset) : string {
        $config = Config::getInstance();
        $time_parameter = $config->getWithDefault($dataset . '_TimeParameter', "");
        if ($time_parameter == "") {
            throw new ConfigNotFoundException("TimeParameter is not set for dataset $dataset");
        }
        return $time_parameter;
    }

    /**
     * $parameter_list should already have the time parameter in the list when this is called
     */
    private function PlaceTimestampFirst(array $parameter_list) : array {
        $dataset = $this->GetRequestedDataset();
        $time_parameter = $this->GetDatasetTimeParameter($dataset);
        assert(in_array($time_parameter, $parameter_list));
        $parameters = array_filter($parameter_list, function ($param) use ($time_parameter) {return $param != $time_parameter;});
        // unshift will put the time parameter in the front of the list
        array_unshift($parameters, $time_parameter);
        return $parameters;
    }
}