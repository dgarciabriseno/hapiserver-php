<?php declare(strict_types=1);

namespace App\Util;

use App\Exception\ConfigNotFoundException;
use DateInterval;
use Exception;

/**
 * Reads metadata for a dataset from the config file
 */
class DatasetInfo {
    protected array $info;

    public function __construct(array $info) {
        $this->info = $info;
    }

    public function GetMaxRequestDuration() : DateInterval {
        if (array_key_exists('maxRequestDuration', $this->info)) {
            try {
                return new DateInterval($this->info['maxRequestDuration']);
            } catch (Exception $e) {
                throw new ConfigNotFoundException("maxRequestDuration in the configuration is invalid");
            }
        } else {
            return new DateInterval('P1Y');
        }
    }

    public function asArray() : array {
        return $this->info;
    }
}