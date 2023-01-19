<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Database\Database;
use App\Endpoint\Endpoint;
use App\Exception\UserInputException;
use App\Response\GoodResponse;
use App\Response\HapiCode;
use App\Util\Catalog;
use App\Util\Config;

class InfoEndpoint extends Endpoint {
    public function run() {
        $this->ValidateRequestedDataset();
        $data = $this->GetDatasetInfo();
        $response = new GoodResponse();
        $response->sendJsonDataToClient($data);
    }

    public function ValidateRequestedDataset() {
        $dataset = $this->GetRequestedDataset();
        if (!Catalog::hasDataset($dataset)) {
            throw new UserInputException(HapiCode::UNKNOWN_DATASET, "$dataset is not part of this server");
        }
    }

    public function GetDatasetInfo() {
        $dataset = $this->GetRequestedDataset();
        $db = Database::getInstance();

        $parameters = $db->GetParametersForDataset($dataset);
        $metadata = $db->GetDatasetMetadata($dataset);

        return array_merge($metadata, array("parameters" => $parameters));
    }

    public function GetRequestedDataset() : string {
        $dataset = $this->getRequestParameterWithDefault("dataset", "");
        if ($dataset == "") {
            throw new UserInputException(HapiCode::USER_ERROR, "Dataset was not provided");
        }
        return $dataset;
    }
}