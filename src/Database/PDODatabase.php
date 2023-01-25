<?php declare(strict_types=1);

namespace App\Database;

use App\Exception\DatabaseException;
use App\Exception\UnimplementedException;
use App\Exception\UserInputException;
use App\Response\HapiCode;
use App\Util\Config;
use App\Util\Dataset;
use App\Util\DatasetInfoReader;
use App\Util\DateUtils;
use App\Util\HapiType;
use App\Util\SubsetDataset;
use App\Util\SubsetInfo;
use DateTimeImmutable;
use PDO;
use PDOException;
use PDOStatement;
use ValueError;

class PDODatabase implements DataRetrievalInterface {
    protected PDO $pdo;
    protected StatementProvider $statement_provider;
    protected string $dbname;

    public function __construct(string $StatementProviderClass) {
        $this->dbname = Credentials::GetDatabaseName();
        $user = Credentials::GetDatabaseUser();
        $pass = Credentials::GetDatabasePassword();
        $connection_string = $this->buildConnectionString();
        try {
            $this->pdo = new PDO($connection_string, $user, $pass);
            // These settings are so that returned data is coerced into PHP types.
            // Without this everything is a string including numbers.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new DatabaseException("Unable to communicate with the backend database", array("info" => $e->getMessage()));
        }
        $this->statement_provider = new $StatementProviderClass($this->pdo);
    }

    protected function buildConnectionString() {
        $driver = Credentials::GetDatabaseDriver();
        $host = Credentials::GetHost();
        $database = Credentials::GetDatabaseName();
        return $driver . ":host=" . $host . ";dbname=" . $database;
    }

    public function GetParametersForDataset(string $dataset): array {
        $column_parameters = $this->GetColumnParameters($dataset);
        $metaparameters = $this->GetMetaparameterInfo($dataset);
        return array_merge($column_parameters, $metaparameters);
    }

    private function GetColumnParameters(string $dataset) : array {
        $whitelist = $this->GetColumnWhitelistForDataset($dataset);
        $columnInfo = $this->FetchDatabaseColumnsInfo($dataset);
        $parameters = array();
        foreach($columnInfo as $row) {
            // Skip any columns that are not whitelisted.
            if (!in_array($row["COLUMN_NAME"], $whitelist)) {
                continue;
            }

            $parameter = array(
                "name" => $row["COLUMN_NAME"],
                "type" => HapiType::GetTypeFor($row["DATA_TYPE"]),
                "description" => $this->GetParameterDescription($dataset, $row["COLUMN_NAME"]),
                "units" => $this->GetParameterUnit($dataset, $row["COLUMN_NAME"]),
                "fill" => null
            );
            if ($parameter["type"] == "string") {
                $parameter = array_merge($parameter, array("length" => intval($row["CHARACTER_MAXIMUM_LENGTH"])));
            }
            if ($parameter["type"] == "isotime") {
                // 26 characters represents the max time for a date like 2022-01-01T00:00:00.123456Z
                $parameter["length"] = 27;
            }
            array_push($parameters, $parameter);
        }
        $parameters = $this->SortParametersByWhitelist($whitelist, $parameters);
        $parameters = $this->PlaceTimestampFirst($dataset, $parameters);
        return $parameters;
    }

    private function SortParametersByWhitelist(array $whitelist, array $parameters) : array {
        $sorted = array();
        foreach ($whitelist as $column) {
            $parameter = array_filter($parameters, function ($param) use ($column) { return $param['name'] == $column; });
            array_push($sorted, $parameter[array_key_first($parameter)]);
        }
        return $sorted;
    }

    /**
     * Returns the parameters array with the timestamp column as the first item in the array.
     */
    private function PlaceTimestampFirst(string $dataset, array $parameters) : array {
        $table = $this->getTableForDataset($dataset);
        $time_column = $this->getTimeColumn($table);
        $time_parameter = array_filter($parameters, function ($param) use ($time_column) { return $param['name'] == $time_column; });
        $other_parameters = array_filter($parameters, function ($param) use ($time_column) { return $param['name'] != $time_column; });
        return array_merge($time_parameter, $other_parameters);
    }

    private function GetMetaparameterInfo(string $dataset) : array {
        $metaparameters = $this->GetMetaparametersFromConfig($dataset);
        $parameters = array();
        foreach ($metaparameters as $meta => $_) {
            $param = array(
                "name" => $meta,
                "type" => $this->GetMetaparameterTypeFromConfig($dataset, $meta),
                "description" => $this->GetParameterDescription($dataset, $meta),
                "units" => $this->GetParameterUnit($dataset, $meta),
                "fill" => null
            );
            if ($param['type'] == "string") {
                $param = array_merge($param, array("length" => $this->GetMetaparameterMaxLengthFromConfig($dataset, $meta)));
            }
            array_push($parameters, $param);
        }
        return $parameters;
    }

    private function GetMetaparametersFromConfig(string $dataset) {
        $set = Dataset::fromName($dataset);
        $dataset = $set->GetParentDataset()->GetName();
        $config = Config::getInstance();
        return $config->getWithDefault($dataset . '_metaparameters', array());
    }

    private function GetMetaparameterTypeFromConfig(string $dataset, string $metaparam) : string {
        $set = Dataset::fromName($dataset);
        $dataset = $set->GetParentDataset()->GetName();
        $config = Config::getInstance();
        return $config->getWithDefault($dataset . '_' . $metaparam . '_type', 'Unspecified');
    }

    private function GetMetaparameterMaxLengthFromConfig(string $dataset, string $metaparam) : int {
        $set = Dataset::fromName($dataset);
        $dataset = $set->GetParentDataset()->GetName();
        $config = Config::getInstance();
        return intval($config->getWithDefault($dataset . '_' . $metaparam . '_maxlength', 10000));
    }

    public function GetColumnWhitelistForDataset(string $dataset) : array {
        $table = $this->getTableForDataset($dataset);
        return $this->GetColumnWhitelist($table);
    }

    public function GetColumnWhitelist(string $table) : array {
        $config = Config::getInstance();
        $whitelist = $table . '_ColumnWhitelist';
        return $config->getWithDefault($table . '_ColumnWhitelist', array());
    }

    protected function GetParametersAsList(string $dataset) : array {
        $parameters = $this->GetParametersForDataset($dataset);
        $result = array();
        foreach ($parameters as $param) {
            array_push($result, $param['name']);
        }
        return $result;
    }

    protected function FetchDatabaseColumnsInfo(string $dataset) : array {
        // For a database backed instance, the "parameters" returned for a dataset correspond to the column names.
        $table = $this->getTableForDataset($dataset);
        $pdo_statement = $this->statement_provider->GetColumnNames($this->dbname, $table);
        return $this->ExecuteStatementAndFetchResults($pdo_statement);
    }

    protected function ExecuteStatementAndFetchResults(PDOStatement $pdo_statement, int $fetchMode = PDO::FETCH_BOTH) : array {
        $query_was_successful = $pdo_statement->execute();
        if ($query_was_successful) {
            return $pdo_statement->fetchAll($fetchMode);
        } else {
            throw new DatabaseException("Failed to get a result from the database.", $pdo_statement->errorInfo());
        }
    }

    protected function GetParameterDescription(string $dataset, string $parameter) {
        $key = $dataset . "_" . $parameter . "_description";
        $config = Config::getInstance();
        return $config->getWithDefault($key, "No description available");
    }

    protected function GetParameterUnit(string $dataset, string $parameter) {
        $key = $dataset . "_" . $parameter . "_unit";
        $config = Config::getInstance();
        return $config->getWithDefault($key, "Unspecified");
    }

    public function GetStartDate(string $dataset) : string {
        $table = $this->getTableForDataset($dataset);
        $column = $this->getTimeColumn($table);
        $this->ApplySubsetFilter($dataset);
        $statement = $this->statement_provider->GetStartDate($table, $column);
        $result = $this->ExecuteStatementAndFetchResults($statement);
        return DateUtils::SQLDateToIsoDate($result[0]["StartDate"]);
    }

    protected function getTimeColumn(string $table) : string {
        $key = $table . "_TimeColumn";
        $config = Config::getInstance();
        $column = $config->getWithDefault($key, "");
        if ($column == "") {
            throw new UnimplementedException("Config is missing the time column for the table: $table");
        }
        return $column;
    }

    public function GetStopDate(string $dataset) : string {
        $table = $this->getTableForDataset($dataset);
        $column = $this->getTimeColumn($table);
        $this->ApplySubsetFilter($dataset);
        $statement = $this->statement_provider->GetStopDate($table, $column);
        $result = $this->ExecuteStatementAndFetchResults($statement);
        return DateUtils::SQLDateToIsoDate($result[0]["StopDate"]);
    }

    protected function getTableForDataset(string $dataset_name) : string {
        $dataset = Dataset::fromName($dataset_name);
        $parent_name = $dataset->GetParentDataset()->GetName();
        $config = Config::getInstance();
        $mapping = $config->getWithDefault("catalog_id_to_db_table", array());
        if (array_key_exists($parent_name, $mapping)) {
            return $mapping[$parent_name];
        } else {
            return $dataset_name;
        }
    }

    public function GetDatasetMetadata(string $dataset) : array {
        $startDate = $this->GetStartDate($dataset);
        $endDate = $this->GetStopDate($dataset);
        $reader = new DatasetInfoReader($dataset);
        $metadata = $reader->GetMetadata()->asArray();
        return array_merge($metadata, array("startDate" => $startDate, "stopDate" => $endDate));
    }

    private function GetValidQueryParameters(string $dataset, array $parameters) : array {
        if (empty($parameters)) {
            return $this->GetParametersAsList($dataset);
        } else {
            $dataset_parameters = $this->GetParametersAsList($dataset);
            // Make sure all requested parameters are part of the dataset.
            foreach ($parameters as $requested_param) {
                if (!in_array($requested_param, $dataset_parameters)) {
                    throw new UserInputException(HapiCode::UNKNOWN_DATASET_PARAMETER, "$requested_param is not part of dataset $dataset");
                }
            }
            return $parameters;
        }
    }

    /**
     * Removes any metaparameters from the $parameters list and returns them as their own array.
     */
    private function ExtractRequestedMetaparameters(string $dataset, array &$parameters) : array {
        // Move any metaparameters from the parameter list into the final parameter list list
        $config = Config::getInstance();
        $metaparameters = $config->getWithDefault($dataset . '_metaparameters', array());
        foreach ($metaparameters as $name => $_) {
            // The metaparameters array starts out fully containing all metaparameters.
            // parameters is the list of parameters requested by the user, some of which may be metaparameters.
            // if the metaparameter is in the parameter array, then remove it and keep it in the metaparameter array.
            // if the metaparameter is in not in the parameter array, then the user didn't request it, so remove it from metaparameters.
            // The end result is that metaparameters should contain only metaparameters requested by the user, or it should be an empty list.
            if (in_array($name, $parameters)) {
                $idx = array_search($name, $parameters);
                unset($parameters[$idx]);
            } else {
                unset($metaparameters[$name]);
            }
        }
        return $metaparameters;
    }

    public function QueryData(string $dataset, array $parameters, DateTimeImmutable $start, DateTimeImmutable $stop): array {
        $this->ValidateDatasetDates($dataset, $start, $stop);
        $parameters = $this->GetValidQueryParameters($dataset, $parameters);
        // This function modifies $parameters to literally extract the metaparameters
        $metaparameters = $this->ExtractRequestedMetaparameters($dataset, $parameters);
        $table = $this->getTableForDataset($dataset);
        $time_column = $this->getTimeColumn($table);
        $this->ApplySubsetFilter($dataset);

        $query = $this->statement_provider->QueryData($table, $time_column, $parameters, $metaparameters, $start, $stop);
        $result = $this->ExecuteStatementAndFetchResults($query, PDO::FETCH_NUM);
        return $result;
    }

    public function ValidateDatasetDates(string $dataset, DateTimeImmutable $start, DateTimeImmutable $stop) {
        $dataset_start_date = new DateTimeImmutable($this->GetStartDate($dataset));
        if ($stop < $dataset_start_date) {
            throw new UserInputException(HapiCode::TIME_OUTSIDE_RANGE, "Stop time is before the start of the dataset time range");
        }

        $dataset_stop_date = new DateTimeImmutable($this->GetStopDate($dataset));
        if ($start > $dataset_stop_date) {
            throw new UserInputException(HapiCode::TIME_OUTSIDE_RANGE, "Start time is after the end of the dataset time range");
        }
    }

    public function QueryDataCount(string $dataset, DateTimeImmutable $start, DateTimeImmutable $stop) : int {
        $table = $this->getTableForDataset($dataset);
        $time_column = $this->getTimeColumn($table);
        $this->ApplySubsetFilter($dataset);

        $statement = $this->statement_provider->QueryDataCount($table, $time_column, $start, $stop);
        $result = $this->ExecuteStatementAndFetchResults($statement);
        return intval($result[0]['count']);
    }

    private function ApplySubsetFilter(string $dataset) {
        $set = Dataset::fromName($dataset);
        if ($set->IsSubset()) {
            $filter = $this->GetSubsetFilter($set);
            $this->statement_provider->SetFilter($filter->column, $filter->value);
        }
    }

    private function GetSubsetFilter(Dataset $set) : SubsetInfo {
        if ($set->IsSubset()) {
            $subset = SubsetDataset::fromDataset($set);
            return $subset->GetSubsetInfo();
        } else {
            throw new ValueError("Attempted to get subset info on non subset");
        }
    }
}

