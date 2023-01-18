<?php declare(strict_types=1);

namespace App\Response;

class GoodResponse extends HapiResponse {
    protected $code;
    protected $message;

    public function __construct() {
        parent::__construct(1200, "OK");
    }
}