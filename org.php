<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
org.php
2020 - John Bradley (userjack6880)

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
	debug("Using GET date value: ".$_GET['range']);
	$dateRange = $_GET['range']; 
} else { 
	debug("Using default date value: ".DATE_RANGE);
	$dateRange = DATE_RANGE; 
}

page_header();

?>

<script>
	var TSort_Data = new Array('orgs_report','s','s','s','s');
	var TSort_Cookie = 'orgs_reports';
	tsRegister();

</script>

<?php

// Org Reports Table
org_report($pdo, $dateRange, $_GET['org'], $_GET['domain']);

// Footer

page_footer();

$pdo = null;

debug("\o/");

?>
