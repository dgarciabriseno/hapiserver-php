<?php declare(strict_types=1);

namespace App\Util;

use App\Database\Database;
use App\Exception\ConfigNotFoundException;

/**
 * Contains information about a given dataset
 */
class Dataset {
    protected string $dataset;

    public function __construct(string $dataset) {
        $this->dataset = $dataset;
    }

    public function GetName() {
        return $this->dataset;
    }

    public function GetParameters() : array {
        $db = Database::getInstance();
        return $db->GetParametersForDataset($this->dataset);
    }

    public function IsInCatalog() : bool {
        return Catalog::hasDataset($this->dataset);
    }

    public function GetTimeParameter() : string {
        $config = Config::getInstance();
        $time_parameter = $config->getWithDefault($this->dataset . '_TimeParameter', "");
        if ($time_parameter == "") {
            throw new ConfigNotFoundException("TimeParameter is not set for dataset " . $this->dataset);
        }
        return $time_parameter;
    }

    /**
     * Returns dataset metadata including timestampLocation, cadence, description, etc.
     */
    public function GetMetadata() : array {
        $db = Database::getInstance();
        return $db->GetDatasetMetadata($this->dataset);
    }
}