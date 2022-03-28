<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
index.php
2022 - John Bradley (userjack6880)

Available at: https://github.com/userjack6880/Open DMARC Analyzer

This file is part of Open DMARC Analyzer.

Open DMARC Analyzer is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// Includes
include_once 'new_includes.php';

// Pull in URI Gets

// Range ----------------------------------------------------------------------
if (!empty($_GET['range'])) {
	$dateRange = htmlspecialchars($_GET['range']);
}
elseif (isset($_POST['range'])) {
	$dateRange = htmlspecialchars($_POST['range']);
}
else {
	$dateRange = DATE_RANGE;
}

// Page -----------------------------------------------------------------------
if (isset($_GET['page'])) {
	$page = htmlspecialchars($_GET['page']);
}
elseif (isset($_POST['page'])) {
	$page = htmlspecialchars($_POST['page']);
}
else {
	$page = "index";
}

// Domain ---------------------------------------------------------------------
if (isset($_GET['domain'])) {
	$domain = htmlspecialchars($_GET['domain']);
}
elseif (isset($_POST['domain'])) {
	$domain = htmlspecialchars($_POST['domain']);
}
else {
	$domain = "all";
}

// IPs ------------------------------------------------------------------------
if (isset($_GET['ip'])) {
		$ip = htmlspecialchars($_GET['ip']);
}
elseif (isset($_POST['ip'])) {
	$ip = htmlspecialchars($_POST['ip']);
}
else {
	$ip = '';
}

// End URI gets

// Page Header
page_header($page, $domain, $dateRange);

if ($page == "index") {
	dashboard($dateRange, $domain);
}
elseif ($page == "sender") {
	senderDashboard($dateRange, $domain, $ip);
}
else {
	echo "<h1>Invalid Page</h1>\n";
}

// Page Footer
page_footer();

?>
