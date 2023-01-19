<?php declare(strict_types=1);

namespace App\Util;
use App\Util\Config;

/**
 * Handles returning data from the config.ini file
 */
class ServerInfo {
    protected $config;
    protected $info;
    public function __construct() {
        $this->config = Config::getInstance();
        $this->info = $this->ConstructServerInfo();
    }

    public function getArray() : array {
        return $this->info;
    }

    protected function ConstructServerInfo() : array {
        $required = $this->GetRequiredFieldsFromConfig();
        $optional = $this->GetOptionalFieldsFromConfig();
        return array_merge($required, $optional);
    }

    protected function GetRequiredFieldsFromConfig() : array {
        return array (
            "id" => $this->config->getWithDefault("server_id", "Unspecified"),
            "title" => $this->config->getWithDefault("server_name", "Unspecified"),
            "contact" => $this->config->getWithDefault("server_contact", "Unspecified")
        );
    }

    protected function GetOptionalFieldsFromConfig() : array {
        $optional = array (
            "description" => $this->config->getWithDefault("server_description", ""),
            "contactID" => $this->config->getWithDefault("server_contact_id", ""),
            "citation" => $this->config->getWithDefault("server_citation", "")
        );
        return $this->removeEmptyValuesFromArray($optional);
    }

    private function removeEmptyValuesFromArray(array $arr) {
        foreach(array_keys($arr) as $key) {
            if ($arr[$key] == "") {
                unset($arr[$key]);
            }
        }
        return $arr;
    }
}