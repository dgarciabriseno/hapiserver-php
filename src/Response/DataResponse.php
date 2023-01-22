<?php declare(strict_types=1);

namespace App\Response;

class DataResponse extends HapiResponse {
    protected $code;
    protected $message;
    protected $data;
    protected $header;

    public function __construct(array $data, array $header) {
        $this->data = $data;
        $this->header = $header;
        parent::__construct(1200, "OK");
    }

    public function sendAsJson() {
        header("Content-Type: application/json");
        if (empty($this->header)) {
            $json = json_encode($this->data);
            $this->echo($json);
        } else {
            $final_data = array_merge($this->header, array("data" => $this->data));
            $json = json_encode($final_data);
            $this->echo($json);
        }
    }

    public function sendAsCsv() {
        header('Content-Type: text/csv');
        $this->EnableCORS();
        $this->sendCommentedHeaderIfNotEmpty();
        foreach ($this->data as $record) {
            echo implode(',', $record);
            echo "\n";
        }
    }

    private function sendCommentedHeaderIfNotEmpty() {
        if (!empty($this->header)) {
            echo "#";
            echo json_encode($this->header);
            echo "\n";
        }
    }
}