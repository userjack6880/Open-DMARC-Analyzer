<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
ncludes/template.php
2021 - John Bradley (userjack6880)

Available at: https://github.com/userjack6880/Open-DMARC-Analyzer

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

// Versioning
function oda_version() {
	echo "0-&alpha;7.1";
}

// General Page Templates
function page_title() {
	debug (basename($_SERVER['PHP_SELF'],".php"));
	if (basename($_SERVER['PHP_SELF'],".php") == 'index') { echo "Open DMARC Analyzer - Dashboard"; }
	elseif (basename($_SERVER['PHP_SELF'],".php") == 'domain') { echo "Open DMARC Analyzer - Domain Details for ".htmlspecialchars($_GET['domain']); }
	else { echo "Open DMARC Analyzer"; }
}

// Control Bar
function control_bar() {
	
	$basename = basename($_SERVER['PHP_SELF'],".php");

	if ($basename == 'index' || $basename == 'domain' || $basename == 'org') { echo "<div id=controlbar>\n"; }
	else { return; } // we don't need to actually perform any of this logic if we don't need to

	// Range Control
	if (isset($_GET['range'])) { 
		$dateRange = htmlspecialchars($_GET['range']);

	  preg_match('/(\d+)(\w+)/', $dateRange, $match);

		if ($match[1] > '1') {	
			$date = ($match[1]-1)."w";
			$laterStartURL = $_SERVER['PHP_SELF']."?range=$date";
		} else { $laterStartURL = $_SERVER['PHP_SELF']."?range=$dateRange"; }

		$date = ($match[1]+1)."w";
		$earlierStartURL = $_SERVER['PHP_SELF']."?range=$date";

		$rangeOption = "&range=".htmlspecialchars($_GET['range']);
	} else {
		preg_match('/\-(\d+)\s(\w+)/', DATE_RANGE, $defRange);

		$date = ($defRange[1]+1)."w";

		$earlierStartURL = $_SERVER['PHP_SELF']."?range=$date";
		$laterStartURL = $_SERVER['PHP_SELF'];

		$rangeOption = '';
	}

	// Disposition Options
	if (isset($_GET['disp'])) { $dispOption = "&disp=".htmlspecialchars($_GET['disp']); }
	else { $dispOption = ''; }

	// Domain Options
	if (isset($_GET['domain'])) { $domainOption = "&domain=".htmlspecialchars($_GET['domain']); }
	else { $domainOption = ''; }

	// Org Options
	if (isset($_GET['org'])) { $orgOption = "&org=".htmlspecialchars($_GET['org']); }
	else { $orgOption = ''; }

	// URL Generate
	$earlierStartURL = $earlierStartURL.$dispOption.$domainOption.$orgOption;
	$laterStartURL = $laterStartURL.$dispOption.$domainOption.$orgOption;

	$dispNoneURL   = $basename.".php?disp=none".$rangeOption.$domainOption;
	$dispQuarURL   = $basename.".php?disp=quarantine".$rangeOption.$domainOption;
	$dispRejectURL = $basename.".php?disp=reject".$rangeOption.$domainOption;

	if ($basename == 'index' || $basename == 'domain' || $basename == 'org') {
		echo "\t&#91; Range Start: <a href='$earlierStartURL'>&larr; 1 Week</a> | <a href='$laterStartURL'>1 Week &rarr;</a> &#93;</br>\n"; 
	}
	if ($basename == 'index' || $basename == 'domain') {
		echo "\t&#91; Disposition: <a href='$dispNoneURL'>none</a> | <a href='$dispQuarURL'>quarantine</a> | <a href='$dispRejectURL'>reject</a> &#93;</br>\n";
	}

	if ($basename == 'index' || $basename == 'domain' || $basename == 'org') {	echo "</div>\n"; }
}

// Dashboard Templates
function dashboard_dmarc_table_start($dateRange) {
	echo "<h3><a id='compliance'></a>DMARC Compliance - Since $dateRange</h3>\n";

	echo "<table id='compliance_table' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Domain</th>\n";
	echo "\t\t<th>Volume</th>\n";
	echo "\t\t<th width='150px'>DMARC Policy</th>\n";
	echo "\t\t<th width='15%'>DMARC Compliance</th>\n";
	echo "\t\t<th width='15%'>DKIM Alignment</th>\n";
	echo "\t\t<th width='15%'>SPF Alignment</th>\n";
	echo "\t</tr>\n";
	echo "\t</thead>\n";
}

// Domain Reports DKIM Table
function domain_reports_dkim_table_start() {
	echo "<h3><a id='compliance'></a>DMARC Compliance</h3>\n";

	echo "<table id='compliance_table' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Domain</th>\n";
	echo "\t\t<th>Volume</th>\n";
	echo "\t\t<th width='150px'>DMARC Policy</th>\n";
	echo "\t\t<th width='15%'>DMARC Compliance</th>\n";
	echo "\t\t<th width='15%'>DKIM Alignment</th>\n";
	echo "\t\t<th width='15%'>SPF Alignment</th>\n";
	echo "\t</tr>\n";
	echo "\t</thead>\n";
}

// Domain Reports Table
function domain_reports_table_start() {
	echo "<h3><a id='reports'></a>Reports</h3>\n";

	echo "<table id='domain_reports' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Date Range</th>\n";
	echo "\t\t<th>Reporting Org</th>\n";
	echo "\t\t<th>Report ID</th>\n";
	echo "\t<tr>\n";
	echo "\t</thead>\n";
}

// Individual Reports Table
function reports_table_start() {
	echo "<h3><a id='report_detail'></a>Report Details</h3>\n";

	echo "<table id='dmarc_reports' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Report ID</th>\n";
	echo "\t\t<th>Sender IP</th>\n";
	echo "\t\t<th>Sender Domain</th>\n";
	echo "\t\t<th>Message Count</th>\n";
	echo "\t\t<th>Disposition</th>\n";
	echo "\t\t<th>Reason</th>\n";
	echo "\t\t<th>DKIM Domain</th>\n";
	echo "\t\t<th>DKIM Result</th>\n";
	echo "\t\t<th>SPF Domain</th>\n";
	echo "\t\t<th>SPF Result</th>\n";
	echo "\t</tr>\n";
	echo "\t</thead>\n";
}

// Single Individual Reports Table
function single_report_table_start() {
	echo "<h3><a id='report_detail'></a>Report Details</h3>\n";

	echo "<table id='dmarc_reports' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Sender IP</th>\n";
	echo "\t\t<th>Sender Domain</th>\n";
	echo "\t\t<th>Message Count</th>\n";
	echo "\t\t<th>Disposition</th>\n";
	echo "\t\t<th>Reason</th>\n";
	echo "\t\t<th>DKIM Domain</th>\n";
	echo "\t\t<th>DKIM Result</th>\n";
	echo "\t\t<th>SPF Domain</th>\n";
	echo "\t\t<th>SPF Result</th>\n";
	echo "\t</tr>\n";
	echo "\t</thead>\n";
}

// Senders Report Table
function senders_report_table_start() {
	echo "<h3><a id='senders'></a>Sender's Report</h3>\n";

	echo "<table id='senders_report' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Sender IP</th>\n";
	echo "\t\t<th>Sender Domain</th>\n";
	echo "\t\t<th>Sent As</th>\n";
	echo "\t</tr>\n";
	echo "\t</thead>\n";
}

function org_report_table_start($org, $domain, $dateRange) {
	echo "<h3><a id='orgs'></a>Org Reports from $org for $domain - Since $dateRange</h3>\n";

	echo "<table id='orgs_report' class='centered'>\n";
	echo "\t<thead>\n";
	echo "\t<tr>\n";
	echo "\t\t<th>Report ID</th>\n";
	echo "\t\t<th>Domain</th>\n";
	echo "\t\t<th>Reporter Email</th>\n";
	echo "\t\t<th>Extra Contact</th>\n";
	echo "\t</tr>\n";
	echo "\t</thead>\n";
}
?>
