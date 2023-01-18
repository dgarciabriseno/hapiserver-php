<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Endpoint\Endpoint;
use App\Response\GoodResponse;

class CapabilitiesEndpoint extends Endpoint {
    public function run() {
        $response = new GoodResponse();
        $response->sendJsonDataToClient($this->getSupportedCapabilities());
    }

    private function getSupportedCapabilities() {
        return array (
            "outputFormats" => array (
                "csv",
                "json"
            )
        );
    }
}