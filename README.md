# Open DMARC Analyzer

This is Open DMARC Analyzer version 0 alpha-3 (0-α3) by John Bradley (john@systemanomaly.com). Open DMARC Analyzer is an Open Source DMARC Report Analyzer to be used with DMARC reports that have been parsed by [John Levine's rrdmarc script](http://www.taugh.com/rddmarc/) or [techsneeze's dmarcts-report-parser](https://github.com/techsneeze/dmarcts-report-parser).

Open DMARC Analyzer was written because there didn't seem to be a full-featured self-hosted report analyzer that provided enough details to make heads or tails of a large volume of DMARC reports that come into medium to large-sized organizations. While other solutions required paid subscriptions or have part of it hosted on AWS, Open DMARC Analyzer will run on any webserver that supports PHP 5.4+ and MySQL 15.1+.

# Changelog

##0-α3

- Fixed issue where a domain will be listed to have a non-zero volume, but on the domain page will have no reports. This page now properly shows all reports related to a single domain.
- Improved the accuracy of the DMARC compliance graph. No longer does it take the larger of the two alignments, but instead counts a message as complaint if it is either DKIM or SPF aligned.
- Moved away from mysqli to utilize PDO instead.

##0-α2

- Code now has most useful features now.

##0-α1

- Project started. It's absolutely terrible and nobody should use this.

# License

Open DMARC Analyzer is released under GNU GPLv3. See LICENSE.

# Credits

Open DMARC Analyzer contains componenets from other software developers:

gs_sortable.js v1.9 is copyright 2007 - 2012 Gennadiy Shvets, released under GNU GPLv3
