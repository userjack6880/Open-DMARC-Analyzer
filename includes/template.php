<?php
/*
OpenDAnalyzer - Open Source DMARC Analyzer
ncludes/template.php
2019 - John Bradley (userjack6880)

Available at: https://github.com/userjack6880/opendanalyzer

This file is part of OpenDAnalyzer.

OpenDAnalyzer is free software: you can redistribute it and/or modify it under
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
	echo "0-&alpha;1";
}

// General Page Templates
function page_title() {
	debug (basename($_SERVER['PHP_SELF'],".php"));
	if (basename($_SERVER['PHP_SELF'],".php") == 'index') { echo "OpenDAnalyzer - Dashboard"; }
	else { echo "OpenDAnalyzer"; }
}

// Dashboard Templates
function dashboard_table_start($dateRange) {
	echo "<h2>DMARC Compliance - Since $dateRange</h2>\n";

	echo "<table id='sorted_dashboard_table' class='centered'>\n";
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

?>
