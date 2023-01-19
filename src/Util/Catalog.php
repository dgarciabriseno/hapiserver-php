<?php declare(strict_types=1);

namespace App\Util;

use App\Util\Config;

class Catalog {
    public static function getArray() : array {
        return self::GetCatalogFromConfig();
    }

    protected static function GetCatalogFromConfig() {
        $config = Config::getInstance();
        $catalog = $config->getWithDefault("catalog", []);
        return self::formatCatalogForHapiOutput($catalog);
    }

    public static function hasDataset(string $dataset) {
        $config = Config::getInstance();
        $catalog = $config->getWithDefault("catalog", []);
        return array_key_exists($dataset, $catalog);
    }

    protected static function formatCatalogForHapiOutput($catalog) {
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