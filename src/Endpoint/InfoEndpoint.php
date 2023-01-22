<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Database\Database;
use App\Endpoint\Endpoint;
use App\Exception\UserInputException;
use App\Response\GoodResponse;
use App\Response\HapiCode;

class InfoEndpoint extends Endpoint {
    public function run() {
        $this->AssertRequestedDatasetIsValid();
        $data = $this->GetDatasetInfo();
        $response = new GoodResponse();
        $response->sendJsonDataToClient($data);
    }

    public function GetDatasetInfo() {
        $dataset = $this->GetRequestedDataset();
        $db = Database::getInstance();

        $parameters = $db->GetParametersForDataset($dataset);
        $filtered_parameters = $this->FilterForUserSpecifiedParameters($parameters);
        $metadata = $db->GetDatasetMetadata($dataset);

        return array_merge($metadata, array("parameters" => $filtered_parameters));
    }

    private function FilterForUserSpecifiedParameters(array $parameters) {
        $filter = $this->GetRequestedParameters();
        if (empty($filter)) {
            return $parameters;
        } else {
            $this->VerifyParameterList($filter, $parameters);
            return $this->GetParametersMatchingFilter($filter, $parameters);
        }
    }

    private function VerifyParameterList(array $filter, array $parameters) {
        foreach ($filter as $user_filter) {
            if (!$this->ParameterListContains($user_filter, $parameters)) {
                throw new UserInputException(HapiCode::UNKNOWN_DATASET_PARAMETER, "Unknown parameter: " . $user_filter);
            }
        }
    }

    private function ParameterListContains($key, array $parameter_list) {
        $search_result = array_filter($parameter_list, function ($el) use ($key) { return $el['name'] == $key; });
        return count($search_result) > 0;
    }

    private function getParameterList() {
        $parameters_input = $this->getRequestParameterWithDefault("parameters", "");
        if ($parameters_input == "") {
            return [];
        } else {
            return explode(",", $parameters_input);
        }
    }

    private function GetParametersMatchingFilter(array $names_to_keep, array $parameters) {
        $kept_parameters = array();

        foreach ($parameters as $param) {
            if (in_array($param['name'], $names_to_keep)) {
                array_push($kept_parameters, $param);
            }
        }

        return $kept_parameters;
    }
}