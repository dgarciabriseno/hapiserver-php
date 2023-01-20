# hapiserver-php
This software is intended to provide HAPI server capabilities to a dataset stored in a MySQL database.
(Other SQL databases may work, though will need some minor tinkering).
The intent is that it is a generic HAPI Front End that can be configured to serve any SQL table.
To use, simply edit the example configuration file to specify database credentials, columns, and server metadata (contact info, ownership, etc).

## Definitions
HAPI uses its own set of definitions for data and datasets.
Since this software is a database-backed design, each definitions translates this way:
- *catalog* - Database
- *dataset* - Table
- *record* - Table row
- *parameter* - Table Column


## What is supported?
- Use a config file to specify server metadata returned by `/hapi/about`
- Define your database details and dataset information in a config file and have it automatically served through the `catalog`, `info`, and `data` endpoints.
- Easily spin up a HAPI server in front of an existing SQL database.
- Data returned in `json` or `csv` formats.
- Limiting requests per-dataset based on maxRequestDuration

## Limitations
- The current implementation does not support *Additional Metadata* in the info endpoint.
- Arrays and bins as a data type are not supported
- Fill is currently not supported, `fill: null` is returned
- Field labels are not supported in the info endpoint yet.
- If each of your data points is a scalar value, then this will work for you.
- Request limiting with error 1408 - too much data requested.
- HAPI error codes are not returned in the HTTP status, only in the response body.
- No HAPI landing page

# Installation and Setup
- Setup your webserver to serve `public/index.php`
- You need a rewrite rule so that all `/hapi/endpoints` routes get sent to index.php

Example for apache2:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* index.php
```

- Copy `config.example.ini` to `config.ini` and enter metadata about your server.
  You should review every line in the config file since it all affects the behavior of your server.
  The configuration is meant to be self-explanatory. If it's not, please open an issue so I can clarify what to set.
