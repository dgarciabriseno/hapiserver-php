<?php declare(strict_types=1);

namespace App\Response;

class DataResponse extends HapiResponse {
    protected $code;
    protected $message;
    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
        parent::__construct(1200, "OK");
    }

    public function sendAsJson() {
        $json = json_encode($this->data);
        $this->sendJsonToClient($json);
    }

    public function sendAsCsv() {
        header('Content-Type: text/csv');
        foreach ($this->data as $record) {
            echo implode(',', $record);
            echo "\n";
        }
    }
}