<?php declare(strict_types=1);

namespace App\Extension;

use App\Util\Config;
use App\Util\Dataset;
use DateTimeImmutable;

/**
 * If your dates are not already in ISO UTC format (ex: 2022-01-01T13:55:00.123Z)
 * Then enable this extension to fomrat the dates into UTC time
 */
class DateToIsoFormat extends Postprocessor {
    /**
     * This variable is automatically set for you. It contains a mapping of Parameter names to Record indices.
     * For example if you have a parameter named 'Width', you would access it in the record array like this:
     * $width_index = $this->indices['Width'];
     * $width = $record[$width_index];
     *
     */
    protected array $indices;

    protected Config $config;

    private string $time_parameter;

    public function __construct(array $parameter_indices, string $format, Dataset $dataset) {
        parent::__construct($parameter_indices, $format, $dataset);
        $this->time_parameter = $dataset->GetTimeParameter();
    }

    public function ProcessRecord(array &$record): void {
        if ($this->time_parameter != "") {
            $index = $this->indices[$this->time_parameter];
            $date = new DateTimeImmutable($record[$index]);
            $record[$index] = $date->format("Y-m-d\TH:i:s.u\Z");
        }
    }
}