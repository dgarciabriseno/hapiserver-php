<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Endpoint\Endpoint;
use App\Response\GoodResponse;
use App\Util\Catalog;

class CatalogEndpoint extends Endpoint {
    public function run() {
        $catalog = new Catalog();
        $response = new GoodResponse();
        $response->sendJsonDataToClient($catalog->getArray());
    }
}