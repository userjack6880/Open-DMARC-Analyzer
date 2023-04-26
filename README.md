# Open DMARC Analyzer
![Open DMARC Analyzer Screenshot](docs/images/oda-screenshot.jpg?raw=true)

Open DMARC Analyzer is an Open Source DMARC Report Analyzer to be used with DMARC reports that have been parsed by [Open Report Parser](https://github.com/userjack6880/Open-Report-Parser).

Open DMARC Analyzer was written because there didn't seem to be a full-featured self-hosted report analyzer that provided enough details to make heads or tails of a large volume of DMARC reports that come into medium to large-sized organizations. While other solutions required paid subscriptions or have part of it hosted on AWS, Open DMARC Analyzer will run on any webserver that supports PHP 7.4+ and MySQL 15.1+.

Open DMARC Analyzer Version 1 Beta 4 (1-β4) is an [Anomaly \<Codebase\>](https://systemanomaly.com/codebase) project by John Bradley (john@systemanomaly.com)

# Minimum Requirements
- Apache 2 or equivalent
- PHP 5 (PHP 7 required for phpWhois)
- PHP PDO
- MySQL 15.1 or equivalent
- **A database that is pre-populated with data from [Open Report Parser](https://github.com/userjack6880/Open-Report-Parser)**

# Dependencies

One of the following 2 packages are required to be installed.

**[jsmitty12/phpWhois](https://github.com/jsmitty12/phpWhois/)**

It is highly recommended that you install this package using composer. Instructions are found on the package's git page. This is required, and will replace most GeoIP data if you disable the MaxMind DB reader package. This package *will* require PHP 7.

**[MaxMind DB Reader PHP API](https://github.com/maxmind/MaxMind-DB-Reader-php)**

A note on this dependency - I've tried to write the one refrence to this external project as optional as possible, and it can almost completely be configured from config.php, due to the limitation of php namespace, I haven't come across a way that won't require you to dig deeper into the code if you happen to chose a compatible library to replace this MaxMind one. If you do wish to replace this library with another compatible one, the line in question is located in `includes\functions.php`:
```php
$reader = new MaxMind\Db\Reader(GEO_DB);
```

It is highly recommended that you install this package using composer. Instructions are found on the package's git page.

You will also need the GeoLite2 database from MaxMind (or any other compatible DB). It can be obtained from [here](https://dev.maxmind.com/geoip/geoip2/geolite2/). Open DMARC Analyzer makes use of the GeoLite2 City database.

The MaxMind library is not distributed with this project, and is ultimately an optional feature to the project as a whole, unless you are using PHP 5.

# Setting up Open DMARC Analyzer

Obtaining Open DMARC Analyzer through `git` is probably the easiest way, in addition to doing occasional pulls to get up-to-date versions.

```
git clone https://github.com/userjack6880/Open-DMARC-Analyzer.git
```

Optionally, a [zip file of the latest release](https://github.com/userjack6880/Open-DMARC-Analyzer/releases) can be downloaded.

Once downloaded and installed in a desired directory, install either jsmitty12's phpWhois package or the MaxMind DB Reader package through composer. Rename `config.php.pub` to `config.php` and edit the configuration for your environment (see the next section on **Configuration Options** for details). Finally, run `install.php` to create the database view used by this software package.

`install.php` should remove itself and `mysql.sql` once complete. If permissions aren't given, `install.php` may not delete those files. It is recommended to manually delete these.

# Configuration Options

**Database Options**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'dmarc');
define('DB_PASS', 'password');
define('DB_NAME', 'dmarc');
define('DB_PORT', '3306'); // default port 3306
```

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

# Latest Changes

## 1-β4
- PostgresSQL validation and fixes backported from future Version 2 Alpha 1 branch.

See `CHANGELOG` under `docs` for full details of all changes.

# Tested System Configurations

| OS              | HTTP          | PHP    | SQL             |
| --------------- | ------------- | ------ | --------------- |
| Debian 11.6     | Apache 2.4.56 | 8.2.5  | MariaDB 10.5.18 |
| Debian 11.6     | Apache 2.4.56 | 8.2.5  | PostgreSQL 13.9 |
| Debian 11.6     | Apache 2.4.56 | 7.4.33 | MariaDB 10.5.18 |
| Debian 11.6     | Apache 2.4.56 | 7.4.33 | PostgreSQL 13.9 |
| CentOS 7.6.1810 | Apache 2.4.6  | 5.4.16 | MariaDB 5.5.65  |

If you have a system configuration not listed, and would like to contribue this data, please [provide feedback](https://github.com/userjack6880/Open-Dmarc-Analyzer/issues).

# Release Cycle and Versioning

This project regular release cycle is not yet determined. Versioning is under the Anomaly Versioning Scheme (2022), as outlined in `VERSIONING` under `docs`.

# Support

Support will be provided as outlined in the following schedule. For more details, see `SUPPORT`.

| Version                             | Support Level    | Released         | End of Support   | End of Life      |
| ----------------------------------- | ---------------- | ---------------- | ---------------- | ---------------- |
| Version 1 Beta 4                    | Full Support     | 26 April 2023    | 1-rc1 Release    | TBD              |
| Version 1 Beta 3                    | Critical Support | 19 April 2023    | 26 April 2023    | 1-rc1 Release    |
| Version 1 Beta 2                    | End of Life      | 29 November 2022 | 19 April 2023    | 26 April 2023    |

# Contributing

Public contributions are encouraged. Please review `CONTRIBUTING` under `docs` for contributing procedures. Additionally, please take a look at our `CODE_OF_CONDUCT`. By participating in this project you agree to abide by the Code of Conduct.

# Contributors

Primary Contributors
- John Bradley - Initial Work

Thanks to [all who contributed](https://github.com/userjack6880/Open-DMARC-Analyzer/graphs/contributors) and [have given feedback](https://github.com/userjack6880/Open-DMARC-Analyzer/issues?q=is%3Aissue).

# Licenses and Copyrights

Copyright © 2023 John Bradley (userjack6880). Open DMARC Analyzer is released under GNU GPLv3. See `LICENSE`.