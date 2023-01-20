CREATE DATABASE HapiTestDatabase;

USE HapiTestDatabase;

CREATE TABLE TestDataset (
    id INT,
    string_data VARCHAR(500),
    decimal_data DECIMAL(5,2),
    float_data FLOAT,
    timestamp TIMESTAMP(3)
);

INSERT INTO TestDataset(string_data, decimal_data, float_data, timestamp)
VALUES ("I'm a string", 5.01, 1.23456, '2022-01-01 05:00:00.123'),
       ("This is a string", 7.01, 5.4321, '2022-01-15 10:00:00.456'),
       ("Hello, world", 1.01, 999.999, '2022-01-31 00:00:00.789');

CREATE USER 'HapiTestUser'@'localhost' IDENTIFIED BY 'password';

GRANT SELECT ON HapiTestDatabase.* TO 'HapiTestUser'@'localhost';