<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Exception\UnimplementedException;

class Endpoint {
    protected $query_params;

    public function __construct() {
        $this->query_params = $_GET;
    }

    public function getRequestParameterWithDefault(string $key, $default) {
        return $this->query_params[$key] ?? $default;
    }

    public function run() {
        throw new UnimplementedException("This function must be overridden by a derived class");
    }
}