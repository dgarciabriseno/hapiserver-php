<?php declare(strict_types=1);

namespace App\Database;

use App\Exception\UnsafeDataException;
use DateTimeImmutable;
use PDO;
use PDOStatement;

/**
 * Executes MySQL statements for the given PDO.
 */
class MySQLStatements implements StatementProvider {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function GetColumnNames($database, $table) : PDOStatement {
        $statement = $this->pdo->prepare("
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :database
                AND TABLE_NAME = :table;
        ");
        $statement->bindValue('database', $database);
        $statement->bindValue('table', $table);
        return $statement;
    }

    public function GetStartDate(string $table, string $time_column) : PDOStatement {
        $this->VerifySafeDataOrThrowException($table);
        $this->VerifySafeDataOrThrowException($time_column);
        // To allow the column or table to be accessed dynamically, we need to take extra
        // precautions that it's not vulnerable to SQL injection. Per the design, this "should" be safe
        // because the table/column values come from the config file. In case this is extended for these values
        // to come from somewhere else, we should make sure the values are safe.
        $sql = sprintf("SELECT TIMESTAMP(MIN(%s)) as StartDate FROM %s",
            $time_column, $table);
        return $this->pdo->prepare($sql);
    }

    public function GetStopDate(string $table, string $time_column) : PDOStatement {
        $this->VerifySafeDataOrThrowException($table);
        $this->VerifySafeDataOrThrowException($time_column);
        $sql = sprintf("SELECT TIMESTAMP(MAX(%s)) as StopDate FROM %s",
            $time_column, $table);
        return $this->pdo->prepare($sql);
    }

    private function VerifySafeDataOrThrowException(string $data) {
        // Only allow alphanumeric characters, and underscores. quotes, slashes, etc are all violations.
        preg_match('/^[a-zA-Z0-9_]+$/', $data, $matches);
        if (count($matches) == 0 || $matches[0] != $data) {
            throw new UnsafeDataException("Caught unsafe data attempting to be used in a SQL query: $data");
        }
    }

    private function VerifySafeColumnsOrThrowException(array $columns) {
        foreach ($columns as $column) {
            $this->VerifySafeDataOrThrowException($column);
        }
    }

    public function QueryData(string $table, string $time_column, array $columns, DateTimeImmutable $start, DateTimeImmutable $stop): PDOStatement {
        $this->VerifySafeDataOrThrowException($table);
        $this->VerifySafeDataOrThrowException($time_column);
        $this->VerifySafeColumnsOrThrowException($columns);
        $sql = sprintf("
            SELECT %s FROM %s WHERE %s >= :start_time AND %s < :stop_time
        ",
            implode(',', $columns),
            $table,
            $time_column,
            $time_column);
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue("start_time", $start->format('Y-m-d H:i:s.v'));
        $statement->bindValue("stop_time", $stop->format('Y-m-d H:i:s.v'));
        return $statement;
    }

    public function QueryDataCount(string $table, string $time_column, DateTimeImmutable $start, DateTimeImmutable $stop): PDOStatement {
        $this->VerifySafeDataOrThrowException($table);
        $this->VerifySafeDataOrThrowException($time_column);
        $sql = sprintf("
            SELECT COUNT(*) as count FROM %s WHERE %s >= :start_time AND %s < :stop_time
        ", $table, $time_column, $time_column);
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue('start_time', $start->format('Y-m-d H:i:s.v'));
        $statement->bindValue('stop_time', $stop->format('Y-m-d H:i:s.v'));
        return $statement;
    }
}