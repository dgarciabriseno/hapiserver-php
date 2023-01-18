<?php declare(strict_types=1);

namespace App\Response;

class HttpResponse {
    protected $headers = [];

    public function headers_list() {
        return $this->headers;
    }

    public function set_header(string $header): void {
        array_push($this->headers, $header);
    }

    protected function send(string $data) {
        $this->sendHeaders();
        $this->sendData($data);
    }

    protected function sendHeaders() {
        foreach ($this->headers as $header) {
            header($header);
        }
    }

    protected function sendData(string $data) {
        echo $data;
    }
}