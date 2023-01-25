<?php declare(strict_types=1);

namespace App\Util;

use App\Database\Database;
use ValueError;

/**
 * Contains information about a given dataset
 */
class SubsetDataset extends Dataset {
    protected string $dataset;
    protected Dataset $superset;

    protected function __construct(string $dataset) {
        $this->dataset = $dataset;
        $this->superset = $this->GetParentDataset();
    }

    public static function fromDataset(Dataset $set) : SubsetDataset {
        if ($set->IsSubset()) {
            return $set;
        } else {
            throw new ValueError("Attempted to cast a non subset Dataset to the SubsetDataset class");
        }
    }

    public function IsSubset() : bool {
        return true;
    }

    public function GetParentDataset() : Dataset {
        $subsetInfo = $this->GetSubsetInfo();
        return Dataset::fromName($subsetInfo->parent);
    }

    public function GetTimeParameter() : string {
        return $this->superset->GetTimeParameter();
    }

    public function GetParameters(): array {
        return $this->superset->GetParameters();
    }

    public function GetSubsetInfo() : SubsetInfo {
        $subsets = $this->GetSubsets();
        return new SubsetInfo($subsets[$this->dataset]);
    }

    public function GetPostprocessors(): array {
        return $this->superset->GetPostprocessors();
    }

    public function GetMetadata(): array {
        $parentMetadata = $this->superset->GetMetadata();
        $subsetMetadata = $this->GetSubsetMetadata();

        // Remove options that shouldn't be shared
        unset($parentMetadata['description']);
        // Anything in subsetMetadata will override parentMetadata during the merge.
        return array_merge($parentMetadata, $subsetMetadata);
    }

    private function GetSubsetMetadata() : array {
        $db = Database::getInstance();
        return $db->GetDatasetMetadata($this->dataset);
    }
}