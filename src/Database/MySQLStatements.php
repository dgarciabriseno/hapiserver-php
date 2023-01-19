<?php declare(strict_types=1);

namespace App\Database;
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
}