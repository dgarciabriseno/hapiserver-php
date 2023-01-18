<?php declare(strict_types=1);

namespace App\Endpoint;

class Endpoint {
    protected $query_params;

    public function __construct() {
        $this->query_params = $_GET;
    }

    public function getWithDefault(string $key, $default) {
        return $this->query_params[$key] ?? $default;
    }
}