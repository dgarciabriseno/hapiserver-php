<?php declare(strict_types=1);

namespace App\Extension;

use App\Util\Config;
use App\Util\Dataset;

/**
 * Postprocessors can be used to modify your data just-in-time before it's sent to the client.
 * You can register a postprocessor by specifying the class name in the config file.
 * The class name must match the file name and the file must exist in the Extension folder.
 */
abstract class Postprocessor {
    /**
     * Parameters are variable and depend on the user's request.
     * This variable stores the indices used to access specific parameters in the record array.
     */
    protected array $indices;

    protected string $outputFormat;

    protected Dataset $dataset;

    protected Config $config;

    public function __construct(array $parameter_indices, string $format, Dataset $dataset) {
        $this->indices = $parameter_indices;
        $this->outputFormat = $format;
        $this->dataset = $dataset;
        $this->config = Config::getInstance();
    }

    /**
     * Processes an individual record in-place.
     * This function will be executed on each record before it is sent to the client.
     * Due to the high volume of data, the record is provided by reference.
     */
    abstract public function ProcessRecord(array &$record) : void;
}