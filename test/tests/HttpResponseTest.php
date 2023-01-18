<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Response\HttpResponse;

final class HttpResponseTest extends TestCase {
    public function testSetsHttpResponseHeaders() {
        $http = new HttpResponse();
        $http->set_header("Content-Type: application/json");
        $headers = $http->headers_list();
        $this->assertEquals(
            "Content-Type: application/json",
            $headers[0]
        );
    }
}