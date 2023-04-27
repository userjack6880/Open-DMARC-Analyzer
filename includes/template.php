<?php
/* ----------------------------------------------------------------------------

Open Report Analyzer
Copyright (C) 2023 - John Bradley (userjack6880)

includes/template.php
  template structures to be called by main page scripts
  this is not the full visual templates

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

// Versioning -----------------------------------------------------------------
function oda_version() {

  echo "2-α1";

}

// Javascripts ----------------------------------------------------------------
function javascript() { ?>
<?php }

// General Page Templates -----------------------------------------------------

// Page Title -----------------------------------
function page_title($page) {
  echo "Open Report Analyzer";
  if ($page == "index") {
    echo " - Dashboard";
  }
}

// Control Bar ----------------------------------
function control_bar($page, $domain, $dateRange, $ip = '') {
  // get some variables out of current daterange
  $dateWord = dateWord($dateRange);
  $dateLtr = dateLtr($dateRange);
  $dateNum = dateNum($dateRange);
  $startdate = date("Y-m-d H:i:s",strtotime(strtolower("-$dateNum $dateWord")));

  // pages that need domain controls
  if ($page == "index" || $page == "sender") {

    $domains = getDomains($dateRange);
    if (count($domains) == 1 && $page != "sender") {
      $domain = $domains[0]['domain'];
      echo "<div id=controlbar style='height:25px'>\n";
    }
    else {
      echo "<div id=controlbar>\n";
    }

    // Show if all domains are being shown or a single domain
    echo "<div id=controlbarleft>\n";
    if ($page == "index" ) {
      if (count($domains) == 1) {
        echo "Since $startdate\n";
      }
      else if ($domain == "all") {
        echo "<h1>All Domains</h1><br />\n
              Since $startdate\n";
      }
      else {
        echo "<h1>$domain</h1><br />\n
              Since $startdate\n";
      }
    }
    // Special Cases
    if ($page == "sender") {
      if ($domain == "all") {
        echo "<h1>Sender $ip</h1><br />\n
              <a href='".$_SERVER['PHP_SELF']."?range=-$dateNum$dateLtr&page=index&domain=all'>&larr; Back</a> | Since $startdate\n";
      }
      else {
        echo "<h1>Sender $ip for $domain</h1><br />\n
              <a href='".$_SERVER['PHP_SELF']."?range=-$dateNum$dateLtr&page=index&domain=$domain'>&larr; Back</a> | Since $startdate\n";
      }
    }
    echo "</div>\n";

    // Domain Selection and Date Selection
    echo "<div id=controlbarright>\n";

    if (count($domains) > 1) {
      echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n
              <select name='domain'>\n
              <option value='all'>All Domains</option>\n";
      foreach ($domains as $listDomain) {
        echo "<option value='".$listDomain['domain']."' ";
        if($listDomain['domain'] == $domain) { echo "selected"; }
        echo ">".$listDomain['domain']."</option>\n";
      }
      echo "</select>\n
              <input type='hidden' name='page' value='$page'>\n
              <input type='hidden' name='ip' value='$ip'>\n
              <input type='hidden' name='range' value='$dateRange'>\n
              <input type='submit' value='Go'>\n
            </form><br />\n";
    }

    // date selection -1 unit in config
    $datePrev = $dateNum+1;
    echo "Range Start: &#91; <a href='".$_SERVER['PHP_SELF']."?range=-$datePrev$dateLtr&page=$page&domain=$domain&ip=$ip'>&larr; 1 $dateWord</a>";
  
    // date selectoin +1 unit in config
    if ($dateNum == 1) {
      echo " &#93;\n";
    }
    else {
      $dateNext = $dateNum-1;
      echo " | <a href='".$_SERVER['PHP_SELF']."?range=-$dateNext$dateLtr&page=$page&domain=$domain&ip=$ip'>1 $dateWord &rarr;</a> &#93;\n";
    }

    echo "  </div>\n
          </div>\n";

  }

  else {
    return; // if it's not the specified pages, this controlbar is irrelvant
  }
}

// Overview Bar ---------------------------------
function overview_bar($d_stats, $t_stats, $domain) {
  if (REPORT_TYPE == "all" || REPORT_TYPE == "dmarc") {
    // extract dmarc stats
    $total  = 0;
    $policy = '';
    $policy_pct = 0;
    $dmarc_none = 0;
    $dmarc_quar = 0;
    $dmarc_rjct = 0;
    $dmarc_comp = 0;
    $dkim_pass_aligned = 0;
    $dkim_pass_noalign = 0;
    $spf_pass_aligned  = 0;
    $spf_pass_noalign  = 0;

    if ($domain == "all") {
      $domain_count = 0;
      foreach ($d_stats as $stat) {
        $stat = array_map('htmlspecialchars',$stat);
        $total = $total+$stat['total_messages'];
        if ($stat['none'] > 0)                { $dmarc_none = $dmarc_none+$stat['none']; }
        if ($stat['quarantine'] > 0)          { $dmarc_quar = $dmarc_quar+$stat['quarantine']; }
        if ($stat['reject'] > 0)              { $dmarc_rjct = $dmarc_rjct+$stat['reject']; }
        if ($stat['compliant'] > 0)           { $dmarc_comp = $dmarc_comp+$stat['compliant']; }
        if ($stat['dkim_pass_aligned'] > 0)   { $dkim_pass_aligned = $dkim_pass_aligned+$stat['dkim_pass_aligned']; }
        if ($stat['dkim_pass_unaligned'] > 0) { $dkim_pass_noalign = $dkim_pass_noalign+$stat['dkim_pass_unaligned']; }
        if ($stat['spf_pass_aligned'] > 0)    { $spf_pass_aligned  = $spf_pass_aligned+$stat['spf_pass_aligned']; }
        if ($stat['spf_pass_unaligned'] > 0)  { $spf_pass_noalign  = $spf_pass_noalign+$stat['spf_pass_unaligned']; }
        $domain_count++;
      }

      // clunky, but detects if we have only one domain, and changes all to a single domain if it's just one
      if ($domain_count == 1) {
        $domain     = $d_stats[0]['domain'];
        $policy     = ucfirst($d_stats[0]['policy_p']);
        $policy_pct = $d_stats[0]['policy_pct'];
      }
    }
    else {
      $d_stats[0]   = array_map('htmlspecialchars',$d_stats[0]);
  //    $total      = $d_stats[0]['total_messages'];
      $policy     = ucfirst($d_stats[0]['policy_p']);
      $policy_pct = $d_stats[0]['policy_pct'];
      if ($d_stats[0]['total_messages'] > 0)      { $total = $d_stats[0]['total_messages']; }
      if ($d_stats[0]['none'] > 0)                { $dmarc_none = $d_stats[0]['none']; }
      if ($d_stats[0]['quarantine'] > 0)          { $dmarc_quar = $d_stats[0]['quarantine']; }
      if ($d_stats[0]['reject'] > 0)              { $dmarc_rjct = $d_stats[0]['reject']; }
      if ($d_stats[0]['compliant'] > 0)           { $dmarc_comp = $d_stats[0]['compliant']; }
      if ($d_stats[0]['dkim_pass_aligned'] > 0)   { $dkim_pass_aligned = $d_stats[0]['dkim_pass_aligned']; }
      if ($d_stats[0]['dkim_pass_unaligned'] > 0) { $dkim_pass_noalign = $d_stats[0]['dkim_pass_unaligned']; }
      if ($d_stats[0]['spf_pass_aligned'] > 0)    { $spf_pass_aligned  = $d_stats[0]['spf_pass_aligned']; }
      if ($d_stats[0]['spf_pass_unaligned'] > 0)  { $spf_pass_noalign  = $d_stats[0]['spf_pass_unaligned']; }
    }

    // stat calculations
    if ($dmarc_none) {
      $dmarc_comp_pct = number_format(100 * ($dmarc_comp / $dmarc_none));
      $dkim_comp_pct  = number_format(100 * ($dkim_pass_aligned / $dmarc_none));
      $dkim_pass_pct  = number_format(100 * (((int)$dkim_pass_aligned + (int)$dkim_pass_noalign) / $dmarc_none));
      $spf_comp_pct   = number_format(100 * ($spf_pass_aligned  / $dmarc_none));
      $spf_pass_pct   = number_format(100 * (((int)$spf_pass_aligned  + (int)$spf_pass_noalign)  / $dmarc_none));
    }
    else {
      $dmarc_comp_pct = 0;
      $dkim_comp_pct  = 0;
      $dkim_pass_pct  = 0;
      $spf_comp_pct   = 0;
      $spf_pass_pct   = 0;
    }
  }

  if (REPORT_TYPE == "all" || REPORT_TYPE == "tls") {
    // extract tls stats
    $tls_mode = 'unknown';
    $tls_success = 0;
    $tls_failure = 0;
    $tls_success_pct = 0;
    $tls_total = 0;

    foreach ($t_stats as $stat) {
      $stat = array_map('htmlspecialchars',$stat);
      // this should pick up the latest policy mode
      if ($stat['policy_mode'] != '') { $tls_mode = $stat['policy_mode']; }
      if ($stat['summary_success'] > 0) { $tls_success = $tls_success+$stat['summary_success']; }
      if ($stat['summary_failure'] > 0) { $tls_failure = $tls_failure+$stat['summary_failure']; }
    }

    // stat calculation
    $tls_total = $tls_success + $tls_failure;
    if ($tls_total > 0) {
      $tls_success_pct = number_format(100 * ($tls_success / $tls_total));
    }
  }

  // overview details
  echo "
        <div id=overviewbar>\n";
  if (REPORT_TYPE == "all") {
    echo "
          <div id=overviewbarleft>\n
            <div id=ovbr-in>\n";
    if ($tls_total == 0) {
      echo "
              <span class=perc-title>No MTA-STS Information</span>\n";  
    }
    else {
      echo "
              <span class=ov-title>MTA-STS Statistics</span><br \><br \>\n
              Mode<br />\n
                <span class=perc-text>$tls_mode</span><br \>\n
              Total Messages<br \>\n
                <span class=perc-text>$tls_total</span><br \>\n
              Success Rate<br \>\n
                <div class=perc-bar style='margin-top:5px'>\n
                  <div class=green-per style='width:$tls_success_pct%'></div>\n
                </div>\n
                <span class=perc-text>$tls_success Success | $tls_failure Failure</span><br \>\n";
    }
    echo "
            </div>\n
          </div>\n";
  }
  elseif (REPORT_TYPE == "dmarc") {
    echo "
              <div id=overviewbarleft-wide>\n
            <span class=ov-title>DMARC Statistics</span>
            <div id=overviewinnerleft>\n
              Total Messages<br />\n
              <span class=overviewtotal>$total</span>\n";
    if ($domain != "all" && $total > 0) {
      echo "
              <br />\n$policy_pct% $policy\n";
    }
    echo "
            </div>\n
            <div id=overviewinnerright>\n
              <div class=ovir-left>\n
                Accepted<br />\n
                Quarantined<br />\n
                Rejected</br />\n
              </div>\n
              <div class=ovir-right>\n
                <span class=pass>$dmarc_none</span><br />\n
                <span class=warn>$dmarc_quar</span><br />\n
                <span class=fail>$dmarc_rjct</span><br />\n
              </div>\n
            </div>\n
          </div>\n";
  }
  else {
    echo "
          <div id=overviewbarcenter-wide>\n
            <div id=ovbr-wide>\n
              <span class=ov-title>MTA-STS Statistics</span> | Mode: $tls_mode \n
                                                             | Messages: $tls_total\n
                                                             | $tls_success Success\n
                                                             | $tls_failure Failure<br \>\n
            </div>
          </div>";
  }

  if (REPORT_TYPE == "dmarc" || REPORT_TYPE == "all") {
    echo "
          <div id=overviewbarright>\n
            <div id=ovbr-in>\n";
  // if no messages are presented, we need to inform that there's no
  // compliance data
    if ($total == 0) {
      echo "
              <span class=perc-title>No Compliance Information</span>\n";
    }
    else {
      echo "
              Percent Compliant<br />\n
              <span class=overviewtotal>$dmarc_comp_pct%</span><br />\n
              <span class=perc-title>DKIM</span><br />\n
              <div class=perc-bar>\n
                <div class=gray-per style='width:$dkim_pass_pct%'></div>\n
                <div class=green-per style='width:$dkim_comp_pct%'></div>\n
              </div>\n
              <span class=perc-text>$dkim_comp_pct% Aligned | $dkim_pass_pct% Passed</span><br />\n
              <span class=perc-title>SPF</span><br />\n
              <div class=perc-bar>\n
                <div class=gray-per style='width:$spf_pass_pct%'></div>\n
                <div class=green-per style='width:$spf_comp_pct%'></div>\n
              </div>\n
              <span class=perc-text>$spf_comp_pct% Aligned | $spf_pass_pct% Passed</span><br />\n";
    }
    echo "
            </div>\n
          </div>\n";
  }
  if (REPORT_TYPE == "all") {
    echo "
          <div id=overviewbarcenter>\n
            <span class=ov-title>DMARC Statistics</span>
            <div id=overviewinnerleft>\n
              Total Messages<br />\n
              <span class=overviewtotal>$total</span>\n";
    if ($domain != "all" && $total > 0) {
      echo "
              <br />\n$policy_pct% $policy\n";
    }
    echo "
            </div>\n
            <div id=overviewinnerright>\n
              <div class=ovir-left>\n
                Accepted<br />\n
                Quarantined<br />\n
                Rejected</br />\n
              </div>\n
              <div class=ovir-right>\n
                <span class=pass>$dmarc_none</span><br />\n
                <span class=warn>$dmarc_quar</span><br />\n
                <span class=fail>$dmarc_rjct</span><br />\n
              </div>\n
            </div>\n
          </div>\n";
  }
  echo "
        </div>\n";

  // returns a modified domain if only one is detected
  return $domain;
}

// Overview Bar ---------------------------------
function domain_overview($d_stats, $t_stats, $dateRange) {
  $data = [];

  // dmarc data processing
  foreach ($d_stats as $stat) {
    $stat = array_map('htmlspecialchars',$stat);
    $domain     = $stat['domain'];

    // extract stats
    $data[$domain]['dmarc_none'] = 0;
    $data[$domain]['dmarc_quar'] = 0;
    $data[$domain]['dmarc_rjct'] = 0;
    $data[$domain]['dmarc_comp'] = 0;
    $data[$domain]['dkim_pass_aligned'] = 0;
    $data[$domain]['dkim_pass_noalign'] = 0;
    $data[$domain]['spf_pass_aligned']  = 0;
    $data[$domain]['spf_pass_noalign']  = 0;

    // prefill these for the next step
    $data[$domain]['tls_mode']    = 'unknown';
    $data[$domain]['tls_success'] = 0;
    $data[$domain]['tls_failure'] = 0;
    $data[$domain]['tls_total']   = 0;

    $data[$domain]['dmarc_total']      = $stat['total_messages'];
    $data[$domain]['dmarc_policy']     = ucfirst($stat['policy_p']);
    $data[$domain]['dmarc_policy_pct'] = $stat['policy_pct'];
    if ($stat['none'] > 0)                { $data[$domain]['dmarc_none'] = $stat['none']; }
    if ($stat['quarantine'] > 0)          { $data[$domain]['dmarc_quar'] = $stat['quarantine']; }
    if ($stat['reject'] > 0)              { $data[$domain]['dmarc_rjct'] = $stat['reject']; }
    if ($stat['compliant'] > 0)           { $data[$domain]['dmarc_comp'] = $stat['compliant']; }
    if ($stat['dkim_pass_aligned'] > 0)   { $data[$domain]['dkim_pass_aligned'] = $stat['dkim_pass_aligned']; }
    if ($stat['dkim_pass_unaligned'] > 0) { $data[$domain]['dkim_pass_noalign'] = $stat['dkim_pass_unaligned']; }
    if ($stat['spf_pass_aligned'] > 0)    { $data[$domain]['spf_pass_aligned']  = $stat['spf_pass_aligned']; }
    if ($stat['spf_pass_unaligned'] > 0)  { $data[$domain]['spf_pass_noalign']  = $stat['spf_pass_unaligned']; }

//    $sender_count = getSenderCount($dateRange, $domain); // this may be a source of slowness, as it's a query

    // stat calculations
    if ($data[$domain]['dmarc_none']) {
      $data[$domain]['dmarc_comp_pct'] = number_format(100 * ($data[$domain]['dmarc_comp'] / $data[$domain]['dmarc_none']));
      $data[$domain]['dkim_comp_pct']  = number_format(100 * ($data[$domain]['dkim_pass_aligned'] / $data[$domain]['dmarc_none']));
      $data[$domain]['dkim_pass_pct']  = number_format(100 * (((int)$data[$domain]['dkim_pass_aligned'] + (int)$data[$domain]['dkim_pass_noalign']) / $data[$domain]['dmarc_none']));
      $data[$domain]['spf_comp_pct']   = number_format(100 * ($data[$domain]['spf_pass_aligned']  / $data[$domain]['dmarc_none']));
      $data[$domain]['spf_pass_pct']   = number_format(100 * (((int)$data[$domain]['spf_pass_aligned']  + (int)$data[$domain]['spf_pass_noalign'])  / $data[$domain]['dmarc_none']));
    }
    else {
      $data[$domain]['dmarc_comp_pct'] = 0;
      $data[$domain]['dkim_comp_pct']  = 0;
      $data[$domain]['dkim_pass_pct']  = 0;
      $data[$domain]['spf_comp_pct']   = 0;
      $data[$domain]['spf_pass_pct']   = 0;
    }
  }

  // tls data processing
  foreach ($t_stats as $stat) {
    $stat = array_map('htmlspecialchars',$stat);
    $domain = $stat['domain'];

    // extract stats
    if (isset($data[$stat['domain']]['tls_mode'])) {
      if ($stat['policy_mode'] != '')   { $data[$domain]['tls_mode']     = $stat['policy_mode']; }
      if ($stat['summary_success'] > 0) { $data[$domain]['tls_success'] += $stat['summary_success']; }
      if ($stat['summary_failure'] > 0) { $data[$domain]['tls_failure'] += $stat['summary_failure']; }
    }
    else {
      $data[$domain]['tls_mode']    = 'unknown';
      $data[$domain]['tls_success'] = 0;
      $data[$domain]['tls_failure'] = 0;
      $data[$domain]['tls_total']   = 0;

      // for entries without DMARC data
      $data[$domain]['dmarc_none']  = 0;
      $data[$domain]['dmarc_quar']  = 0;
      $data[$domain]['dmarc_rjct']  = 0;
      $data[$domain]['dmarc_comp']  = 0;
      $data[$domain]['dmarc_total'] = 0;
      $data[$domain]['dmarc_policy'] = 'unknown';
      $data[$domain]['dmarc_policy_pct']  = 0;
      $data[$domain]['dkim_pass_aligned'] = 0;
      $data[$domain]['dkim_pass_noalign'] = 0;
      $data[$domain]['spf_pass_aligned']  = 0;
      $data[$domain]['spf_pass_noalign']  = 0;

      if ($stat['policy_mode'] != '')   { $data[$domain]['tls_mode']     = $stat['policy_mode']; }
      if ($stat['summary_success'] > 0) { $data[$domain]['tls_success'] += $stat['summary_success']; }
      if ($stat['summary_failure'] > 0) { $data[$domain]['tls_failure'] += $stat['summary_failure']; }
    }
  }

  foreach ($data as $domain => $entry) {
    // some after (or I guess before) calculations
    $entry['tls_total'] = $entry['tls_success'] + $entry['tls_failure'];

    // overview details
    echo "<div class=dov-bar>\n
            <div class=dov-bar-in>\n
              <div class=dov-bar-in-domain>\n
                <h1 class=dov-bar-head>\n
                  <a href='".$_SERVER['PHP_SELF']."?range=$dateRange&page=index&domain=$domain'>$domain</a>\n
                </h1>\n
              </div>\n";
    if ($entry['dmarc_policy'] == 'unknown') {
      echo "
              <div class=dov-bar-in-dstats-alignment style='text-align:center; margin-top: 40px'>\n
                <span style='margin-top: 50px;'>No Compliance Data</span>\n";
    }
    else {
      echo "
              <div class=dov-bar-in-dstats-alignment>\n
                <span class=perc-text style='text-align:left'>DMARC Compliance</span>\n
                <div class=perc-bar>\n
                  <div class=green-per style='width:".$entry['dmarc_comp_pct']."%'></div>\n
                </div>\n
                <span class=perc-text style='text-align:left'>DKIM</span>\n
                <div class=perc-bar>\n
                  <div class=gray-per style='width:".$entry['dkim_pass_pct']."%'></div>\n
                  <div class=green-per style='width:".$entry['dkim_comp_pct']."%'></div>\n
                </div>\n
                <span class=perc-text style='text-align:left'>SPF</span>\n
                <div class=perc-bar>\n
                  <div class=gray-per style='width:".$entry['spf_pass_pct']."%'></div>\n
                  <div class=green-per style='width:".$entry['spf_comp_pct']."%'></div>\n
                </div>\n";
    }
    echo "
              </div>\n
              <div class=dov-bar-in-dstats-totals>\n
                <table class=dov>\n
                  <tr class=dov>\n
                    <td class=dov style='width:20%; text-align: right; padding-right: 20px;'>DMARC</td>\n
                    <td class=dov style='width:20%'>".$entry['dmarc_total']." Messages Sent</td>\n";
    if ($entry['dmarc_policy'] == 'unknown') {
      echo "
                    <td class=dov style='width:20%'>".$entry['dmarc_policy']."</td>\n";
    }
    else {
      echo "
                    <td class=dov style='width:20%'>".$entry['dmarc_policy_pct']."% ".$entry['dmarc_policy']."</td>\n";
    }
    echo "
                    <td class=dov style='width:20%'>\n";
    if ($entry['dmarc_quar'] > 0) {
      echo "
                      <span class='warn'>".$entry['dmarc_quar']."</span>";
    }
    else {
      echo "
                      ".$entry['dmarc_quar'];
    } 
    echo " Quarantined\n
                    </td>\n
                    <td class=dov style='width:20%'>\n";
    if ($entry['dmarc_rjct'] > 0) {
      echo "
                      <span class='fail'>".$entry['dmarc_rjct']."</span>";
    }
    else {
      echo "
                      ".$entry['dmarc_rjct'];
    } 
    echo " Rejected\n
                    </td>\n
                  </tr>\n
                  <tr class=dov>\n
                    <td class=dov style='text-align: right; padding-right: 20px;'>MTA-STS</td>\n
                    <td class=dov>".$entry['tls_total']." Messages Recieved</td>\n
                    <td class=dov>".$entry['tls_mode']."</td>\n
                    <td class=dov>".$entry['tls_success']." Success</td>\n
                    <td class=dov>\n";
    if ($entry['tls_failure'] > 0) {
      echo "
                      <span class='fail'>".$entry['tls_failure']."</span>";
    }
    else {
      echo "
                      ".$entry['tls_failure'];
    }
    echo " Failure\n
                    </td>\n
                  </tr>\n
                </table>\n
              </div>\n
            </div>\n
          </div>\n";
  }
}

// Domain Details -------------------------------
function domain_details($d_stats, $t_stats, $domain, $dateRange) {
  if (REPORT_TYPE == "all" || REPORT_TYPE == "tls") {
    $t_entries = 0;
    $t_success = [];
    $t_failure = [];
    $t_mx      = [];
    echo "<br>";
    // do summations here
    foreach ($t_stats as $stat) {
      $stat       = array_map('htmlspecialchars',$stat);
      if ($stat['recv_mx'] == '') { $stat['recv_mx'] = 'unknown'; }
      if (!isset($t_success[$stat['recv_mx']]) && !isset($t_failure[$stat['recv_mx']])) {
        $t_success[$stat['recv_mx']] = 0;
        $t_failure[$stat['recv_mx']] = 0;
        if ($stat['summary_success'] > 0) { $t_success[$stat['recv_mx']] = $stat['summary_success']; }
        if ($stat['summary_failure'] > 0) { $t_failure[$stat['recv_mx']] = $stat['summary_failure']; }
        array_push($t_mx, $stat['recv_mx']);
        $t_entries++;
      }
      else {
        $t_success[$stat['recv_mx']] += $stat['summary_success'];
        $t_failure[$stat['recv_mx']] += $stat['summary_failure'];
      }
    }

    $t_height = $t_entries * 100;

    echo "
        <h2 class=section>Domain Recievers</h2>\n";

    if ($t_height != 0) {
      echo "
          <div class=dov-bar style='height:".$t_height."px'>\n
            <div class=dov-bar-in style='height:".$t_height."px'>\n";
        foreach ($t_mx as $mx) {
          // calculations
          $total = $t_success[$mx] + $t_failure[$mx];
          $tls_total = $t_success[$mx] + $t_failure[$mx];
          if ($tls_total > 0) {
            $tls_success_pct = number_format(100 * ($t_success[$mx] / $tls_total));
          }

          echo "
              <div class=dov-bar-in-ip>\n
                <div style='width: 500px'>\n
                  <h3 class=dov-bar-in-ip-h3><a href='".$_SERVER['PHP_SELF']."?range=$dateRange&page=reciever&domain=$domain&mx=$mx'>$mx</a></h3>\n
                </div>\n
                <div style='left: 600px; width:calc(100% - 1000px);'>\n
                  <table class=dov>\n
                    <tr class=dov>\n
                      <td class=dov style='min-width:100px'><strong>Total Messages</strong></td>\n
                      <td class=dov style='min-width:100px'><strong>Success</strong></td>\n
                      <td class=dov style='min-width:100px'><strong>Failure</strong></td>\n
                    </tr>\n
                    <tr class=dov>\n
                      <td class=dov>$total</td>\n
                      <td class=dov>$t_success[$mx]</td>\n
                      <td class=dov>$t_failure[$mx]</td>\n
                    </tr>\n
                  </table>\n
                </div>\n
                <div style='right:20px;width:350px'>\n
                  Success Rate<br \>\n
                  <div class=perc-bar style='margin-top:5px'>\n
                    <div class=green-per style='width:$tls_success_pct%'></div>\n
                  </div>\n
                </div>\n
              </div>\n";
        }
      echo "
            </div>\n
          </div>\n";
    }
    else {
      echo "
          <div class=dov-bar style='height: 100px'>\n
            <div class=dov-bar-in style='height: 100px'>\n
              <div class=dov-bar-in-ip>\n
                <div>\n
                  <h3 class=dov-bar-in-ip-h3>No MTA-STS Data</h3>\n
                </div>\n
              </div>\n
            </div>\n
          </div>\n";
    }
  }
  
  if (REPORT_TYPE == "all" || REPORT_TYPE == "dmarc") {
    $d_entries = count($d_stats);
    $d_height = $d_entries * 100;
    echo "
        <h2 class=section>Domain Senders</h2>\n";

    if ($d_height != 0) {
      echo "
          <div class=dov-bar style='height:".$d_height."px'>\n
            <div class=dov-bar-in style='height:".$d_height."px'>\n";

      foreach ($d_stats as $stat) {
        $compliant  = 0;
        $none       = 0;
        $quarantine = 0;
        $reject     = 0;
        $dkim_pass  = 0;
        $dkim_align = 0;
        $spf_pass   = 0;
        $spf_align  = 0;

        // extract stats - this'll be sorted by senderIP
        $ip         = get_ip($stat['ip'], $stat['ip6']);
        $stat       = array_map('htmlspecialchars',$stat);
        $messages   = $stat['messages'];
        if ($stat['compliant'] > 0)  { $compliant  = $stat['compliant']; }
        if ($stat['none'] > 0)       { $none       = $stat['none']; }
        if ($stat['quarantine'] > 0) { $quarantine = $stat['quarantine']; }
        if ($stat['reject'] > 0)     { $reject     = $stat['reject']; }
        if ($stat['dkim_pass'] > 0)  { $dkim_pass  = $stat['dkim_pass']; }
        if ($stat['dkim_align'] > 0) { $dkim_align = $stat['dkim_align']; }
        if ($stat['spf_pass'] > 0)   { $spf_pass   = $stat['spf_pass']; }
        if ($stat['spf_align'] > 0)  { $spf_align  = $stat['spf_align']; }

        // calculate stats
        $dmarc_comp_pct = number_format(100 * ($compliant  / $messages));
        if ($none > 0) {
          $dkim_comp_pct = number_format(100 * ($dkim_align / $none));
          $dkim_pass_pct = number_format(100 * ($dkim_pass  / $none));
          $spf_comp_pct  = number_format(100 * ($spf_align  / $none));
          $spf_pass_pct  = number_format(100 * ($spf_pass   / $none));
        }
        else {
          // sometimes we get entries that are full reject
          $dkim_comp_pct = 0;
          $dkim_pass_pct = 0;
          $spf_comp_pct  = 0;
          $spf_pass_pct  = 0;
        }

        // now present
        echo "
              <div class=dov-bar-in-ip>\n
                <div style='width:400px'>\n
                  <h3 class=dov-bar-in-ip-h3><a href='".$_SERVER['PHP_SELF']."?range=$dateRange&page=sender&domain=$domain&ip=".$ip['ip']."'>".$ip['ip']."</a></h3>\n
                  <span class=dov-bar-small>".gethostbyaddr($ip['ip'])."</span>\n
                </div>\n
                <div style='left:450px; width:calc(100% - 870px);'>\n
                  <table class=dov>\n
                    <tr class=dov>\n
                      <td class=dov style='min-width:100px'><strong>Messages</strong></td>\n
                      <td class=dov style='min-width:100px'><strong>Compliant</strong></td>\n
                      <td class=dov style='min-width:100px'><strong>Quarantined</strong></td>\n
                      <td class=dov style='min-width:100px'><strong>Rejected</strong></td>\n
                    </tr>\n
                    <tr class=dov>\n
                      <td class=dov>$messages</td>\n
                      <td class=dov>$dmarc_comp_pct%</td>\n
                      <td class=dov>";
        if ($quarantine > 0) {
          echo "
                        <span class='warn'>$quarantine</span>";
        }
        else {
          echo "
                        $quarantine";
        }
        echo "
                      </td>\n
                      <td class=dov>";
        if ($quarantine > 0) {
          echo "
                        <span class='fail'>$reject</span>";
        }
        else {
          echo "
                        $reject";
        }
        echo "
                      </td>\n
                    </tr>\n
                  </table>\n
                </div>\n
                <div style='right:20px;width:350px'>\n
                  <span class=perc-text style='text-align:left'>DKIM</span>\n
                  <div class=perc-bar>\n
                    <div class=gray-per style='width:$dkim_pass_pct%'></div>\n
                    <div class=green-per style='width:$dkim_comp_pct%'></div>\n
                  </div>\n
                  <span class=perc-text style='text-align:left'>SPF</span>\n
                  <div class=perc-bar>\n
                    <div class=gray-per style='width:$spf_pass_pct%'></div>\n
                    <div class=green-per style='width:$spf_comp_pct%'></div>\n
                  </div>\n
                </div>\n
              </div>\n";
      }

      echo "  
            </div>\n
          </div>\n";
    }
    else {
      echo "
          <div class=dov-bar style='height: 100px'>\n
            <div class=dov-bar-in style='height: 100px'>\n
              <div class=dov-bar-in-ip>\n
                <div>\n
                  <h3 class=dov-bar-in-ip-h3>No DMARC Data</h3\n
                </div>\n
              </div>\n
            </div>\n
          </div\n";
    }
  }
}

// Sender Details -------------------------------
function sender_details($geo_data, $stats, $domain, $dateRange, $ip) {
  $hostname = gethostbyaddr($ip) ?: '';
  $org      = '';
  $city     = '';
  $region   = '';
  $country  = '';
  $lon      = '';
  $lat      = '';

  if (GEO_ENABLE) {
    if (array_key_exists('city',$geo_data))         { $city     = $geo_data['city']['names']['en']; }
    if (array_key_exists('subdivisions',$geo_data)) { $region   = $geo_data['subdivisions']['0']['names']['en']; }
    if (array_key_exists('country',$geo_data))      { $country  = $geo_data['country']['names']['en']; }
    if (array_key_exists('location',$geo_data)) { 
      $lat      = $geo_data['location']['latitude'];
      $lon      = $geo_data['location']['longitude'];
    }
  }
  else {
    $org = $geo_data['regrinfo']['owner']['organization'] ?: '';
  }

  // present the data, obi-wan
  if (GEO_ENABLE) {
    echo "<div class=dov-bar style='margin-top: 0;height:400px;'>\n
            <div class=dov-bar-in style='height:400px;'>\n
              <div class=geo-left>\n
                <div class=geo-left-inner>\n";
  }
  else {
    echo "<div class=dov-bar style='margin-top: 0;height:100px;'>\n
            <div class=dov-bar-in style='height:100px;'>\n
              <div class=geo-left style='height:100px;'>\n
                <div class=geo-left-inner>\n";
  }

  if ($ip != '')       { echo "$ip<br />\n"; }
  if ($hostname != '') { echo "$hostname<br />\n"; }
  if ($org != '')      { echo "$org<br />\n"; }
  if ($city != '')     { echo "$city<br />"; }
  if ($region != '')   { echo "$region<br />"; }
  if ($country != '')  { echo "$country<br />"; }

  echo "<br />\n";

  echo "        </div>\n
              </div>\n";

  // if there's no maxmind data, then there's no map to find
  if (GEO_ENABLE) {
    echo "    <div class=geo-right>\n
    <iframe width='100%' height='100%' src='https://maps.google.com/maps?q=$lat,$lon&z=3&output=embed'></iframe>\n
              </div>\n";
  }

  echo "    </div>\n
          </div>\n";

  if (count($stats) > 0) {
    echo "<table style='margin: 30px auto 0 auto' id='dmarc_reports' class='centered'>\n
            <thead>\n
              <tr>\n
                <th>Report ID</th>\n
                <th>Message Count</th>\n
                <th>Disposition</th>\n
                <th>Reason</th>\n
                <th>DKIM</th>\n
                <th>SPF</th>\n
              </tr>\n
            </thead>\n";
  }
  
  foreach ($stats as $stat) {
    $stat       = array_map('htmlspecialchars',$stat);
    $dkimresult = $stat['dkimresult'] ?: 'unknown';
    $dkim_align = $stat['dkim_align'] ?: 'unknown';
    $spfresult  = $stat['spfresult']  ?: 'unknown';
    $spf_align  = $stat['spf_align']  ?: 'unknown';
    echo "<tr>\n
            <td><a href='".$_SERVER['PHP_SELF']."?page=report&report=".$stat['reportid']."'>".$stat['reportid']."</a></td>\n
            <td>".$stat['rcount']."</td>\n
            <td>";
    if ($stat['disposition'] == "quarantine") {
      echo "<span class='warn'>".$stat['disposition']."</span>";
    }
    elseif ($stat['disposition'] == "reject") {
      echo "<span class='fail'>".$stat['disposition']."</span>";
    }
    else {
      echo $stat['disposition'];
    }
    echo "</td>\n
            <td>".$stat['reason']."</td>\n
            <td>";
    if ($stat['dkimdomain'] != '') {
      echo "Signed by <span class=signed>".$stat['dkimdomain']."</span><br />\n
              Result: <span class=$dkimresult>$dkimresult</span> | 
              Alignment: <span class=$dkim_align>$dkim_align</span></td>\n";
    }
    else {
      echo "Not Signed</td>\n";
    }
    echo "  <td>Envelope from <span class=signe'>".$stat['spfdomain']."</span><br />\n
                Result: <span class=$spfresult>$spfresult</span> | 
                Alignment: <span class=$spf_align>$spf_align</span></td>\n
          </tr>\n";
  }

  if (count($stats) > 0) { echo "</table>\n"; }

}

function report_details($data, $report) {

  if ($data[0]['ip6'] != '') { $ip = $data[0]['ip6']; }
  $data[0] = array_map('htmlspecialchars',$data[0]);
  if ($data[0]['ip6'] != '') { $data[0]['ip6'] = $ip; }

  if ($data[0]['policy_adkim'] == 'r')      { $dkim_policy = 'Relaxed'; }
  else if ($data[0]['policy_adkim'] == 's') { $dkim_policy = 'Strict'; }
  else                                      { $dkim_policy = 'unknown'; }
  if ($data[0]['policy_aspf'] == 'r')       { $spf_policy = 'Relaxed'; }
  else if ($data[0]['policy_aspf'] == 's')  { $spf_policy = 'Strict'; }
  else                                      { $spf_policy = 'unknown'; }

  // report details
  echo "<h1 style='margin-bottom: 20px'>Details for Report $report</h1>\n
        <div class=dov-bar style='margin-top: 0;height:200px;'>\n
          <div class=dov-bar-in style='height:200px;'>\n
            <div class=report-left>\n
              <div class=report-inner>\n";

  if ($data[0]['mindate'] != '' 
   && $data[0]['maxdate'] != '')            { echo "Date Range<br />\n"; }
  if ($data[0]['org'] != '')                { echo "Reporting Organization<br />\n"; }
  if ($data[0]['email'] != '')              { echo "Report Origin Email<br />\n"; }
  if ($data[0]['extra_contact_info'] != '') { echo "Contact Info<br />\n"; }
  if ($data[0]['policy_p'] != ''
   && $data[0]['policy_pct'] != '')         { echo "DMARC Policy<br />\n"; }
  if ($data[0]['policy_adkim'] != '')       { echo "DKIM Policy<br />\n"; }
  if ($data[0]['policy_aspf'] != '')        { echo "SPF Policy<br />\n"; }

  echo "      </div>\n
            </div>\n
            <div class=report-right>\n
              <div class=report-inner>\n";

  if ($data[0]['mindate'] != '' 
   && $data[0]['maxdate'] != '')            { echo $data[0]['mindate']." - ".$data[0]['maxdate']."<br />\n"; }
  if ($data[0]['org'] != '')                { echo $data[0]['org']."<br />\n"; }
  if ($data[0]['email'] != '')              { echo $data[0]['email']."<br />\n"; }
  if ($data[0]['extra_contact_info'] != '') { echo $data[0]['extra_contact_info']."<br />\n"; }
  if ($data[0]['policy_p'] != ''
   && $data[0]['policy_pct'] != '')         { echo ucfirst($data[0]['policy_p'])." ".$data[0]['policy_pct']."%<br />\n"; }
  if ($data[0]['policy_adkim'] != '')       { echo "$dkim_policy<br />\n"; }
  if ($data[0]['policy_aspf'] != '')        { echo "$spf_policy<br />\n"; }

  echo "      </div>\n
            </div>\n
          </div>\n
        </div>\n";

  echo "<table style='margin: 30px auto 0 auto' id='dmarc_reports' class='centered'>\n
          <thead>\n
            <tr>\n
              <th>Sender IP</th>\n
              <th>RFC5322 Domain</th>\n
              <th>Message Count</th>\n
              <th>Disposition</th>\n
              <th>Reason</th>\n
              <th>DKIM</th>\n
              <th>SPF</th>\n
            </tr>\n
          </thead>\n";
  
  foreach ($data as $row) {
    $ip         = get_ip($row['ip'],$row['ip6']);
    $row        = array_map('htmlspecialchars',$row);
    $dkimresult = $row['dkimresult'] ?: 'unknown';
    $dkim_align = $row['dkim_align'] ?: 'unknown';
    $spfresult  = $row['spfresult']  ?: 'unknown';
    $spf_align  = $row['spf_align']  ?: 'unknown';
    echo "<tr>\n
            <td><a href='".$_SERVER['PHP_SELF']."?page=sender&ip=".$ip['ip']."'>".$ip['ip']."</a></td>\n
            <td>".$row['domain']."</td>\n
            <td>".$row['rcount']."</td>\n
            <td>";
    if ($row['disposition'] == "quarantine") {
      echo "<span class='warn'>".$row['disposition']."</span>";
    }
    elseif ($row['disposition'] == "reject") {
      echo "<span class='fail'>".$row['disposition']."</span>";
    }
    else {
      echo $row['disposition'];
    }
    echo "</td>\n
            <td>".$row['reason']."</td>\n
            <td>";
    if ($row['dkimdomain'] != '') {
      echo "Signed by <span class=signed>".$row['dkimdomain']."</span><br />\n
              Result: <span class=$dkimresult>$dkimresult</span> | 
              Alignment: <span class=$dkim_align>$dkim_align</span></td>\n";
    }
    else {
      echo "Not Signed</td>\n";
    }
    echo "  <td>Envelope from <span class=signed>".$row['spfdomain']."</span><br />\n
                Result: <span class=$spfresult>$spfresult</span> | 
                Alignment: <span class=$spf_align>$spf_align</span></td>\n
          </tr>\n";
  }
  
  echo "</table>\n";
}
?>
