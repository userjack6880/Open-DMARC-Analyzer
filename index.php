<?php
/* ----------------------------------------------------------------------------

Open Report Analyzer
Copyright (C) 2023 - John Bradley (userjack6880)

index.php
  the all encompassing page that is always loaded
  how it is displayed changes with context

Available at: https://github.com/userjack6880/Open-Report-Analyzer

This file is part of Open Report Analyzer.

-------------------------------------------------------------------------------

Open Report Analyzer is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program.  If not, see <https://www.gnu.org/licenses/>.

---------------------------------------------------------------------------- */

// Includes
include_once 'includes.php';

// Pull in URI Gets

$dateRange  = getArg('range',DATE_RANGE);
$page       = getArg('page','index');
$domain     = getArg('domain','all');
$ip         = getArg('ip','');
$mx         = getArg('mx','');
$report     = getArg('report','');

// End URI gets

// Page Header
page_header($page, $domain, $dateRange, $ip, $mx);

if ($page == "index") {
  dashboard($dateRange, $domain);
}
elseif ($page == "sender") {
  senderDashboard($dateRange, $domain, $ip);
}
elseif ($page == "reciever") {
  recieverDashboard($dateRange, $domain, $mx);
}
elseif ($page == "report") {
  reportDashboard($report);
}
else {
  echo "<h1>Invalid Page</h1>\n";
}

// Page Footer
page_footer();

?>
