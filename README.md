# Open Report Analyzer
![Open Report Analyzer Screenshot](docs/images/oda-screenshot.jpg?raw=true)

Open Report Analyzer is an Open Source and MTA-STS TLS Report Analyzer to be used with reports that have been parsed by [Open Report Parser](https://github.com/userjack6880/Open-Report-Parser).

Open Report Analyzer was written because there didn't seem to be a full-featured self-hosted report analyzer that provided enough details to make heads or tails of a large volume of DMARC reports that come into medium to large-sized organizations. While other solutions required paid subscriptions or have part of it hosted on AWS, Additionally, it was expanded to include MTA-STS SMTP TLS reports. Open Report Analyzer will run on any webserver that supports PHP 7.4+ and MariaDB 10.5+.

Open Report Analyzer Version 2 Alpha 1 (2-α1) is an [Anomaly \<Codebase\>](https://systemanomaly.com/codebase) project by John Bradley (john@systemanomaly.com)

# Minimum Requirements
- Apache 2 or equivalent
- PHP 5 (PHP 7+ required for phpWhois)
- PHP PDO
- MariaDB 10.5 (or equivalent) *or* PostgreSQL 13.9
- **A database that is pre-populated with data from [Open Report Parser](https://github.com/userjack6880/Open-Report-Parser)**

# Dependencies

One of the following 2 packages are required to be installed.

**[kevinoo/phpWhois](https://github.com/kevinoo/phpWhois)**

It is highly recommended that you install this package using composer. Instructions are found on the package's git page. This is required, and will replace most GeoIP data if you disable the MaxMind DB reader package. This package *will* require PHP 7 or newer.

*PLEASE NOTE: if you are using the jsmitty12/phpWhois package, it does not support PHP 8 properly. Please remove it and use the newer kevinoo/phpWhois package.*

**[MaxMind DB Reader PHP API](https://github.com/maxmind/MaxMind-DB-Reader-php)**

A note on this dependency - I've tried to write the one refrence to this external project as optional as possible, and it can almost completely be configured from config.php, due to the limitation of php namespace, I haven't come across a way that won't require you to dig deeper into the code if you happen to chose a compatible library to replace this MaxMind one. If you do wish to replace this library with another compatible one, the line in question is located in `includes\functions.php`:
```php
$reader = new MaxMind\Db\Reader(GEO_DB);
```

It is highly recommended that you install this package using composer. Instructions are found on the package's git page.

You will also need the GeoLite2 database from MaxMind (or any other compatible DB). It can be obtained from [here](https://dev.maxmind.com/geoip/geoip2/geolite2/). Open Report Analyzer makes use of the GeoLite2 City database.

The MaxMind library is not distributed with this project, and is ultimately an optional feature to the project as a whole, unless you are using PHP 5.

# Setting up Open Report Analyzer

Obtaining Open Report Analyzer through `git` is probably the easiest way, in addition to doing occasional pulls to get up-to-date versions.

```
git clone https://github.com/userjack6880/Open-Report-Analyzer.git
```

Optionally, a [zip file of the latest release](https://github.com/userjack6880/Open-Report-Analyzer/releases) can be downloaded.

Once downloaded and installed in a desired directory, install either jsmitty12's phpWhois package or the MaxMind DB Reader package through composer. Rename `config.php.pub` to `config.php` and edit the configuration for your environment (see the next section on **Configuration Options** for details). Finally, run `install.php` to create the database view used by this software package.

```sh
ALLOW_INSTALL=1 php install.php
```

`install.php` should remove itself and `mysql.sql` once complete. If permissions aren't given, `install.php` may not delete those files. It is recommended to manually delete these.

# Configuration Options

**Database Options**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'openreport');
define('DB_PASS', 'password');
define('DB_NAME', 'openreport');
define('DB_PORT', '3306'); // default port 3306, 5432 for pgsql
define('DB_TYPE', 'mysql'); // supported mysql and pgsql
```

**Report Type Option**
```php
define('REPORT_TYPE', 'all'); // supported dmarc, tls, all
```

Defaults to 

**Debug Settings**
```php
define('DEBUG', 1);
```
*Not Currently Used*

**Template Settings**
```php
define('TEMPLATE','openda');
```
This will load the visual templated located `templates/`. Simply name the directory the template is located in. Do not use a trailing slash.

**Package Loader**
```php
define('AUTO_LOADER','vendor/autoload.php');
```
Should not need to change this setting unless using a non-standard composer installation.

**GeoIP2 Settings**
```php
define('GEO_ENABLE', 1);
define('GEO_DB', 'includes/geolite2.mmdb');
```
Allows you to select between jsmitty12's phpWhois package and the MaxMind DB Reader package. The default is to use the MaxMind DB Reader package, as it provides the most relevant data to the user. To fall back to the jsmitty12's phpWhois package, change the `GEO_ENABLE` option to `0`.

The second option, `GEO_DB` is used in conjunction with the MaxMind DB Reader package. The path to the MaxMind GeoIP database is relative to the root of the software package.

**Date Range**
```php
define('DATE_RANGE', '-1w');
```
Defines the standard starting date range for data presented. All pages where dates are relevant start at a certain point and end at the time the page is loaded. This option defines where that starting point is, and the increment by which that starting date is changed.

Valid date signifiers are `m`, `w`, and `d` for "month", "week", and "day".

# Docker
A docker image is provided for ease of deployment. The image is based on the official PHP 8.2 image, and is configured to use Apache 2.4.

The image is available on [GitHub Container Registry](https://github.com/userjack6880/Open-DMARC-Analyzer/pkgs/container/open-dmarc-parser).

## 2-α1
- New Version
- MTA-STS SMTP TLS report support (Issue #74)
- Name change to better include the full extent of what this project does
- PostgreSQL Validation and Fixes
- Optimizations

There is also a pre-built `docker-compose.yml` file that can be used to deploy the container and its requirements. It is recommended to use this file as a template, and modify it to your needs.

# Tested System Configurations

| OS              | HTTP          | PHP    | SQL             |
| --------------- | ------------- | ------ | --------------- |
| Debian 11.6     | Apache 2.4.56 | 8.2.5  | MariaDB 10.5.18 |
| Debian 11.6     | Apache 2.4.56 | 8.2.5  | PostgreSQL 13.9 |
| Debian 11.6     | Apache 2.4.56 | 7.4.33 | MariaDB 10.5.18 |
| Debian 11.6     | Apache 2.4.56 | 7.4.33 | PostgreSQL 13.9 |
| CentOS 7.6.1810 | Apache 2.4.6  | 5.4.16 | MariaDB 5.5.65  |

If you have a system configuration not listed, and would like to contribue this data, please [provide feedback](https://github.com/userjack6880/Open-Report-Analyzer/issues).

# Release Cycle and Versioning

At release, End of Support and End of Life will be determined based on what will be in the next version. Versioning is under the Anomaly Versioning Scheme (2022), as outlined in `VERSIONING` under `docs`.

# Support

Support will be provided as outlined in the following schedule. For more details, see `SUPPORT`.

| Version                             | Released         | End of Support   | End of Life      |
| ----------------------------------- | ---------------- | ---------------- | ---------------- |
| Version 2 Alpha 1                   | 15 May 2023      | TBD              | TBD              |
| Version 1 (Stable)                  | 15 May 2023      | 15 May 2024      | 31 December 2024 |

# Contributing

Public contributions are encouraged. Please review `CONTRIBUTING` under `docs` for contributing procedures. Additionally, please take a look at our `CODE_OF_CONDUCT`. By participating in this project you agree to abide by the Code of Conduct.

# Contributors

Primary Contributors
- John Bradley - Initial Work

Thanks to [all who contributed](https://github.com/userjack6880/Open-Report-Analyzer/graphs/contributors) and [have given feedback](https://github.com/userjack6880/Open-Report-Analyzer/issues?q=is%3Aissue).

# Licenses and Copyrights

Copyright © 2023 John Bradley (userjack6880). Open Report Analyzer is released under GNU GPLv3. See `LICENSE`.