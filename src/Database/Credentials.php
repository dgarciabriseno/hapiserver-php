<?php declare(strict_types=1);

namespace App\Database;

use App\Util\Config;

class Credentials {
    public static function GetHost() : string {
        $config = Config::getInstance();
        return $config->getWithDefault("dbhost", "127.0.0.1");
    }

    public static function GetDatabaseName() : string {
        $config = Config::getInstance();
        return $config->getWithDefault("dbname", "");
    }

    public static function GetDatabaseUser() : string {
        $config = Config::getInstance();
        return $config->getWithDefault("dbuser", "");
    }

    public static function GetDatabasePassword() : string {
        $config = Config::getInstance();
        return $config->getWithDefault("dbpass", "");
    }

    public static function GetDatabaseDriver() : string {
        $config = Config::getInstance();
        return $config->getWithDefault("data_driver", "");
    }
}