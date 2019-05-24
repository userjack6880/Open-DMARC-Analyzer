<?php
/*
OpenDAnalyzer - Open Source DMARC	Analyzer
include.php
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

// MySQLi Functions //

function dbConn() {
	debug("Connecting to MySQL");
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
	if ($mysqli->connect_errno) {
		die("\nCould not connect to $db: ".$mysqli->connect_error."\n");
	}
	debug("Connection Successful\n");
	return $mysqli;
}

function report_data($mysqli, $dateRange = DATE_RANGE) {
	$startDate = start_date($dateRange);
	debug("Start Date: $startDate");
	$query = "SELECT * FROM `report` WHERE `mindate` BETWEEN '$startDate' AND NOW()";
	$result = $mysqli->query($query);
	$rows = [];
	while ($row = $result->fetch_array()) { array_push($rows, $row); }
	$result->close();
	return $rows;
}

// Debug Functions //
function debug($string, $debugLevel = DEBUG) {
	if ($debugLevel == 1) { echo "$string\n"; } // Basic CLI Debug Level
}

// Dashboard //

function dashboard($mysqli, $dateRange = DATE_RANGE) {
	debug("\nCompliance Overview\n");

	// pull the serial number of all reports within date range
	$rData = report_data($mysqli, $dateRange);

	$counts = [];
	// using said serial numbers, pull all rpt record data
	// run through each row, and count total emails, the alignment counts, and results
	foreach ($rData as $data) {
		debug("Serial: ".$data['serial']);
		$query = "SELECT * from `rptrecord` WHERE `serial` = ".$data['serial'];
		$result = $mysqli->query($query);
		while ($row = $result->fetch_array()) {
			$id = $row['identifier_hfrom'];

			if (empty($counts[$id])) { 
				$counts[$id] = new stdClass(); 
				$counts[$id]->hfrom      = $row['identifier_hfrom'];
				$counts[$id]->rcount     = 0;
				$counts[$id]->numReport  = 0;
				$counts[$id]->resultDKIM = 0;
				$counts[$id]->resultSPF  = 0;
				$counts[$id]->alignDKIM  = 0;
				$counts[$id]->alignSPF   = 0;
				$counts[$id]->policy     = $data['policy_p'];
				$counts[$id]->policyPct  = $data['policy_pct'];
				$counts[$id]->reports    = [];
				debug("New Domain: ".$counts[$id]->hfrom);
			}
			$counts[$id]->numReport++;
			$counts[$id]->rcount += $row['rcount'];
			if ($row['dkimresult'] == 'pass')  { $counts[$id]->resultDKIM++; }
			if ($row['spfresult'] == 'pass')   { $counts[$id]->resultSPF++; }
			if ($row['dkim_align'] == 'pass' ) { $counts[$id]->alignDKIM++; }
			if ($row['spf_align'] == 'pass')   { $counts[$id]->alignSPF++; }
			if (empty($counts[$id]->reports[$data['org']])) { $counts[$id]->reports[$data['org']] = 0; }
			$counts[$id]->reports[$data['org']]++;
		}
	}

	debug("\nCount Object Values");

	// Now we calculate the volume of mail, the DMARC compliance, and the verification percentages
	// and each organization and number of reports... and print it out into a table

	foreach ($counts as $count) {
		debug(var_dump(get_object_vars($count)));
		echo "From Domain: ".$count->hfrom."\n";
		echo "Volume: ".$count->rcount."\n";

		$alignDKIM = 100 * ($count->alignDKIM / $count->numReport);
		$alignSPF = 100 * ($count->alignSPF / $count->numReport);
		$DKIMpass = 100 * ($count->resultDKIM / $count->numReport);
		$SPFpass = 100 * ($count->resultSPF / $count->numReport);
		$compliance = max($alignDKIM, $alignSPF);

		debug("DMARC Compliance: $compliance");
		debug("DMARC Policy: ".$count->policyPct." ".$count->policy);
		debug("DKIM Pass: $DKIMpass");
		debug("DKIM Align: $alignDKIM");
		debug("SPF Pass: $SPFpass");
		debug("SPF Align: $alignSPF");

		foreach ($count->reports as $org => $report) {
			debug("Report Org: $org");
			debug("Num Reports: $report");
		}
	}
}

// Misc Functions //
function format_date($date) {
	return date("Y-m-d H:i:s",$date);
}

function date_range($dateRange = DATE_RANGE) {
	preg_match('/(\d+)(\w+)/', $dateRange, $match);
	$range = "-$match[1] ";

	// work out if it's week, month, or day
	if ($match[2] == 'w') { $range += 'week'; }
	if ($match[2] == 'm') { $range += 'month'; }
	if ($match[2] == 'y') { $range += 'year'; }

	return $range;
}

function start_date($dateRange = DATE_RANGE) {
	return format_date(strtotime($dateRange));
}


?>
