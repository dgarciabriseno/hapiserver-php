<?php declare(strict_types=1);

namespace App\Database;

use DateTimeImmutable;

interface DataRetrievalInterface {
    /**
     * Gets the parameters allowed for the given dataset and returns them as an array of arrays
     * The first parameter must be the record's isotime field.
     * in the form:
     * [
     *  "name" => _
     *  "type" => _
     *  "description" => _
     *  "units" => _
     *  "fill" => _
     * ]
     */
    public function GetParametersForDataset(string $dataset) : array;

    /**
     * Returns dataset information to be returned over the info endpoint. Returns key, value
     * pairs corresponding to the HAPI Info response. Excludes parameters.
     */
    public function GetDatasetMetadata(string $dataset): array;

    /**
     * Returns data for a dataset. Result should be a json_encodable array of arrays in list form (no key value pairs).
     * The first element in each record must be the record's timestamp.
     */
    public function QueryData(string $dataset, array $parameters, DateTimeImmutable $start, DateTimeImmutable $stop) : array;

    /**
     * Returns the number of records that would be returned for the given dataset and time
     */
    public function QueryDataCount(string $dataset, DateTimeImmutable $start, DateTimeImmutable $stop) : int;
}