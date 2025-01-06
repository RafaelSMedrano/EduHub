Changelog
=========

2.1.1 (March 1, 2024)
--------------------
- Fix: If a user does not have group permissions to view analytics, but is allowed to view a user's statistics (e.g. their own profile), the charts will not load.
- Enh: Update `GeoLite2-Country.mmdb` to version 2024.03.01
- Enh: new logo

2.1.0 (November 10, 2023)
--------------------
- Enh: Add reported content charts (daily count)
- Enh: Add charts to user statistics
- Cgh: The reported content count will start the date where the module is installed or updated to this version
- Enh: Replace the unmaintained [Which Browser Parser](https://github.com/WhichBrowser/Parser-PHP) library with [PHP User Agent Parser](https://github.com/donatj/PhpUserAgent)

2.0.2 (July 1, 2023)
--------------------
- Fix: The mention "{nbMembers} members visited the space(s) since the {startDate}" must be moved from the number of members to the number of visitors chart
- Fix: Don't display the total in charts about the number of accounts or members
- Enh: Sort user's browsers list by last visit

2.0.1 (July 1, 2023)
--------------------
- Fix: If friendship or messager module is disabled, display the chart dedicated to the enabled module instead of a common char

2.0 (June 30, 2023)
--------------------
- Enh: Added "total" statistics (on the top left of the charts)
- Enh: Added a setting allowing to choose the chart type (line,area, column or bar)
- Enh: Added "private messages", "friendship" (charts + counts) and "reported content" (counts) statistics
- Enh: Moved stats renders to widgets which can be used by third party modules
- Enh: Added module for users (adds statistics on the profile)
- Enh: Updated description of the module (mainly default permissions)
- Enh: Added admin menu at the top to keep full width
- Chg: Minimal Humhub version is now 1.12 (it was only tested on 1.13)
- Fix: Date format from `G:i:s` to `H:i:s` to reflect Humhub core fix https://github.com/humhub/humhub/pull/6264

1.0.1 (April 25, 2023)
--------------------
- Fix: Added PHP 8.2 compatibility for Humhub 1.14 (https://github.com/WhichBrowser/Parser-PHP/pull/673 and https://github.com/WhichBrowser/Parser-PHP/issues/676)

1.0 (April 6, 2023)
--------------------
- Enh: First release
