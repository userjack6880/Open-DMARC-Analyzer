# Open DMARC Analyzer

This is Open DMARC Analyzer version 0 alpha-5 (0-α5) by John Bradley (john@systemanomaly.com). Open DMARC Analyzer is an Open Source DMARC Report Analyzer to be used with DMARC reports that have been parsed by [John Levine's rrdmarc script](http://www.taugh.com/rddmarc/) or [techsneeze's dmarcts-report-parser](https://github.com/techsneeze/dmarcts-report-parser).

Open DMARC Analyzer was written because there didn't seem to be a full-featured self-hosted report analyzer that provided enough details to make heads or tails of a large volume of DMARC reports that come into medium to large-sized organizations. While other solutions required paid subscriptions or have part of it hosted on AWS, Open DMARC Analyzer will run on any webserver that supports PHP 5.4+ and MySQL 15.1+.

# Dependencies

## Optional - [MaxMind DB Reader PHP API](https://github.com/maxmind/MaxMind-DB-Reader-php)
A note on this dependency - I've tried to write the one refrence to this external project as optional as possible, and it can almost completely be configured from config.php, due to the limitation of php namespace, I haven't come across a way that won't require you to dig deeper into the code if you happen to chose a compatible library to replace this MaxMind one. If you do wish to replace this library with another compatible one, the line in question is located in `includes\functions.php`:
```php
$reader = new MaxMind\Db\Reader(GEO_DB);
```

You will also need the GeoLite2 database from MaxMind (or any other compatible DB). It can be obtained from [here](https://dev.maxmind.com/geoip/geoip2/geolite2/). Open DMARC Analyzer makes use of the GeoLite2 City database.

The MaxMind library is not distributed with this project, and is ultimately an optional feature to the project as a whole.

# Changelog

## 0-α5
- Fixed behavior of the control that changes the start of the display period to take in account the default date range configured in `config.php`.
- Created the beginnings of the org reports page. It's kinda basic right now.
- Added a bit of color to the DKIM and SPF result columns.
- Added optional GeoIP2 Information on `hosts.php`.
- Fixed a bunch of little things here and there, and added a few comments in areas that needed it.
- Many thanks to Timo N. for making excellent suggestions on improving this project, pointing out things I would've overlooked long into it.

## 0-α4
- Added a control to change the start of the display period in 1 week steps.
- Added disposition control to display a single disposition only.
- Added a sender report to show senders for a single domain or what domains a single sender sent as.
- Some code cleanup.

## 0-α3

- Fixed issue where a domain will be listed to have a non-zero volume, but on the domain page will have no reports. This page now properly shows all reports related to a single domain.
- Improved the accuracy of the DMARC compliance graph. No longer does it take the larger of the two alignments, but instead counts a message as complaint if it is either DKIM or SPF aligned.
- Moved away from mysqli to utilize PDO instead.

## 0-α2

- Code now has most useful features now.

## 0-α1

- Project started. It's absolutely terrible and nobody should use this.

# License

Open DMARC Analyzer is released under GNU GPLv3. See LICENSE.

# Credits

Open DMARC Analyzer contains componenets from other software developers:

gs_sortable.js v1.9 is copyright 2007 - 2012 Gennadiy Shvets, released under GNU GPLv3
