<?php declare(strict_types=1);

namespace App\Endpoint;

use App\Endpoint\Endpoint;
use App\Response\GoodResponse;
use App\Util\Config;
use App\Util\ServerInfo;

class AboutEndpoint extends Endpoint {
    public function run() {
        $response = new GoodResponse();
        $serverInfo = new ServerInfo();
        $response->sendJsonDataToClient($serverInfo->getArray());
    }
}