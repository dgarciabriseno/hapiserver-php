<?php declare(strict_types=1);

namespace App\Util;

use App\Database\Database;
use App\Exception\ConfigNotFoundException;

class SubsetInfo {
    public string $parent;
    public string $column;
    public string $value;

    public function __construct(string $subset_info) {
        $split = explode(',', $subset_info);
        $this->parent = $split[0];
        $this->column = $split[1];
        $this->value = $split[2];
    }
}