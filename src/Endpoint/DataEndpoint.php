<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Database\Database;
use App\Endpoint\Endpoint;
use App\Exception\UnimplementedException;
use App\Exception\UserInputException;
use App\Response\DataResponse;
use App\Response\HapiCode;
use DateTimeImmutable;

class DataEndpoint extends Endpoint {
    public function run() {
        $this->AssertRequestedDatasetIsValid();
        $dataset = $this->GetRequestedDataset();
        $start = $this->ValidateAndGetRequestedStartTime();
        $stop = $this->ValidateAndGetRequestedStopTime();
        $this->ValidateStartDateIsBeforeEndDate($start, $stop);
        $parameters = $this->GetRequestedParameters();

        $data = $this->QueryData($dataset, $parameters, $start, $stop);

        $header = array();
        if ($this->getRequestParameterWithDefault("include", "") == "header") {
            $header = $this->GetDataHeader($dataset);
        }

        $response = new DataResponse($data, $header);

        $format = $this->getRequestParameterWithDefault("format", "csv");
        switch ($format) {
            case "csv":
                return $response->sendAsCsv();
            case "json":
                return $response->sendAsJson();
            case "binary":
                throw new UserInputException(HapiCode::UNSUPPORTED_OUTPUT, "This server doesn't support binary output");
            }
    }

    public function QueryData(string $dataset, array $parameters, DateTimeImmutable $start, DateTimeImmutable $stop) {
        $db = Database::getInstance();
        return $db->QueryData($dataset, $parameters, $start, $stop);
    }

    public function GetRequestedParameters() {
        $parameters = $this->getRequestParameterWithDefault("parameters", "");
        if ($parameters == "") {
            return array();
        } else {
            return explode(',', $parameters);
        }
    }

    public function GetDataHeader(string $dataset) {
        $info = new InfoEndpoint();
        return $info->GetDatasetInfo($dataset);
    }
}