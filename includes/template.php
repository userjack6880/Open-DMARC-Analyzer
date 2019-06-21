<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
ncludes/template.php
2019 - John Bradley (userjack6880)

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
	echo "0-&alpha;3";
}

// General Page Templates
function page_title() {
	debug (basename($_SERVER['PHP_SELF'],".php"));
	if (basename($_SERVER['PHP_SELF'],".php") == 'index') { echo "Open DMARC Analyzer - Dashboard"; }
	else { echo "Open DMARC Analyzer"; }
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
	echo "<h3><a id='reports'></a>Reports</h2>\n";

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
	echo "<h3><a id='report_detail'></a>Report Details</h2>\n";

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
	echo "<h3><a id='report_detail'></a>Report Details</h2>\n";

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
?>
