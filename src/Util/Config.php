<?php declare(strict_types=1);

namespace App\Util;
use App\Exception\ConfigNotFoundException;

/**
 * Handles returning data from the config.ini file
 */
class Config {
    protected $ini;

    private static $instance = null;

    public static function getInstance() : Config {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->ini = $this->loadIniFile();
    }

    protected function loadIniFile() {
        $ini = parse_ini_file(__DIR__ . "/../../config.ini");
        if ($ini == false) {
            throw new ConfigNotFoundException();
        }
        return $ini;
    }

    public function getWithDefault(string $key, $default) {
        if (array_key_exists($key, $this->ini)) {
            return $this->ini[$key];
        }
        return $default;
    }
}