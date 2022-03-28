<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
includes/functions.php
2022 - John Bradley (userjack6880)

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

// Small Functions
function dateWord($date) {
	preg_match('/\-\d+(\w)/', $date, $match);
	if ($match[1] == 'd') {
		$dateWord = "Day";
	}
	elseif ($match[1] == 'w') {
		$dateWord = "Week";
	}
	else {
		$dateWord = "Month";
	}
	return $dateWord;
}

function dateNum($date) {
	preg_match('/\-(\d+)\w/', $date, $match);
	return $match[1];
}

function dateLtr($date) {
	preg_match('/\-\d+(\w)/', $date, $match);
	return $match[1];
}

// Page Functions
function dashboard($dateRange,$domain) {
	$pdo = dbConn();
	$startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

	// Get broad statistics
	$statement = "SELECT t1.domain, total_messages, t1.policy_p, t1.policy_pct, t2.none, t3.quarantine, t4.reject, 
	               t5.dkim_pass as dkim_pass_aligned, t6.dkim_pass as dkim_pass_unaligned,
	               t7.spf_pass as spf_pass_aligned, t8.spf_pass as spf_pass_unaligned, t9.compliant
	              FROM (
	               SELECT
	                domain, sum(rcount) AS total_messages, policy_p, policy_pct
	               FROM report_stats
	               WHERE mindate BETWEEN :startdate AND NOW()
	               GROUP BY domain
	              ) t1
	              LEFT JOIN (SELECT domain, sum(rcount) AS none 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'none' GROUP BY disposition, domain) t2 ON t1.domain=t2.domain
	              LEFT JOIN (SELECT domain, sum(rcount) AS quarantine 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'quarantine' GROUP BY disposition, domain) t3 on t1.domain=t3.domain
	              LEFT JOIN (SELECT domain, sum(rcount) AS reject 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW() 
	                            AND disposition = 'reject' GROUP BY disposition, domain) t4 on t1.domain=t4.domain
	              LEFT JOIN (SELECT domain, sum(rcount) as dkim_pass 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'none' AND dkimresult = 'pass' AND dkim_align = 'pass' GROUP BY domain) t5 on t1.domain=t5.domain
	              LEFT JOIN (SELECT domain, sum(rcount) as dkim_pass 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'none' AND dkimresult = 'pass' AND NOT dkim_align = 'pass' GROUP BY domain) t6 on t1.domain=t6.domain
	              LEFT JOIN (SELECT domain, sum(rcount) as spf_pass 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'none' AND spfresult = 'pass' AND spf_align = 'pass' GROUP BY domain) t7 on t1.domain=t7.domain
	              LEFT JOIN (SELECT domain, sum(rcount) as spf_pass 
	                           FROM report_stats 
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'none' AND spfresult = 'pass' AND NOT spf_align = 'pass' GROUP BY domain) t8 on t1.domain=t8.domain
	              LEFT JOIN (SELECT domain, sum(rcount) as compliant
	                           FROM report_stats
	                          WHERE mindate BETWEEN :startdate AND NOW()
	                            AND disposition = 'none' AND ((spfresult = 'pass' AND spf_align = 'pass') OR (dkimresult = 'pass' AND dkim_align = 'pass'))
	                       GROUP BY domain) t9 on t1.domain=t9.domain";
	if ($domain == "all") {
		$params = array(':startdate' => $startDate);
	}
	else {
		$statement .= " WHERE t1.domain = :domain";
		$params = array(':startdate' => $startDate, ':domain' => $domain);
	}

	$stats = dbQuery($pdo, $statement, $params);
	$domain = overview_bar($stats, $domain);

	// individual domain overviews for multi-domain environments
	if ($domain == "all") {
		domain_overview($stats, $dateRange);
	}

	// details if a specific domain is selected
	if ($domain != "all") {
		// new stat query
		$statement = "SELECT ip, INET6_NTOA(ip6) as ip6,
		                     SUM(rcount) as messages,
		                     SUM(compliant) as compliant,
		                     SUM(none) as none,
		                     SUM(quarantine) as quarantine,
		                     SUM(reject) as reject,
		                     SUM(dkim_pass) as dkim_pass,
		                     SUM(t5.dkim_align) as dkim_align,
		                     SUM(spf_pass) as spf_pass,
		                     SUM(t7.spf_align) as spf_align
		                FROM rptrecord
		                     LEFT JOIN (SELECT id, rcount AS compliant FROM rptrecord
		                                 WHERE disposition = 'none' 
		                                   AND ((dkimresult = 'pass' AND dkim_align = 'pass')
		                                       OR (spfresult = 'pass' AND spf_align = 'pass'))) t0 on rptrecord.id=t0.id
		                     LEFT JOIN (SELECT id, rcount AS none FROM rptrecord WHERE disposition = 'none') t1 ON rptrecord.id=t1.id
		                     LEFT JOIN (SELECT id, rcount AS quarantine FROM rptrecord WHERE disposition = 'quarantine') t2 ON rptrecord.id=t2.id
		                     LEFT JOIN (SELECT id, rcount AS reject FROM rptrecord WHERE disposition = 'reject') t3 ON rptrecord.id=t3.id
		                     LEFT JOIN (SELECT id, rcount AS dkim_pass FROM rptrecord WHERE disposition = 'none' AND dkimresult ='pass' ) t4 ON rptrecord.id=t4.id
		                     LEFT JOIN (SELECT id, rcount AS dkim_align FROM rptrecord 
		                                 WHERE disposition = 'none' AND dkimresult = 'pass' AND dkim_align = 'pass' ) t5 ON rptrecord.id=t5.id
		                     LEFT JOIN (SELECT id, rcount AS spf_pass FROM rptrecord WHERE disposition = 'none' AND spfresult ='pass' ) t6 ON rptrecord.id=t6.id
		                     LEFT JOIN (SELECT id, rcount AS spf_align FROM rptrecord 
		                                 WHERE disposition = 'none' AND spfresult = 'pass' AND spf_align = 'pass' ) t7 ON rptrecord.id=t7.id
		               WHERE serial IN (SELECT serial FROM report WHERE mindate BETWEEN :startdate AND NOW() AND domain = :domain)
		            GROUP BY ip, ip6
		            ORDER BY messages DESC";
		$params = array(':startdate' => $startDate, ':domain' => $domain);
		$stats = dbQuery($pdo, $statement, $params);

		domain_details($stats, $domain, $dateRange);
	}

	$pdo = NULL;
}

// ----- Smaller Fetches ------------------------
// Get Domains
function getDomains($dateRange) {
	$pdo = dbConn();
	$startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));
	$statement = "SELECT UNIQUE domain FROM report WHERE mindate BETWEEN :startdate AND NOW()";
	$params = array(':startdate' => $startDate);
	$domains = dbQuery($pdo, $statement, $params);
	$pdo = NULL;
	return $domains;
}

// Get Number of Reports
function getSenderCount($dateRange, $domain) {
	$pdo = dbConn();
	$startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));
	$statement = "SELECT (
	               (SELECT COUNT(DISTINCT ip) FROM rptrecord WHERE serial IN
	                 (SELECT serial FROM report WHERE mindate BETWEEN :startdate AND NOW() AND domain = :domain)) +
	               (SELECT COUNT(DISTINCT ip6) FROM rptrecord WHERE serial IN
	                 (SELECT serial FROM report WHERE mindate BETWEEN :startdate AND NOW() AND domain = :domain)) )
	              AS count";
	$params = array(':startdate' => $startDate, ':domain' => $domain);
	$count = dbQuery($pdo, $statement, $params);
	$pdo = NULL;
	return $count[0]['count'];
}

// Convert the raw DB IP format into something usable
function get_ip($ip4, $ip6) {
	if ($ip4) {
		$array['ip'] = long2ip($ip4);
		$array['ipv4'] = true;
		return $array;
	}
	if ($ip6) {
		$array['ip'] = $ip6;
		$array['ipv4'] = false;
		return $array;
	}
}
?>
