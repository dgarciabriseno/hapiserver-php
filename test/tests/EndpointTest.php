<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Endpoint\Endpoint;

final class EndpointTest extends TestCase {
    public function testParsesQueryParameters() {
        $this->specifyQueryParameter("id", 10);

        $endpoint = new Endpoint();
        $this->assertEquals(
            10,
            $endpoint->getRequestParameterWithDefault('id', 0)
        );
    }

    public function testProvidesDefaultValues() {
        $endpoint = new Endpoint();
        $this->assertEquals(
            22,
            $endpoint->getRequestParameterWithDefault('id', 22)
        );
    }

    private function specifyQueryParameter(string $key, $value) {
        $_GET[$key] = $value;
    }

    protected function tearDown() : void {
        $this->clearQueryParameters();
    }

    private function clearQueryParameters() {
        $_GET = [];
    }
}