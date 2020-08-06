# Open DMARC Analyzer

This is Open DMARC Analyzer version 0 alpha-7 (0-α7) by John Bradley (john@systemanomaly.com). Open DMARC Analyzer is an Open Source DMARC Report Analyzer to be used with DMARC reports that have been parsed by [John Levine's rrdmarc script](http://www.taugh.com/rddmarc/) or [techsneeze's dmarcts-report-parser](https://github.com/techsneeze/dmarcts-report-parser).

Open DMARC Analyzer was written because there didn't seem to be a full-featured self-hosted report analyzer that provided enough details to make heads or tails of a large volume of DMARC reports that come into medium to large-sized organizations. While other solutions required paid subscriptions or have part of it hosted on AWS, Open DMARC Analyzer will run on any webserver that supports PHP 5.4+ and MySQL 15.1+.

# Dependencies

## Required - [jsmitty12/phpWhois](https://github.com/jsmitty12/phpWhois/)
It is highly recommended that you install this package using composer. Instructions are found on the package's git page. This is required, and will replace most GeoIP data if you disable the MaxMind DB reader package.

## Optional - [MaxMind DB Reader PHP API](https://github.com/maxmind/MaxMind-DB-Reader-php)
A note on this dependency - I've tried to write the one refrence to this external project as optional as possible, and it can almost completely be configured from config.php, due to the limitation of php namespace, I haven't come across a way that won't require you to dig deeper into the code if you happen to chose a compatible library to replace this MaxMind one. If you do wish to replace this library with another compatible one, the line in question is located in `includes\functions.php`:
```php
$reader = new MaxMind\Db\Reader(GEO_DB);
```

It is highly recommended that you install this package using composer. Instructions are found on the package's git page.

You will also need the GeoLite2 database from MaxMind (or any other compatible DB). It can be obtained from [here](https://dev.maxmind.com/geoip/geoip2/geolite2/). Open DMARC Analyzer makes use of the GeoLite2 City database.

The MaxMind library is not distributed with this project, and is ultimately an optional feature to the project as a whole.

# Latest Changes

## 0-α7

- Sort index by DMARC policy added.
- Link to domain page from senders page.
- Fixed policy listed on index.
- Added Organization Output.

See `CHANGELOG.md` for full details of all changes.

# License

Open DMARC Analyzer is released under GNU GPLv3. See LICENSE.

# Credits

Open DMARC Analyzer contains componenets from other software developers:

gs_sortable.js v1.9 is copyright 2007 - 2012 Gennadiy Shvets, released under GNU GPLv3
