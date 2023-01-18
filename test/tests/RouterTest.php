<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Router;

use App\Endpoint\CapabilitiesEndpoint;
use App\Endpoint\AboutEndpoint;
use App\Endpoint\CatalogEndpoint;
use App\Endpoint\InfoEndpoint;
use App\Endpoint\DataEndpoint;

final class RouterTest extends TestCase {
    public function testFindsCapabilitiesEndpoint() {
        $this->assertInstanceOf(
            CapabilitiesEndpoint::class,
            Router::GetEndpoint("/hapi/capabilities")
        );
    }

    public function testFindsAboutEndpoint() {
        $this->assertInstanceOf(
            AboutEndpoint::class,
            Router::GetEndpoint("/hapi/about")
        );
    }

    public function testFindsCatalogEndpoint() {
        $this->assertInstanceOf(
            CatalogEndpoint::class,
            Router::GetEndpoint("/hapi/catalog")
        );
    }

    public function testFindsInfoEndpoint() {
        $this->assertInstanceOf(
            InfoEndpoint::class,
            Router::GetEndpoint("/hapi/info")
        );
    }

    public function testFindsDataEndpoint() {
        $this->assertInstanceOf(
            DataEndpoint::class,
            Router::GetEndpoint("/whatever/data")
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testReturnsBadRequestForOtherEndpoints() {
        Router::route("something that doesn't exist");
        $this->assertEquals(404, http_response_code());
    }
}