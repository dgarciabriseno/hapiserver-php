<?php declare(strict_types=1);

namespace App\Database;

use App\Exception\DatabaseException;
use App\Exception\UnimplementedException;
use App\Exception\UserInputException;
use App\Response\HapiCode;
use App\Util\Config;
use App\Util\DatasetInfo;
use App\Util\DatasetInfoReader;
use App\Util\DateUtils;
use App\Util\HapiType;
use DateTimeImmutable;
use PDO;
use PDOException;
use PDOStatement;

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
        $info = $this->FetchParameters($dataset);
        $result = array();
        foreach($info as $row) {
            $parameter = array(
                "name" => $row["COLUMN_NAME"],
                "type" => HapiType::GetTypeFor($row["DATA_TYPE"]),
                "description" => $this->GetParameterDescription($dataset, $row["COLUMN_NAME"]),
                "units" => $this->GetParameterUnit($dataset, $row["COLUMN_NAME"]),
                "fill" => null
            );
            if ($parameter["type"] == "string") {
                $parameter = array_merge($parameter, array("length" => $row["CHARACTER_MAXIMUM_LENGTH"]));
            }
            array_push($result, $parameter);
        }
        return $result;
    }

    protected function GetParametersAsList(string $dataset) : array {
        $info = $this->FetchParameters($dataset);
        $parameters = array();
        foreach ($info as $row) {
            array_push($parameters, $row["COLUMN_NAME"]);
        }
        return $parameters;
    }

    protected function FetchParameters(string $dataset) : array {
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
        $column = $this->getTimeColumn($dataset, $table);
        $statement = $this->statement_provider->GetStartDate($table, $column);
        $result = $this->ExecuteStatementAndFetchResults($statement);
        return DateUtils::SQLDateToIsoDate($result[0]["StartDate"]);
    }

    protected function getTimeColumn(string $dataset) : string {
        $key = $dataset . "_TimeColumn";
        $config = Config::getInstance();
        $column = $config->getWithDefault($key, "");
        if ($column == "") {
            throw new UnimplementedException("Config is missing the time column for the dataset: $dataset");
        }
        return $column;
    }

    public function GetStopDate(string $dataset) : string {
        $table = $this->getTableForDataset($dataset);
        $column = $this->getTimeColumn($dataset, $table);
        $statement = $this->statement_provider->GetStopDate($table, $column);
        $result = $this->ExecuteStatementAndFetchResults($statement);
        return DateUtils::SQLDateToIsoDate($result[0]["StopDate"]);
    }

    protected function getTableForDataset($dataset) : string {
        $config = Config::getInstance();
        $mapping = $config->getWithDefault("catalog_id_to_db_table", array());
        if (array_key_exists($dataset, $mapping)) {
            return $mapping[$dataset];
        } else {
            return $dataset;
        }
    }

    public function GetDatasetMetadata(string $dataset) : array {
        $startDate = $this->GetStartDate($dataset);
        $endDate = $this->GetStopDate($dataset);
        $reader = new DatasetInfoReader($dataset);
        $metadata = $reader->GetMetadata()->asArray();
        return array_merge($metadata, array("startDate" => $startDate, "stopDate" => $endDate));
    }

    public function QueryData(string $dataset, array $parameters, DateTimeImmutable $start, DateTimeImmutable $stop): array {
        $this->ValidateDatasetDates($dataset, $start, $stop);
        if (empty($parameters)) {
            $parameters = $this->GetParametersAsList($dataset);
        } else {
            $dataset_parameters = $this->GetParametersAsList($dataset);
            foreach ($parameters as $requested_param) {
                if (!in_array($requested_param, $dataset_parameters)) {
                    throw new UserInputException(HapiCode::UNKNOWN_DATASET_PARAMETER, "$requested_param is not part of dataset $dataset");
                }
            }
        }
        $table = $this->getTableForDataset($dataset);
        $time_column = $this->getTimeColumn($dataset, $table);
        $query = $this->statement_provider->QueryData($table, $time_column, $parameters, $start, $stop);
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
        $time_column = $this->getTimeColumn($dataset, $table);
        $statement = $this->statement_provider->QueryDataCount($table, $time_column, $start, $stop);
        $result = $this->ExecuteStatementAndFetchResults($statement);
        return intval($result[0]['count']);
    }
}

