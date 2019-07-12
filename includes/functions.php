<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
includes/functions.php
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

// Debug Functions //
function debug($string, $debugLevel = DEBUG) {
	if ($debugLevel == 1 && php_sapi_name() === 'cli') { echo "$string\n"; } // Basic CLI Debug Level
	if ($debugLevel == 2) { echo "$string<br>\n"; } // Web Debug Level
}

// Get Data //
function report_data($pdo, $dateRange = DATE_RANGE, $serial = NULL) {

	// pull the data of all reports within date range
	$startDate = start_date($dateRange);
	if (isset($serial)) {
		$params = array(':startDate' => $startDate, ':serial' => $serial);
		$query = $pdo->prepare("SELECT * FROM `report` WHERE `serial` = :serial AND `mindate` BETWEEN :startDate AND NOW() ORDER BY `domain`");
	} else {
		$params = array(':startDate' => $startDate);
		$query = $pdo->prepare("SELECT * FROM `report` WHERE `mindate` BETWEEN :startDate AND NOW() ORDER BY `serial`");
	}
	$query->execute($params);
	$rows = [];

	// push it into an array that we'll give back
	while ($row = $query->fetch(PDO::FETCH_ASSOC)) { array_push($rows, $row); }
	$query = null;
	return $rows;
}

function domain_data($pdo, $dateRange = DATE_RANGE, $domain, $disp = 'none') {
	// This function will only work if the domain is given
	if (!isset($domain)) { die("critical error: must have domain name defined"); }

	// since we know the domain, we need to get all of the serial numbers of reports associated with this domain
	$params = array(':domain' => $domain, ':disp' => $disp);
	$query = $pdo->prepare("SELECT DISTINCT `serial` FROM `rptrecord` WHERE `identifier_hfrom` = :domain AND `disposition` = :disp");
	$query->execute($params);

	// now that we have the serial numbers, let's get the data for each serial number
	$rows = [];
	while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
		$rdata = report_data($pdo, $dateRange, $row['serial']);
		// this will return an array of rows - we'll need to merge this with the existing blank rows array
		debug("Merging arrays for ".$row['serial']);
		$rows = array_merge($rows, $rdata);
	}

	$query = null;
	debug("ROWS Array\n".print_r($rows,true));
	return $rows;
}

function dmarc_data($pdo, $rdata, $domain = NULL, $disp = 'none') {

	$counts = [];
	$serials = [];
	$policy = [];

	// extract the serial numbers from the array given and push into an array of just serial numbers
	// additionally, pair serial numbers with their policies
	foreach ($rdata as $data) {
		array_push($serials, $data['serial']);
		$policy[$data['serial'].'_p'] = $data['policy_p'];
		$policy[$data['serial'].'_pct'] = $data['policy_pct'];
	}

	// parameters are different based on if the domain is set
	if (isset($domain)) {
		$params = array(':domain' => "$domain", ':disp' => $disp);
	} else {
		$params = array(':domain' => "%", ':disp' => $disp);
	}

	// find all records with serials in the array of serials
	$query = $pdo->prepare("SELECT * from `rptrecord` WHERE `serial` IN ('".implode("', '",$serials)."') AND `identifier_hfrom` LIKE :domain AND disposition = :disp ORDER BY `identifier_hfrom`");
	$query->execute($params);

	// run through each returned row and create some counts
	while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
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
			$counts[$id]->compliance = 0;
			$counts[$id]->policy     = $policy[$row['serial'].'_p'];
			$counts[$id]->policyPct  = $policy[$row['serial']'_pct'];
			$counts[$id]->reports    = [];
		}
		$counts[$id]->numReport++;
		$counts[$id]->rcount   += $row['rcount'];
		$counts[$id]->policy    = $policy[$row['serial'].'_p'];
		$counts[$id]->policyPct = $policy[$row['serial'].'_pct'];
		if ($row['dkimresult'] == 'pass')   { $counts[$id]->resultDKIM++; }
		if ($row['spfresult']  == 'pass')   { $counts[$id]->resultSPF++;  }
		if ($row['dkim_align'] == 'pass')   { $counts[$id]->alignDKIM++;  }
		if ($row['spf_align']  == 'pass')   { $counts[$id]->alignSPF++;   }

		// let's properly count compliance - if results and alignment pass for either SPF or DKIM, it's compliant
		if (($row['dkimresult'] == 'pass' && $row['dkim_align'] == 'pass') || ($row['spfresult'] == 'pass' && $row['spf_align'] == 'pass')) {
			$counts[$id]->compliance++;
		}

		// still don't know if this is useful to me yet coming from this function			
		if (empty($counts[$id]->reports[$data['org']])) { $counts[$id]->reports[$data['org']] = 0; }
		$counts[$id]->reports[$data['org']]++;
	}

	$query = null;

	return $counts;
}

// Domain Reports //

function domain_reports($domain, $pdo, $dateRange = DATE_RANGE, $disp = 'none') {
	echo "<h2>";
	if ($disp == 'quarantine') { echo "Quarantined "; }
	if ($disp == 'reject') { echo "Rejected "; }
	echo "Domain Details for $domain - Since ".start_date($dateRange)."</h2>\n";

	// pull serial numbers of reports within date range and with specific domain
	$rdata = domain_data($pdo, $dateRange, $domain, $disp);
	$counts = dmarc_data($pdo, $rdata, $domain, $disp);	

	domain_reports_dkim_table_start();

	foreach ($counts as $data) {
		echo "\t<tr class='dash_row'>\n";
		echo "\t\t<td>$data->hfrom</td>\n";
		echo "\t\t<td>$data->rcount</td>\n";

		$alignDKIM  = number_format(100 * ($data->alignDKIM  / $data->numReport));
		$alignSPF   = number_format(100 * ($data->alignSPF   / $data->numReport));
		$DKIMpass   = number_format(100 * ($data->resultDKIM / $data->numReport));
		$SPFpass    = number_format(100 * ($data->resultSPF  / $data->numReport));
		$compliance = number_format(100 * ($data->compliance / $data->numReport));

		echo "\t\t<td>$data->policyPct% $data->policy</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'><span>$compliance% Compliant</span></div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$compliance%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'>\n";
		echo "\t\t\t\t<span>$alignDKIM% Aligned | $DKIMpass% Passed</span>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='gray-per' style='width:$DKIMpass%'></div>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$alignDKIM%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'>\n";
		echo "\t\t\t\t<span>$alignSPF% Aligned | $SPFpass% Passed</span>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='gray-per' style='width:$SPFpass%'></div>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$alignSPF%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";
	}

	echo "</table>\n";

	// list out the reports
	domain_reports_table_start();
	
	foreach ($rdata as $data) {
		echo "\t<tr>\n";
		echo "\t\t<td>".$data['mindate']." - ".$data['maxdate']."</td>\n";
		echo "\t\t<td><a href='org.php?org=".$data['org']."&domain=$domain";
		if (date_range($dateRange) != DATE_RANGE) { echo "&range=$dateRange"; }
		echo "'>".$data['org']."</a></td>\n";
		echo "\t\t<td><a href='report.php?serial=".$data['serial']."'>".$data['reportid']."</a></td>\n";
		echo "\t<tr>\n";
	}
	echo "</table>\n";

	// now let's just list out all the details
	reports_table_start();

	$serials = [];
	$reports = [];
	foreach ($rdata as $data) {
		array_push($serials, $data['serial']);
		$reports[$data['serial']] = $data['reportid'];
	}

	$params = array(':domain' => $domain, ':disp' => $disp);
	$query = $pdo->prepare("SELECT * FROM `rptrecord` WHERE `serial` IN ('".implode("', '",$serials)."') AND `identifier_hfrom` = :domain AND `disposition` = :disp");
	$query->execute($params);
	while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
		debug ("printing row");
		echo "\t<tr>\n";
		echo "\t\t<td><a href='report.php?serial=".$row['serial']."'>".$reports[$row['serial']]."</a></td>\n";
		echo "\t\t<td><a href='host.php?ip=".long2ip($row['ip']);
		if (date_range($dateRange) != DATE_RANGE) { echo "&range=$dateRange"; }
		echo "'>".long2ip($row['ip'])."</a></td>\n";
		echo "\t\t<td>".gethostbyaddr(long2ip($row['ip']))."</td>\n";
		echo "\t\t<td>".$row['rcount']."</td>\n";
		echo "\t\t<td>".$row['disposition']."</td>\n";
		echo "\t\t<td>".$row['reason']."</td>\n";
		echo "\t\t<td>".$row['dkimdomain']."</td>\n";
		echo "\t\t<td>Result: <span class='".$row['dkimresult']."'>".$row['dkimresult']."</span> | Alignment: <span class='".$row['dkim_align']."'>".$row['dkim_align']."</span></td>\n";
		echo "\t\t<td>".$row['spfdomain']."</td>\n";
		echo "\t\t<td>Result: <span class='".$row['spfresult']."'>".$row['spfresult']."</span> | Alignment: <span class='".$row['spf_align']."'>".$row['spf_align']."</span></td>\n";
		echo "\t</tr>\n";
	} 
	$query = null;
	echo "</table>\n";

}

// Single Report Table //

function single_report($serial, $pdo) {
	// let's get some data from the report that matches this serial number
	$params = array(':serial' => $serial);
	$query = $pdo->prepare("SELECT * FROM `report` WHERE `serial` = :serial");
	$query->execute($params);

	$data = $query->fetch(PDO::FETCH_ASSOC); // this should only return one row

	echo "<h2>Details for Report ".$data['reportid']."</h2>\n";
	echo "<p>Date Range: ".$data['mindate']." - ".$data['maxdate']."<br />\n";
	echo "Domain: ".$data['domain']."<br />\n";
	echo "Reporting Org: ".$data['org']."<br />\n";
	echo "Domain DMARC Policy: ".$data['policy_p'].
	     " | Subdomain Policy: ".$data['policy_sp'].
	     " | Enforcement Percentage: ".$data['policy_pct']."<br />\n";
	echo "DKIM Policy: ".$data['policy_adkim']." | SPF Policy: ".$data['policy_aspf']."<br />\n";

	// Now print a detailed table...
	single_report_table_start();

	$query = null;
	$query = $pdo->prepare("SELECT * FROM `rptrecord` WHERE `serial` = :serial");
	$query->execute($params);

	while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
		echo "\t<tr>\n";
		echo "\t\t<td><a href='host.php?ip=".long2ip($row['ip'])."'>".long2ip($row['ip'])."</a></td>\n";
		echo "\t\t<td>".gethostbyaddr(long2ip($row['ip']))."</td>\n";
		echo "\t\t<td>".$row['rcount']."</td>\n";
		echo "\t\t<td>".$row['disposition']."</td>\n";
		echo "\t\t<td>".$row['reason']."</td>\n";
		echo "\t\t<td>".$row['dkimdomain']."</td>\n";
		echo "\t\t<td>Result: <span class='".$row['dkimresult']."'>".$row['dkimresult']."</span> | Alignment: <span class='".$row['dkim_align']."'>".$row['dkim_align']."</span></td>\n";
		echo "\t\t<td>".$row['spfdomain']."</td>\n";
		echo "\t\t<td>Result: <span class='".$row['dkimresult']."'>".$row['spfresult']."</span> | Alignment: <span class='".$row['spf_align']."'>".$row['spf_align']."</span></td>\n";
		echo "\t</tr>\n";
	}

	echo "</table>\n";
	$query = null;
}

// Senders (Host) Report GeoIP info //
function senders_report_info($ip = null) {
	// if no IP is given, don't bother with anything
	if (!isset($ip)) { return; }
	// if GeoIP2 is disabled, don't bother with anything
	elseif(!GEO_ENABLE) { return; }
	// otherwise, let's get started with this
	else {
		echo "<h2>GeoIP Info for $ip</h2>\n";

		require_once(GEO_LOADER); 

		$reader = new MaxMind\Db\Reader(GEO_DB);

		$data = $reader->get($ip);

		echo "City: ".$data['city']['names']['en']."<br>\n";
		echo "Region: ".$data['subdivisions']['0']['names']['en']."<br>\n";
		echo "Country: ".$data['country']['names']['en']."<br>\n";
		echo "Location: ".$data['location']['latitude'].",".$data['location']['longitude']."<br>\n";
		echo "Hostname: ".gethostbyaddr($ip)."</td>\n";
		debug(str_replace(array('&lt;?php&nbsp;','?&gt;'), '', highlight_string( '<?php ' .     var_export($data, true) . ' ?>', true ) ));

		$reader->close();
	}
}

// Senders (Host) Report Table //

function senders_report_table($pdo, $dateRange = DATE_RANGE, $domain = null, $ip = null) {
	$ip = ip2long($ip);
	$rdata = report_data($pdo, $dateRange);

	senders_report_table_start();

	$serials = [];
	foreach ($rdata as $data) {
		array_push($serials, $data['serial']);
	}

	$params = array(':ip' => '%%', ':domain' => '%%');
	if (isset($ip)) { $params[':ip'] = "%$ip%"; }
	if (isset($domain)) { $params[':domain'] = "%$domain%"; }
	$query = $pdo->prepare("SELECT DISTINCT `ip`,`identifier_hfrom` FROM `rptrecord` WHERE `ip` IS NOT NULL AND `ip` LIKE :ip AND `identifier_hfrom` LIKE :domain AND `serial` IN ('".implode("', '",$serials)."') ORDER BY `ip`");
	$query->execute($params);

	while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
		echo "\t<tr>\n";
		echo "\t\t<td>".long2ip($row['ip'])."</td>\n";
		echo "\t\t<td>".gethostbyaddr(long2ip($row['ip']))."</td>\n";
		echo "\t\t<td><a href='domain.php?domain=".$row['identifier_hfrom']."'>".$row['identifier_hfrom']."</a></td>\n";
		echo "\t</tr>\n";
	}

	echo "</table>\n";
	$query = null;
}

// ORG Report Table //

function org_report($pdo, $dateRange = DATE_RANGE, $org = null, $domain = null) {
	// This function will only work if the org is given
	if (!isset($org)) { die("critical error: must have org defined"); }

	if (isset($domain)) { $domainTxt = $domain; }
	else { $domainTxt = "All Domains"; }

	org_report_table_start($org, $domainTxt, start_date($dateRange));

	if (isset($domain)) { $params = array(':org' => $org, ':domain' => $domain, ':startDate' => start_date($dateRange)); }
	else { $params = array(':org' => $org, ':domain' => '%%', ':startDate' => start_date($dateRange)); }

	$query = $pdo->prepare("SELECT * FROM `report` WHERE `org` = :org AND `domain` LIKE :domain AND `mindate` BETWEEN :startDate AND NOW() ORDER BY `serial`");
	$query->execute($params);

	while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
		echo "\t<tr>\n";
		echo "\t\t<td><a href='report.php?serial=".$row['serial']."'>".$row['reportid']."</a></td>\n";
		echo "\t\t<td>".$row['domain']."</td>\n";
		echo "\t\t<td>".$row['email']."</td>\n";
		echo "\t\t<td>".$row['extra_contact_info']."</td>\n";
		echo "\t</tr>\n";
	}

	echo "</table>\n";
	$query = null;
}

// Dashboard //

function dashboard($pdo, $dateRange = DATE_RANGE, $disp = 'none') {
	echo "<h2>Dashboard";
	if ($disp == 'quarantine') { echo " - Quarantined"; }
	if ($disp == 'reject') { echo " - Rejected"; }
	echo "</h2>\n";

	dashboard_dmarc_table_start(start_date($dateRange));

	// Now we calculate the volume of mail, the DMARC compliance, and the verification percentages
	// and each organization and number of reports... and print it out into a table

	$rdata = dmarc_data($pdo, report_data($pdo,$dateRange), null, $disp);	

	foreach ($rdata as $data) {
		echo "\t<tr class='dash_row'>\n";
		echo "\t\t<td><a href='domain.php?domain=".$data->hfrom;
		// optionals
		if ($disp == 'quarantine' || $disp == 'reject') { echo "&disp=$disp"; }
		if (date_range($dateRange) != DATE_RANGE) { echo "&range=$dateRange"; }
		echo "'>".$data->hfrom."</a></td>\n";
		echo "\t\t<td>".$data->rcount."</td>\n";

		$alignDKIM  = number_format(100 * ($data->alignDKIM  / $data->numReport));
		$alignSPF   = number_format(100 * ($data->alignSPF   / $data->numReport));
		$DKIMpass   = number_format(100 * ($data->resultDKIM / $data->numReport));
		$SPFpass    = number_format(100 * ($data->resultSPF  / $data->numReport));
		$compliance = number_format(100 * ($data->compliance / $data->numReport));

		echo "\t\t<td>".$data->policyPct."% ".$data->policy."</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'><span>$compliance% Compliant</span></div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$compliance%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'>\n";
		echo "\t\t\t\t<span>$alignDKIM% Aligned | $DKIMpass% Passed</span>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t\t<div class='perc-bar'>\n";
		echo "\t\t\t\t<div class='gray-per' style='width:$DKIMpass%'></div>\n";
		echo "\t\t\t\t<div class='green-per' style='width:$alignDKIM%'></div>\n";
		echo "\t\t\t</div>\n";
		echo "\t\t</td>\n";

		echo "\t\t<td>\n";
		echo "\t\t\t<div class='perc-text'>\n";
		echo "\t\t\t\t<span>$alignSPF% Aligned | $SPFpass% Passed</span>\n";
		echo "\t\t\t</div>\n";
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
