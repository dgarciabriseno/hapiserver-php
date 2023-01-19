<?php declare(strict_types=1);

namespace App\Database;

use DateTimeImmutable;
use PDOStatement;

interface StatementProvider {
    public function GetColumnNames(string $database, string $table) : PDOStatement;
    public function GetStartDate(string $table, string $time_column) : PDOStatement;
    public function QueryData(string $table, string $time_column, array $columns, DateTimeImmutable $start, DateTimeImmutable $stop) : PDOStatement;
}