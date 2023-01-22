<?php declare(strict_types=1);

namespace App\Extension;

/**
 * This is an example of using a postprocessor to create a metaparameter.
 * See the description in config.ini for more details about metaparameters.
 */
class ExampleMetaparameter extends Postprocessor {
    /**
     * This variable is automatically set for you. It contains a mapping of Parameter names to Record indices.
     * For example if you have a parameter named 'Width', you would access it in the record array like this:
     * $width_index = $this->indices['Width'];
     * $width = $record[$width_index];
     */
    protected array $indices;

    public function ProcessRecord(array &$record): void {
        /**
         * The example config states that example_metaparameter is the name of the metaparameter.
         * It also specified that the metaparameter is derived from the id and timestamp columns.
         * Within the record, there will be a key matching the name of your metaparameter.
         * Its value will the values for the columns specified in the ini file separated by a ~
         * In this example the columns for the metaparameter were id and timestamp.
         */
        // Reminder that records are not key indexed. The result may be returned as a CSV, the ordering depends on user specified orders.
        if (array_key_exists('example_metaparameter', $this->indices)) {
            $index = $this->indices['example_metaparameter'];
            $value = $record[$index];
            // ~ is the defacto separator for columns returned for metaparameters.
            // If your data contains a ~, I'm sorry. Make a PR making this separator configurable.
            $split = explode('~', $value);
            // since id was specified first, it will be returned first.
            $id = $split[0];
            // timestamp was specified second, so it'll be the second item.
            $timestamp = $split[1];
            $record[$index] = "Database ID: $id, timestamp: $timestamp";
        }
    }
}