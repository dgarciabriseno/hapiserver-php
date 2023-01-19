<?php declare(strict_types=1);

namespace App\Util;

/**
 * Reads metadata for a dataset from the config file
 */
class DatasetMetadataReader {
    protected Config $config;
    protected string $dataset;

    public function __construct(string $dataset) {
        $this->dataset = $dataset;
        $this->config = Config::getInstance();
    }

    public function GetMetadata() {
        $metadata = array();
        $this->PushOptionalMetadata($metadata, "timeStampLocation");
        $this->PushOptionalMetadata($metadata, "cadence");
        $this->PushOptionalMetadata($metadata, "sampleStartDate");
        $this->PushOptionalMetadata($metadata, "sampleStopDate");
        $this->PushOptionalMetadata($metadata, "maxRequestDuration");
        $this->PushOptionalMetadata($metadata, "description");
        $this->PushOptionalMetadata($metadata, "unitsSchema");
        $this->PushOptionalMetadata($metadata, "coordinateSystemSchema");
        $this->PushOptionalMetadata($metadata, "resourceURL");
        $this->PushOptionalMetadata($metadata, "resourceID");
        $this->PushOptionalMetadata($metadata, "creationDate");
        $this->PushOptionalMetadata($metadata, "citation");
        $this->PushOptionalMetadata($metadata, "modificationDate");
        $this->PushOptionalMetadata($metadata, "contact");
        $this->PushOptionalMetadata($metadata, "contactID");
        return $metadata;
    }

    private function PushOptionalMetadata(array &$arr, string $metadata_key) {
        $data = $this->GetMetadataArrayFromConfig($metadata_key);
        if ($this->MetadataExistsForDataset($this->dataset, $data)) {
            $arr[$metadata_key] = $data[$this->dataset];
        }
    }

    private function GetMetadataArrayFromConfig(string $key) : array {
        return $this->config->getWithDefault($key, array());
    }

    private function MetadataExistsForDataset(string $dataset, array $metadata) {
        return array_key_exists($dataset, $metadata);
    }
}