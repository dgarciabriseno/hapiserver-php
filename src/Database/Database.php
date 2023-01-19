<?php declare(strict_types=1);

namespace App\Database;
use App\Database\DataRetrievalInterface;
use App\Exception\UnimplementedException;
use App\Util\Config;

class Database {
    private static $instance = null;

    public static function getInstance() : DataRetrievalInterface {
        if (self::$instance == null) {
            self::$instance = self::CreateDatabaseInstance();
        }
        return self::$instance;
    }

    private static function CreateDatabaseInstance() : DataRetrievalInterface {
        $config = Config::getInstance();
        $driver = $config->getWithDefault("data_driver", "mysql");
        switch ($driver) {
            case "mysql":
                return new PDODatabase("App\Database\MySQLStatements");
            default:
                throw new UnimplementedException("Database driver " . $driver . " is not implemented");
        }
    }
}