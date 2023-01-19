<?php declare(strict_types=1);

namespace App\Response;

class HapiResponse {
    protected $code;
    protected $message;
    const HAPI_VERSION = "3.1";

    public function __construct(int $code, string $message) {
        $this->code = $code;
        $this->message = $message;
    }

    protected function sendJsonToClient(string $json) {
        header("Content-Type: application/json");
        echo $json;
    }

    public function sendStatusToClient() {
        $status = $this->getStatusJson();
        $this->sendJsonToClient($status);
    }

    public function sendJsonDataToClient(array $data) {
        $json = $this->getDataJson($data);
        $this->sendJsonToClient($json);
    }

    public function getDataArray(array $data) : array {
        return $this->mergeDataWithStatus($data);
    }

    public function getDataJson(array $data) : string {
        return json_encode($this->getDataArray($data));
    }

    private function mergeDataWithStatus(array $data) : array {
        $status = $this->getStatusArray();
        return array_merge($data, $status);
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