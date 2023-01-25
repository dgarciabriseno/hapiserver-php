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
    protected string $filter_column = "1";
    protected $filter_value = 1;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->UnsetFilter();
    }

    public function SetFilter(string $column, $value) {
        $this->VerifySafeString($column);
        $this->filter_column = $column;
        $this->filter_value = $value;
    }

    public function UnsetFilter() {
        $this->filter_column = "1";
        $this->filter_value = 1;
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
        $this->VerifySafeString($table);
        $this->VerifySafeString($time_column);
        // To allow the column or table to be accessed dynamically, we need to take extra
        // precautions that it's not vulnerable to SQL injection. Per the design, this "should" be safe
        // because the table/column values come from the config file. In case this is extended for these values
        // to come from somewhere else, we should make sure the values are safe.
        $sql = sprintf("SELECT TIMESTAMP(MIN(%s)) as StartDate FROM %s WHERE %s = :filter_value",
            $time_column, $table, $this->filter_column);
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue("filter_value", $this->filter_value);
        return $statement;
    }

    public function GetStopDate(string $table, string $time_column) : PDOStatement {
        $this->VerifySafeString($table);
        $this->VerifySafeString($time_column);
        $sql = sprintf("SELECT TIMESTAMP(MAX(%s)) as StopDate FROM %s WHERE %s = :filter_value",
            $time_column, $table, $this->filter_column);
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue("filter_value", $this->filter_value);
        return $statement;
    }

    private function VerifySafeString(string $data) {
        // Only allow alphanumeric characters, and underscores. quotes, slashes, etc are all violations.
        preg_match('/^[a-zA-Z0-9_]+$/', $data, $matches);
        if (count($matches) == 0 || $matches[0] != $data) {
            throw new UnsafeDataException("Caught unsafe data attempting to be used in a SQL query: $data");
        }
    }

    private function VerifySafeStringArray(array $columns) {
        foreach ($columns as $column) {
            $this->VerifySafeString($column);
        }
    }

    public function QueryData(string $table, string $time_column, array $columns, array $metacolumns, DateTimeImmutable $start, DateTimeImmutable $stop): PDOStatement {
        $this->VerifySafeString($table);
        $this->VerifySafeString($time_column);
        $this->VerifySafeStringArray($columns);
        $metacolumn_portion = $this->GetMetacolumnSQL($metacolumns);
        if (empty($columns)) {
            // Remove leading comma in metacolumn portion of sql since there are no columns.
            $metacolumn_portion = substr($metacolumn_portion, 1);
        }

        $sql = sprintf("
            SELECT %s%s FROM %s WHERE %s >= :start_time AND %s < :stop_time AND %s = :filter_value
        ",
            implode(',', $columns),
            $metacolumn_portion,
            $table,
            $time_column,
            $time_column,
            $this->filter_column);
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue("start_time", $start->format('Y-m-d H:i:s.v'));
        $statement->bindValue("stop_time", $stop->format('Y-m-d H:i:s.v'));
        $statement->bindValue("filter_value", $this->filter_value);
        return $statement;
    }

    /**
     * Builds the metacolumn portion of the query which looks like this:
     * ", CONCAT_WS('~', a, b, c) as metaparameter"
     */
    public function GetMetacolumnSQL(array $metacolumns) {
        $sql = "";
        if (!empty($metacolumns)) {
            $sql .= ",";
        }

        foreach ($metacolumns as $name => $columns) {
            $column_list = explode(',', $columns);
            $this->VerifySafeStringArray($column_list);
            $this->VerifySafeString($name);
            $sql .= " CONCAT_WS('~', " . implode(',', $column_list) . ") as $name,";
        }

        // remove trailing comma
        return substr($sql, 0, -1);
    }

    public function QueryDataCount(string $table, string $time_column, DateTimeImmutable $start, DateTimeImmutable $stop): PDOStatement {
        $this->VerifySafeString($table);
        $this->VerifySafeString($time_column);
        $sql = sprintf("
            SELECT COUNT(*) as count FROM %s WHERE %s >= :start_time AND %s < :stop_time AND %s = :filter_value
        ", $table, $time_column, $time_column, $this->filter_column);
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue('start_time', $start->format('Y-m-d H:i:s.v'));
        $statement->bindValue('stop_time', $stop->format('Y-m-d H:i:s.v'));
        $statement->bindValue("filter_value", $this->filter_value);
        return $statement;
    }
}