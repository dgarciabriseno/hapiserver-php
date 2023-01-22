<?php declare(strict_types=1);

namespace App\Extension;

/**
 * If your data returns strings with commas in them, then you must enable this extension to wrap the string with quotes.
 */
class QuoteStringsForCSV extends Postprocessor {
    /**
     * This variable is automatically set for you. It contains a mapping of Parameter names to Record indices.
     * For example if you have a parameter named 'Width', you would access it in the record array like this:
     * $width_index = $this->indices['Width'];
     * $width = $record[$width_index];
     *
     */
    protected array $indices;

    public function ProcessRecord(array &$record): void {
        if ($this->outputFormat == 'csv') {
            foreach ($record as &$entry) {
                if (is_string($entry)) {
                    $entry = '"' . $entry . '"';
                }
            }
        }
    }
}