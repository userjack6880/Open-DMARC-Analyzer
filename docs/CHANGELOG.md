# Changelog

## 0-α9.1

## 0-α9
- Bugfixes
- Version Update to begin final feature implementation for Version 1 Feature Complete

## 0-α8.2
- Added `SECURITY` and `SUPPORT` and updated relevant documentation to reference these.
- Determined date for next Alpha release.
- Added a basic installer

## 0-α8.1

- Added `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, pull request template, issue templates, and organized documents into docs folder.
- Added basic installation script to add `report_stats` view from file and attempt to cleanup after itself.
- Further fleshed out `README.md`
- Improved compatibility with older SQL databases that do not support `INET6_ATON` or `INET6_NTOA`.
- Added SQL error output.

## 0-α8

- Rewrite of ODA for performance and visual improvements and feature simplification.
- Begin Documentation Process

## 0-α7.3

- README.md updates

## 0-α7.2

- Added function to change date range increment based on default date range setting.

## 0-α7.1

- PDO Input Sanitization (thanks Matthäus Wander)
- Added IPv6 Support (thanks Matthäus Wander)

## 0-α7
- No longer counts forwarded messages that aren't quarantined or rejected against compliance.
- Minor PDO query optimisation.

## 0-α6
- Sort index by DMARC policy added.
- Link to domain page from senders page.
- Fixed policy listed on index.
- Added Organization Output

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
