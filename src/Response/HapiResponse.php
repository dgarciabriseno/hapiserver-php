<?php declare(strict_types=1);

namespace App\Response;
use App\Response\HttpResponse;

class HapiResponse {
    protected $code;
    protected $message;
    const HAPI_VERSION = "3.1";

    public function __construct(int $code, string $message) {
        $this->code = $code;
        $this->message = $message;
    }

    public function sendStatusToClient() {
        header("Content-Type: application/json");
        echo $this->getStatusJson();
    }

    public function getStatusArray() : array {
        return array(
            'HAPI' => self::HAPI_VERSION,
            'status' => array (
                'code' => $this->code,
                'message' => $this->message
            )
        );
    }

    public function getStatusJson() : string {
        return json_encode($this->getStatusArray());
    }
}