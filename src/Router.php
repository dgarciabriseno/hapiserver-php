<?php declare(strict_types=1);

namespace App;
use App\Endpoint\CapabilitiesEndpoint;
use App\Endpoint\AboutEndpoint;
use App\Endpoint\CatalogEndpoint;
use App\Endpoint\InfoEndpoint;
use App\Endpoint\DataEndpoint;
use App\Endpoint\Endpoint;
use App\Error\ErrorHandler;
use App\Exception\InvalidEndpointException;

class Router {
    static public function route($path) {
        try {
            $endpoint = Router::GetEndpoint($path);
        } catch (InvalidEndpointException $e) {
            ErrorHandler::HandleInvalidEndpointException($e);
        }
    }

    static public function GetEndpoint(string $path) : Endpoint {
        $resource = basename($path);
        switch ($resource) {
            case "capabilities":
                return new CapabilitiesEndpoint();
            case "about":
                return new AboutEndpoint();
            case "catalog":
                return new CatalogEndpoint();
            case "info":
                return new InfoEndpoint();
            case "data":
                return new DataEndpoint();
            default:
                throw new InvalidEndpointException($resource);
        }
    }
}