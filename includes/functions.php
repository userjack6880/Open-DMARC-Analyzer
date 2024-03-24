<?php
/* ----------------------------------------------------------------------------

Open Report Analyzer
Copyright (C) 2023 - John Bradley (userjack6880)

includes/functions.php
  site functions that also includes specific queries

Available at: https://github.com/userjack6880/Open-Report-Analyzer

-------------------------------------------------------------------------------

This file is part of Open Report Analyzer.

Open Report Analyzer is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY 
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
this program.  If not, see <https://www.gnu.org/licenses/>.

---------------------------------------------------------------------------- */

// Small Functions ------------------------------------------------------------

// Date Functions -------------------------------
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

// Convert IP to something Usable ---------------
function get_ip($ip4, $ip6) {
  if ($ip4) {
    $array['ip'] = long2ip($ip4);
    $array['ipv4'] = true;
    return $array;
  }
  else {
    $array['ip'] = inet_ntop($ip6);
    $array['ipv4'] = false;
    return $array;
  }
}

// Get argument from GET, POST, or return the default value given
function getArg($arg,$default) {
  if (!empty($_GET[$arg])) {
    return htmlspecialchars($_GET[$arg]);
  }
  elseif (isset($_POST[$arg])) {
    return htmlspecialchars($_POST[$arg]);
  }
  else {
    return $default;
  }
}

// Page Functions -------------------------------------------------------------

// Get TLS Statistics -------------------------------------------------------
function getTLSStats($dateRange,$domain){
  $pdo = dbConn();
  $startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

  $statement = "SELECT policy_mode, summary_success, summary_failure, mindate, maxdate, domain
  FROM tls
  WHERE mindate BETWEEN :startdate AND NOW()";
  if ($domain == "all") {
    $params[':startdate'] = $startDate;
  }
  else {
    $statement .= " AND domain = :domain";
    $params = array(':startdate' => $startDate, ':domain' => $domain);
  }
  $statement .= " ORDER BY maxdate";

  $t_stats = dbQuery($pdo, $statement, $params);
  return $t_stats;
}

// Get DMARC Statistics -------------------------------------------------------
function getDMARCStats($dateRange,$domain) {
  $pdo = dbConn();
  $startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

  $statement = "SELECT t1.domain, total_messages, t1.policy_p, t1.policy_pct, t2.none, t3.quarantine, t4.reject, 
  t5.dkim_pass as dkim_pass_aligned, t6.dkim_pass as dkim_pass_unaligned,
  t7.spf_pass as spf_pass_aligned, t8.spf_pass as spf_pass_unaligned, t9.compliant
  FROM (
  SELECT
    domain, sum(rcount) AS total_messages, policy_p, policy_pct
  FROM report_stats
  WHERE mindate BETWEEN :startdate AND NOW()
  GROUP BY domain, policy_p, policy_pct
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
    $params[':startdate'] = $startDate;
  }
  else {
    $statement .= " WHERE t1.domain = :domain";
    $params = array(':startdate' => $startDate, ':domain' => $domain);
  }

  $d_stats = dbQuery($pdo, $statement, $params);
  return $d_stats;
}
// JSON ----------------------------------------
function json($dateRange, $domain){
  //Get the stats that are stored in the DB
  $recordedStats = ["d_stats" => [], "t_stats" => []];

  if (REPORT_TYPE == "all" || REPORT_TYPE == "dmarc") {
    $recordedStats["d_stats"] = getDMARCStats($dateRange,$domain);
  }

  if (REPORT_TYPE == "all" || REPORT_TYPE == "tls") {
    $recordedStats["t_stats"] = getTLSStats($dateRange,$domain);
  }

  //Create the empty results object that will be outputted
  $numericStats = ["summary_success", "summary_failure", "total_messages", "none", "quarantine", "reject", "dkim_pass_aligned", "dkim_pass_unaligned", "spf_pass_aligned", "spf_pass_unaligned", "compliant"];
  $pctStats = ["policy_pct"];

  $allStats = [];
  $domainStats = [];
  $totalDomains = 0;

  foreach($numericStats as $statName){
    $allStats[$statName] = 0;
  }
  foreach($pctStats as $statName){
    $allStats[$statName] = 0;
  }

  //Process the stats
  foreach($recordedStats as $recordedStatName=>$recordedStat){
    foreach($recordedStat as $stats){
      $domain = $stats["domain"];
      //Check if we are processing a new domain
      if(!array_key_exists($domain, $domainStats)){
        $domainStats[$domain] = ["dmarc" => [], "mtasts" => []];
        $totalDomains++;
      }
      
      //Go through each stat and add it to the allstats totals
      foreach($stats as $statName=>$statValue){
        if(in_array($statName, $numericStats) || in_array($statName, $pctStats)){
          if($statValue == null){
            $statValue = 0;
          }
          $allStats[$statName] = $allStats[$statName] + $statValue;
        }
      }

      //Store the full stats in the domain record
      if($recordedStatName == "d_stats"){
        $domainStats[$domain]["dmarc"] = $stats;
      }elseif($recordedStatName == "t_stats"){
        $domainStats[$domain]["mtasts"] = $stats;
      }
    }
  }

  //Convert percentage stats
  foreach($pctStats as $statName){
    $allStats[$statName] = $allStats[$statName] / $totalDomains;
  }

  $out = ["all" => $allStats, "domains" => $domainStats];
  echo json_encode($out);
}

// Dashboard ------------------------------------
function dashboard($dateRange,$domain) {
  $pdo = dbConn();
  $startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

  // Get broad DMARC statistics
  if (REPORT_TYPE == "all" || REPORT_TYPE == "dmarc") {
    $d_stats = getDMARCStats($dateRange,$domain);
  }
  else {
    $d_stats = [];
  }

  // get broad TLS statistics
  if (REPORT_TYPE == "all" || REPORT_TYPE == "tls") {
    $t_stats = getTLSStats($dateRange,$domain);
  }
  else {
    $t_stats = [];
  }

  overview_bar($d_stats, $t_stats, $domain);

  // individual domain overviews for multi-domain environments
  if ($domain == "all") {
    domain_overview($d_stats, $t_stats, $dateRange);
  }

  // details if a specific domain is selected
  if ($domain != "all") {

    // dmarc query for domain senders
    if (REPORT_TYPE == "all" || REPORT_TYPE == "dmarc") {
      $statement = "SELECT ip, ip6,
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
      $d_stats = dbQuery($pdo, $statement, $params);
    }
    else {
      $d_stats = [];
    }

    // tls query for domain recievers
    if (REPORT_TYPE == "all" || REPORT_TYPE == "tls") {
      $statement = "SELECT UNIQUE summary_success, summary_failure, recv_mx
                    FROM tls
                    LEFT JOIN tlsrecord on tls.serial=tlsrecord.serial
                    WHERE domain = :domain AND mindate BETWEEN :startdate AND NOW()";
      $params = array(':domain' => $domain, ':startdate' => $startDate);
      $t_stats = dbQuery($pdo, $statement, $params);
    }
    else {
      $t_stats = [];
    }

    domain_details($d_stats, $t_stats, $domain, $dateRange);
  }

  $pdo = NULL;
}

// Get Sender Details ---------------------------
function senderDashboard($dateRange, $domain, $ip) {
  if (!isset($ip)) {
    echo "<h1>No IP Given</h1>\n";
    return;
  }
  elseif(!GEO_ENABLE) {
    require_once(AUTO_LOADER);

    $whois = new phpWhois\Whois();
    $geo_data = $whois->lookup($ip,false);
  }
  else {
    require_once(AUTO_LOADER);

    $geo = new MaxMind\Db\Reader(GEO_DB);
    $geo_data = $geo->get($ip);

    $geo->close();
  }

  // db connect
  $pdo = dbConn();
  $startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

  $statement = "SELECT reportid, rcount, mindate, disposition, reason,
                         dkimdomain, dkimresult, dkim_align,
                         spfdomain, spfresult, spf_align
                FROM report
                LEFT JOIN rptrecord ON report.serial=rptrecord.serial
                WHERE report.serial in (
                      SELECT serial
                      FROM rptrecord";

  // determine if ipv4 or ipv6 before proceeding
  $ip4 = ip2long($ip);
  $is_ip4 = true;
  if (!$ip4) {
      $ip6 = inet_pton($ip);
      $is_ip4 = false;
  }

  // proceed with the statment
  if ($is_ip4) {
    $statement .= " WHERE ip = :ip)
                   AND ip = :ip";
    $params[':ip'] = $ip4;
  }
  else {
    $statement .= " WHERE ip6 = :ip)
                   AND ip6 = :ip";
    $params[':ip'] = $ip6;
  }

  $statement .= " AND mindate BETWEEN :startdate AND NOW()";
  $params[':startdate'] = $startDate;

  // domain dependent
  if ($domain != "all") {
    $statement .= " AND domain = :domain";
    $params[':domain'] = $domain;
  }

  $statement .= " ORDER BY mindate ASC";

  $stats = dbQuery($pdo, $statement, $params);

  sender_details($geo_data, $stats, $domain, $dateRange, $ip);

  $pdo = NULL;
}

// Get Reciever Details -------------------------------------------------------
function recieverDashboard($dateRange, $domain, $mx) {
  if (!isset($mx)) {
    echo "<h1>No MX Given</h1>\n";
    return;
  }

  // db connect
  $pdo = dbConn();
  $startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

  // get list of reports
  $statement = "SELECT UNIQUE tls.serial AS serial, mindate, maxdate, reportid, domain, org, email, summary_success, summary_failure, policy_mode
                FROM tls
                LEFT JOIN tlsrecord ON tls.serial=tlsrecord.serial
                WHERE tls.serial IN (
                  SELECT serial
                  FROM tlsrecord
                  WHERE recv_mx = :mx) AND mindate BETWEEN :startdate AND NOW()";
  
  if ($domain == 'all') {
    $params = array(':mx' => $mx, ':startdate' => $startDate);
  }
  else {
    $statement .= " AND domain = :domain";
    $params = array(':mx' => $mx, ':startdate' => $startDate, ':domain' => $domain);
  }

  $statement .= " ORDER BY org, mindate ASC";

  $reports = dbQuery($pdo, $statement, $params);

  // now get the details of entries and stick into a 4d array
  $entries = [];
  foreach ($reports as $report) {
    $serial = $report['serial'];
    $statement = "SELECT type, count
                  FROM tlsrecord
                  WHERE recv_mx = :mx AND serial = :serial";
    $params = array(':mx' => $mx, ':serial' => $serial);

    $results = dbQuery($pdo, $statement, $params);

    foreach ($results as $result) {
      if(!isset($entries[$serial][$result['type']])) {
        $entries[$serial][$result['type']] = $result['count'];
      }
      else {
        $entries[$serial][$result['type']] += $result['count'];
      }
    }
  }

  reciever_details($reports, $entries, $domain, $dateRange, $mx);

  $pdo = NULL;
}

// Get Report Details ---------------------------------------------------------
function reportDashboard($report) {
  if (!isset($report)) {
    echo "<h1>No ReportID Given</h1>\n";
    return;
  }

  $pdo = dbConn();

  $statement = "SELECT mindate, maxdate, domain, org, email, extra_contact_info, 
                       policy_adkim, policy_aspf, policy_p, policy_pct, 
                       ip, ip6, rcount, disposition, reason, 
                       dkimdomain, dkimresult, dkim_align, 
                       spfdomain, spfresult, spf_align
                  FROM rptrecord
                LEFT JOIN report ON rptrecord.serial=report.serial
                WHERE reportid = :reportid";
  $params[':reportid'] = $report;

  $data = dbQuery($pdo, $statement, $params);

  report_details($data, $report);

  $pdo = NULL;
}

// ----- Smaller Fetches ------------------------------------------------------

// Get Domains ----------------------------------
function getDomains($dateRange) {
  $pdo = dbConn();
  // let's modify this so we get *all* domains rather than just ones within a date range - this'll be important
  // if we want to support TLS reports too

//  $startDate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));
//  $statement = "SELECT DISTINCT domain FROM report WHERE mindate BETWEEN :startdate AND NOW()";
  $statement = "SELECT DISTINCT domain FROM report UNION SELECT DISTINCT domain FROM tls";
//  $params[':startdate'] = $startDate;
//  $domains = dbQuery($pdo, $statement, $params);
  $params = [];
  $domains = dbQuery($pdo, $statement, $params);

  foreach ($domains as $key => $domain) {
    $domain = array_map('htmlspecialchars', $domain);
    $domains[$key] = $domain;
  }

  $pdo = NULL;
  return $domains;
}

// Get Number of Senders ------------------------
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
?>
