<?php declare(strict_types=1);

namespace App\Database;

interface DataRetrievalInterface {
    public function GetParametersForDataset(string $dataset) : array;
    public function GetDatasetMetadata(string $dataset);
}