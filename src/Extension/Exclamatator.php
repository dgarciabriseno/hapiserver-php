<?php declare(strict_types=1);

namespace App\Extension;

/**
 * This is an example postprocessor to demonstrate how you may modify data before it's sent to the client.
 * It adds an exclamation point to every string data type that's returned.
 */
class Exclamatator extends Postprocessor {
    /**
     * This variable is automatically set for you. It contains a mapping of Parameter names to Record indices.
     * For example if you have a parameter named 'Width', you would access it in the record array like this:
     * $width_index = $this->indices['Width'];
     * $width = $record[$width_index];
     *
     */
    protected array $indices;

    public function ProcessRecord(array &$record): void {
        foreach ($record as &$entry) {
            if (is_string($entry)) {
                $entry .= "!";
            }
        }

        if (array_key_exists('string_data', $this->indices)) {
            $record[$this->indices['string_data']] = 'Exclamatator Override!';
        }
    }
}