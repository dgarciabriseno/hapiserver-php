<?php declare(strict_types=1);

namespace App\Util;

use App\Exception\UnimplementedException;

/**
 * Backend types may be somewhat different than the Hapi standard.
 * For example, a database may hold a float which should be delivered as a double.
 * Use this class to map internal type names to HAPI type names
 */
class HapiType {
    /**
     * Available HAPI types are:
     * - string
     * - double
     * - integer
     * - isotime
     */
    const TypeMap = array(
        "string" => "string",
        "varchar" => "string",

        "float" => "double",
        "double" => "double",
        "decimal" => "double",

        "integer" => "integer",
        "int" => "integer",
        "tinyint" => "integer",
        "smallint" => "integer",

        "isotime" => "isotime",
        "datetime" => "isotime"
    );

    public static function GetTypeFor(string $type) : string {
        if (array_key_exists($type, self::TypeMap)) {
            return self::TypeMap[$type];
        } else {
            throw new UnimplementedException("$type has not been mapped to a HAPI Specification Type");
        }
    }
}