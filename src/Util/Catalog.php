<?php declare(strict_types=1);

namespace App\Util;

use App\Util\Config;

class Catalog {
    protected $catalog;
    protected $config;
    public function __construct() {
        $this->config = Config::getInstance();
        $this->catalog = $this->GetCatalogFromConfig();
    }

    public function getArray() {
        return $this->catalog;
    }

    protected function GetCatalogFromConfig() {
        $catalog = $this->config->getWithDefault("catalog", []);
        return $this->formatCatalogForHapiOutput($catalog);
    }

    protected function formatCatalogForHapiOutput($catalog) {
        $items = array();
        // The catalog here is formed from id => title pairs.
        foreach (array_keys($catalog) as $id) {
            array_push($items, array(
                "id" => $id,
                "title" => $catalog[$id]
            ));
        }
        return array (
            "catalog" => $items
        );
    }
}