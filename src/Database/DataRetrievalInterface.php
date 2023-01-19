<?php declare(strict_types=1);

namespace App\Database;

use DateTimeImmutable;

interface DataRetrievalInterface {
    public function GetParametersForDataset(string $dataset) : array;
    public function GetDatasetMetadata(string $dataset): array;
    public function QueryData(string $dataset, array $parameters, DateTimeImmutable $start, DateTimeImmutable $stop) : array;
}