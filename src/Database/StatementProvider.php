<?php declare(strict_types=1);

namespace App\Database;

use PDOStatement;

interface StatementProvider {
    public function GetColumnNames($database, $table) : PDOStatement;
}