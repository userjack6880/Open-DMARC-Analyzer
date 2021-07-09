<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
host.php
2021 - John Bradley (userjack6880)

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
include_once 'includes.php';

// Get Date Stuff
$pdo = dbConn();
if (!empty($_GET['range'])) { 
	debug("Using GET date value: ".htmlspecialchars($_GET['range']));
	$dateRange = htmlspecialchars($_GET['range']);
} else { 
	debug("Using default date value: ".DATE_RANGE);
	$dateRange = DATE_RANGE; 
}

page_header();

?>

<script>
	var TSort_Data = new Array('senders_report','s','s','s');
	var TSort_Cookie = 'senders_reports';
	tsRegister();

</script>

<?php
// GeoIP Info
senders_report_info(htmlspecialchars($_GET['ip']));

// Report Table
senders_report_table($pdo, $dateRange, htmlspecialchars($_GET['domain']), htmlspecialchars($_GET['ip']));

// Footer

page_footer();

$pdo = null;

debug("\o/");

?>
