# hapi-server-php
This software was written for use in Helioviewer's API for querying solar image data.
The intent is that it is a generic HAPI server connected to SQL database as the data backend.
Ideally only a configuration file specifying the databases, columns, and descriptions should be required to spin up a hapi server.

## Definitions
HAPI uses its own set of definitions for data and datasets.
Since this software is a database-backed design, each definitions translates this way:
- *catalog* - Database
- *dataset* - Table
- *record* - Table row
- *parameter* - Table Column

## Endpoints
These endpoints are supported by the generic interface

### Capabilities
The server supports `csv` and `json`.
`Binary` capability is not supported since some database columns may have a variable size.

### About
Returns server metadata specified in the configuration file

### Catalog
Returns the list of available databases, specified in the configuration file

### Info
Returns database column information.
Descriptions must be defined manually in the configuration file.

### Data
Returns rows of a database formatted in `csv` or `json` as requested.
