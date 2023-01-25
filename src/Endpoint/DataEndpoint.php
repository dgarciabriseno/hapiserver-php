<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Database\Database;
use App\Endpoint\Endpoint;
use App\Exception\ConfigNotFoundException;
use App\Exception\UserInputException;
use App\Response\DataResponse;
use App\Response\HapiCode;
use App\Util\Config;
use App\Util\Dataset;
use App\Util\DatasetInfoReader;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class DataEndpoint extends Endpoint {
    public function run() {
        $this->AssertRequestedDatasetIsValid();
        $dataset = $this->GetRequestedDataset();
        $start = $this->ValidateAndGetRequestedStartTime();
        $stop = $this->ValidateAndGetRequestedStopTime();
        $this->VerifyStartStopIsWithinMaxRequestDuration($dataset->GetName(), $start, $stop);
        $this->ValidateStartDateIsBeforeEndDate($start, $stop);
        $parameters = $this->GetRequestedParameters();

        $this->BlockRequestIfQueryExceedsRecordLimit($dataset->GetName(), $start, $stop);

        $data = $this->QueryData($dataset->GetName(), $parameters, $start, $stop);

        $format = $this->getRequestParameterWithDefault("format", "csv");
        $this->RunPostprocessors($format, $dataset->GetName(), $parameters, $data);

        $header = array();
        if ($this->getRequestParameterWithDefault("include", "") == "header") {
            $header = $this->GetDataHeader($dataset->GetName());
        }

        $response = new DataResponse($data, $header);

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

    public function GetDataHeader(string $dataset) {
        $info = new InfoEndpoint();
        return $info->GetDatasetInfo($dataset);
    }

    public function VerifyStartStopIsWithinMaxRequestDuration(string $dataset, DateTimeImmutable $start, DateTimeImmutable $stop) {
        $datasetInfo = new DatasetInfoReader($dataset);
        $maxDuration = $datasetInfo->GetMetadata()->GetMaxRequestDuration();
        $requestDuration = $stop->diff($start);
        if ($this->DurationExceedsDuration($requestDuration, $maxDuration)) {
            throw new UserInputException(HapiCode::TOO_MUCH_DATA, "Request duration exceeds maxRequestDuration, please shorten your start and stop times");
        }
    }

    public function DurationExceedsDuration(DateInterval $duration, DateInterval $limit) {
        // Why aren't DateInterval's comparable in PHP???
        // need to make sure calling DateTime->add always ADDS to the datetime.
        // invert=1 leads to subtracting the dateinterval from the DateTime
        $duration->invert = 0;
        $limit->invert = 0;

        $max_date = new DateTime();
        $date_to_check = clone $max_date;

        $max_date->add($limit);
        $date_to_check->add($duration);

        return $date_to_check > $max_date;
    }

    public function BlockRequestIfQueryExceedsRecordLimit(string $dataset, DateTimeImmutable $start, DateTimeImmutable $stop) {
        $config = Config::getInstance();
        // config will return '1' when 'true' is specified in the configuration. It's a PHP thing.
        $should_limit_records = $config->getWithDefault('enable_record_limit', '1') == '1';
        if ($should_limit_records) {
            $recordLimit = $this->GetRecordLimit($dataset);
            $count = $this->GetRecordCount($dataset, $start, $stop);
            if ($count > $recordLimit) {
                throw new UserInputException(HapiCode::TOO_MUCH_DATA, "Request would return too much data, please reduce your time range");
            }
        }
    }

    public function GetRecordLimit(string $dataset) : int {
        $config = Config::getInstance();
        $record_limit_str = $config->getWithDefault('record_limit_' . $dataset, '10000');
        $limit = intval($record_limit_str);
        // intval returns 0 on failure.
        if ($limit == 0) {
            throw new ConfigNotFoundException("Unable to read configuration for record_limit_$dataset");
        }
        return $limit;
    }

    public function GetRecordCount(string $dataset, DateTimeImmutable $start, DateTimeImmutable $stop) : int {
        $db = Database::getInstance();
        return $db->QueryDataCount($dataset, $start, $stop);
    }

    /**
     * Postprocessors are user-defined php scripts that may modify the data before sending it to the client.
     */
    public function RunPostprocessors(string $format, string $dataset, array $parameters, array &$data) {
        $set = Dataset::fromName($dataset);
        $postprocessors = $set->GetPostprocessors();
        if (!empty($postprocessors)) {
            $indices = $this->GetParameterIndices($dataset, $parameters);
            foreach ($postprocessors as $postprocessor) {
                $postprocessor_class = "App\Extension\\$postprocessor";
                $postprocessor = new $postprocessor_class($indices, $format, $set);
                foreach ($data as &$record) {
                    $postprocessor->ProcessRecord($record);
                }
            }
        }
    }

    /**
     * Returns a mapping of parameter name to index
     * For example, for parameters a, b, and c, this will return:
     * [ 'a' => 0, 'b' => 1, 'c' => 2]
     * If the parameters array is empty, then the parameters used will be the full set of parameters
     * for the dataset.
     */
    public function GetParameterIndices(string $dataset, array $parameters) {
        // Parameters will be empty if the user isn't requesting any specific parameter list.
        // In this case all parameters are returned, so we need the parameter list.
        if (empty($parameters)) {
            $db = Database::getInstance();
            $all_parameters = $db->GetParametersForDataset($dataset);
            $indices = array();
            foreach ($all_parameters as $index => $param) {
                $indices[$param['name']] = $index;
            }
            return $indices;
        } else {
            $indices = array();
            foreach ($parameters as $index => $param) {
                $indices[$param] = $index;
            }
            return $indices;
        }
    }
}