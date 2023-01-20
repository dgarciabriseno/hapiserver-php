<?php declare(strict_types=1);

namespace App\Database;

use DateTimeImmutable;
use PDOStatement;

interface StatementProvider {
    /**
     * Returns a statement that will return the name of each column.
     * Required array keys in the select are:
     * - COLUMN_NAME
     * - DATA_TYPE
     * - CHARACTER_MAXIMUM_LENGTH
     */
    public function GetColumnNames(string $database, string $table) : PDOStatement;

    /**
     * Returns a statement that will return the start date of a dataset
     * Query must return 1 row with the key StartDate
     */
    public function GetStartDate(string $table, string $time_column) : PDOStatement;

    /**
     * Returns a statement that will return the start date of a dataset
     * Query must return 1 row with the key StopDate
     */
    public function GetStopDate(string $table, string $time_column) : PDOStatement;

    /**
     * Returns a statement that will return all the request data.
     * Data should be returned in the order given in $columns
     */
    public function QueryData(string $table, string $time_column, array $columns, DateTimeImmutable $start, DateTimeImmutable $stop) : PDOStatement;

    /**
     * Returns a statement that will return the number of records over the time range.
     * This should be representative of how many records would be returned with QueryData.
     * The result must return the key "count"
     */
    public function QueryDataCount(string $table, string $time_column, DateTimeImmutable $start, DateTimeImmutable $stop) : PDOStatement;
}