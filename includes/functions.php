<?php
/*
OpenDAnalyzer - Open Source DMARC Analyzer
includes/functions.php
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

// Debug Functions //
function debug($string, $debugLevel = DEBUG) {
	if ($debugLevel == 1 && php_sapi_name() === 'cli') { echo "$string\n"; } // Basic CLI Debug Level
	if ($debugLevel == 2) { echo "$string<br>\n"; } // Web Debug Level
}

// Get Data //
function report_data($mysqli, $dateRange = DATE_RANGE) {

	// pull the serial number of all reports within date range
	$startDate = start_date($dateRange);
	debug("Start Date: $startDate");
	$query = "SELECT * FROM `report` WHERE `mindate` BETWEEN '$startDate' AND NOW() ORDER BY `domain`";
	$result = $mysqli->query($query);
	$rows = [];
	while ($row = $result->fetch_array()) { array_push($rows, $row); }
	$result->close();

	$counts = [];
	// using said serial numbers, pull all rpt record data
	// run through each row, and count total emails, the alignment counts, and results
	foreach ($rows as $data) {
		$query = "SELECT * from `rptrecord` WHERE `serial` = ".$data['serial']." ORDER BY `identifier_hfrom`";
		$result = $mysqli->query($query);
		while ($row = $result->fetch_array()) {
			$id = strtolower($row['identifier_hfrom']);

			if (empty($counts[$id])) { 
				$counts[$id] = new stdClass(); 
				$counts[$id]->hfrom      = $id;
				$counts[$id]->rcount     = 0;
				$counts[$id]->numReport  = 0;
				$counts[$id]->resultDKIM = 0;
				$counts[$id]->resultSPF  = 0;
				$counts[$id]->alignDKIM  = 0;
				$counts[$id]->alignSPF   = 0;
				$counts[$id]->policy     = $data['policy_p'];
				$counts[$id]->policyPct  = $data['policy_pct'];
				$counts[$id]->reports    = [];
			}
			$counts[$id]->numReport++;
			$counts[$id]->rcount += $row['rcount'];
			if ($row['dkimresult'] == 'pass')   { $counts[$id]->resultDKIM++; }
			if ($row['spfresult']  == 'pass')   { $counts[$id]->resultSPF++;  }
			if ($row['dkim_align'] == 'pass')   { $counts[$id]->alignDKIM++;  }
			if ($row['spf_align']  == 'pass')   { $counts[$id]->alignSPF++;   }
			if (empty($counts[$id]->reports[$data['org']])) { $counts[$id]->reports[$data['org']] = 0; }
			$counts[$id]->reports[$data['org']]++;
		}
	}

	return $counts;
}

// Dashboard //

function dashboard($mysqli, $dateRange = DATE_RANGE) {
	dashboard_table_start(start_date($dateRange));

	// Now we calculate the volume of mail, the DMARC compliance, and the verification percentages
	// and each organization and number of reports... and print it out into a table

	$rdata = report_data($mysqli, $dateRange);	

	foreach ($rdata as $data) {
		echo "\t<tr class='dash_row'>\n";
		echo "\t\t<td>".$data->hfrom."</td>\n";
		echo "\t\t<td>".$data->rcount."</td>\n";

		$alignDKIM = number_format(100 * ($data->alignDKIM  / $data->numReport));
		$alignSPF  = number_format(100 * ($data->alignSPF   / $data->numReport));
		$DKIMpass  = number_format(100 * ($data->resultDKIM / $data->numReport));
		$SPFpass   = number_format(100 * ($data->resultSPF  / $data->numReport));
		$compliance = max($alignDKIM, $alignSPF);

		echo "\t\t<td>".$data->policyPct."% ".$data->policy."</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'><span>$compliance%</span></div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$compliance%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'><span>$alignDKIM%</span></div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='gray-per' style='width:$DKIMpass%'></div>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$alignDKIM%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'><span>$alignSPF%</span></div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='gray-per' style='width:$SPFpass%'></div>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$alignSPF%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";
	}

	echo "</table>";
}

// Misc Functions //
function format_date($date) {
	return date("Y-m-d H:i:s",$date);
}

function date_range($dateRange = DATE_RANGE) {
	debug("Date Range: $dateRange");
	if ($dateRange == DATE_RANGE) { return $dateRange; }
	preg_match('/(\d+)(\w+)/', $dateRange, $match);
	$range = "-$match[1] ";

	// work out if it's week, month, or day
	if ($match[2] == 'w') { $range .= 'week'; }
	if ($match[2] == 'm') { $range .= 'month'; }
	if ($match[2] == 'y') { $range .= 'year'; }

	debug("Date Range Formatted: $range");

	return $range;
}

function start_date($dateRange = DATE_RANGE) {
	return format_date(strtotime(date_range($dateRange)));
}


?>
