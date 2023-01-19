<?php declare(strict_types=1);

namespace App\Util;

class DateUtils {
    public static function SQLDateToIsoDate(string $sql_date) {
        $date = str_replace(" ", "T", $sql_date);
        return $date . "Z";
    }
}