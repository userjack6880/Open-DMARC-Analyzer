<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
includes/template.php
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

// Versioning -----------------------------------------------------------------
function oda_version() {
	echo "0-&alpha;8";
}

// Javascripts ----------------------------------------------------------------
function javascript() { ?>
<?php }

// General Page Templates -----------------------------------------------------

// Page Title -----------------------------------
function page_title($page) {
	echo "Open DMARC Analyzer";
	if ($page == "index") {
		echo " - Dashboard";
	}
}

// Control Bar ----------------------------------
function control_bar($page, $domain, $dateRange, $ip = '') {
	$startdate = date("Y-m-d H:i:s",strtotime(strtolower("-".dateNum($dateRange)." ".dateWord($dateRange))));

	// pages that need domain controls
	if ($page == "index" || $page == "sender") {
		echo "<div id=controlbar>\n";

		$domains = getDomains($dateRange);
		if (count($domains) == 1) {
			$domain = $domains[0]['domain'];
		}

		// Show if all domains are being shown or a single domain
		echo "<div id=controlbarleft>\n";
		if ($page == "index" ) {
			if ($domain == "all") {
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
				      Since $startdate\n";
			}
			else {
				echo "<h1>Sender $ip for $domain</h1><br />\n
				      Since $startdate\n";
			}
		}
		echo "</div>\n";

		// Domain Selection and Date Selection
		echo "<div id=controlbarright>\n";
		?><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<select name="domain">
				<option value="all">All Domains</option>
		<?php
		foreach ($domains as $listDomain) {
			?><option value="<?php echo $listDomain['domain']; ?>" <?php if($listDomain['domain'] == $domain) { echo "selected"; } ?>>
			<?php echo $listDomain['domain']; ?></option><?php
		}
		?></select>
		<input type="hidden" name="page" value="<?php echo $page; ?>">
		<input type="hidden" name="range" value="<?php echo $dateRange; ?>">
		<input type="submit" value="Go"></form><br />
		<?php

		// get some variables out of current daterange
		$dateWord = dateWord($dateRange);
		$dateLtr = dateLtr($dateRange);
 	 $dateNum = dateNum($dateRange);
	
		// date selection -1 unit in config
		$datePrev = $dateNum+1;
		echo "Range Start: &#91; <a href=\"".$_SERVER['PHP_SELF']."?range=-$datePrev$dateLtr&page=$page&domain=$domain\">&larr; 1 $dateWord</a>";
	
		// date selectoin +1 unit in config
		if ($dateNum == 1) {
			echo " &#93;\n";
		}
		else {
			$dateNext = $dateNum-1;
			echo " | <a href=\"".$_SERVER['PHP_SELF']."?range=-$dateNext$dateLtr&page=$page&domain=$domain\">1 $dateWord &rarr;</a> &#93;\n";
		}

		echo "</div>\n";

		// close up the control bar
		echo "</div>\n";

	}
	

	else {
		return; // if it's not the specified pages, this controlbar is irrelvant
	}
}

// Overview Bar ---------------------------------
function overview_bar($stats, $domain) {
	// extract stats
	$total = 0;
	$policy = '';
	$policy_pct = 0;
	$dmarc_none = 0;
	$dmarc_quar = 0;
	$dmarc_rjct = 0;
	$dmarc_comp = 0;
	$dkim_pass_aligned = 0;
	$dkim_pass_noalign = 0;
	$spf_pass_aligned = 0;
	$spf_pass_noalign = 0;

	if ($domain == "all") {
		$domain_count = 0;
		foreach ($stats as $stat) {
			$total = $total+$stat['total_messages'];
			$dmarc_none = $dmarc_none+$stat['none'];
			$dmarc_quar = $dmarc_quar+$stat['quarantine'];
			$dmarc_rjct = $dmarc_rjct+$stat['reject'];
			$dmarc_comp = $dmarc_comp+$stat['compliant'];
			$dkim_pass_aligned = $dkim_pass_aligned+$stat['dkim_pass_aligned'];
			$dkim_pass_noalign = $dkim_pass_noalign+$stat['dkim_pass_unaligned'];
			$spf_pass_aligned = $spf_pass_aligned+$stat['spf_pass_aligned'];
			$spf_pass_noalign = $spf_pass_noalign+$stat['spf_pass_unaligned'];
			$domain_count++;
		}

		// clunky, but detects if we have more than one domain, and changes all to a single domain if it's just one
		if ($domain_count == 1) {
			$domain = $stats[0]['domain'];
		}
	}
	else {
		$total = $stats[0]['total_messages'];
		$policy = ucfirst($stats[0]['policy_p']);
		$policy_pct = $stats[0]['policy_pct'];
		if ($stats[0]['none'] > 0) {       $dmarc_none = $stats[0]['none']; }
		if ($stats[0]['quarantine'] > 0) { $dmarc_quar = $stats[0]['quarantine']; }
		if ($stats[0]['reject'] > 0) {     $dmarc_rjct = $stats[0]['reject']; }
		if ($stats[0]['compliant'] > 0) {  $dmarc_comp = $stats[0]['compliant']; }
		if ($stats[0]['dkim_pass_aligned'] > 0) {   $dkim_pass_aligned = $stats[0]['dkim_pass_aligned']; }
		if ($stats[0]['dkim_pass_unaligned'] > 0) { $dkim_pass_noalign = $stats[0]['dkim_pass_unaligned']; }
		if ($stats[0]['spf_pass_aligned'] > 0) {   $spf_pass_aligned = $stats[0]['spf_pass_aligned']; }
		if ($stats[0]['spf_pass_unaligned'] > 0) { $spf_pass_noalign = $stats[0]['spf_pass_unaligned']; }
	}

	// stat calculations
	$dmarc_comp_pct = number_format(100 * ($dmarc_comp / $dmarc_none));
	$dkim_comp_pct  = number_format(100 * ($dkim_pass_aligned / $dmarc_none));
	$dkim_pass_pct  = number_format(100 * (($dkim_pass_aligned + $dkim_pass_noalign) / $dmarc_none));
	$spf_comp_pct   = number_format(100 * ($spf_pass_aligned  / $dmarc_none));
	$spf_pass_pct   = number_format(100 * (($spf_pass_aligned  + $spf_pass_noalign)  / $dmarc_none));

	// overview details
	echo "<div id=overviewbar>\n
	        <div id=overviewbarleft>\n
	          <div id=overviewinnerleft>\n
	            Total Messages<br />\n
	            <span class=overviewtotal>$total</span>\n";
	if ($domain != "all") {
		echo     "<br />\n$policy_pct% $policy\n";
	}
	echo     "</div>\n
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
	        </div>\n
	        <div id=overviewbarright>\n
	          <div id=ovbr-in>\n
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
	            <span class=perc-text>$spf_comp_pct% Aligned | $spf_pass_pct% Passed</span><br />\n
	          </div>\n
	        </div>\n
	      </div>\n";

	// returns a modified domain if only one is detected
	return $domain;
}

// Overview Bar ---------------------------------
function domain_overview($stats, $dateRange) {
	foreach ($stats as $stat) {
		// extract stats
		$dmarc_none = 0;
		$dmarc_quar = 0;
		$dmarc_rjct = 0;
		$dmarc_comp = 0;
		$dkim_pass_aligned = 0;
		$dkim_pass_noalign = 0;
		$spf_pass_aligned = 0;
		$spf_pass_noalign = 0;

		$domain = $stat['domain'];
		$total = $stat['total_messages'];
		$policy = ucfirst($stat['policy_p']);
		$policy_pct = $stat['policy_pct'];
		if ($stats[0]['none'] > 0) {       $dmarc_none = $stat['none']; }
		if ($stats[0]['quarantine'] > 0) { $dmarc_quar = $stat['quarantine']; }
		if ($stats[0]['reject'] > 0) {     $dmarc_rjct = $stat['reject']; }
		if ($stats[0]['compliant'] > 0) {  $dmarc_comp = $stat['compliant']; }
		if ($stats[0]['dkim_pass_aligned'] > 0) {   $dkim_pass_aligned = $stat['dkim_pass_aligned']; }
		if ($stats[0]['dkim_pass_unaligned'] > 0) { $dkim_pass_noalign = $stat['dkim_pass_unaligned']; }
		if ($stats[0]['spf_pass_aligned'] > 0) {   $spf_pass_aligned = $stat['spf_pass_aligned']; }
		if ($stats[0]['spf_pass_unaligned'] > 0) { $spf_pass_noalign = $stat['spf_pass_unaligned']; }

		$sender_count = getSenderCount($dateRange, $domain);

		// stat calculations
		$dmarc_comp_pct = number_format(100 * ($dmarc_comp / $dmarc_none));
		$dkim_comp_pct  = number_format(100 * ($dkim_pass_aligned / $dmarc_none));
		$dkim_pass_pct  = number_format(100 * (($dkim_pass_aligned + $dkim_pass_noalign) / $dmarc_none));
		$spf_comp_pct   = number_format(100 * ($spf_pass_aligned  / $dmarc_none));
		$spf_pass_pct   = number_format(100 * (($spf_pass_aligned  + $spf_pass_noalign)  / $dmarc_none));

		// overview details
		echo "<div class=dov-bar>\n
		        <div class=dov-bar-in>\n
		          <div class=dov-bar-in-domain>\n
		            <h1 class=dov-bar-head>$domain - <a href='".$_SERVER['PHP_SELF']."?range=$dateRange&page=index&domain=$domain'>$sender_count Senders</a></h1>\n
		          </div>\n
		          <div class=dov-bar-in-dstats-alignment>\n
		            <span class=perc-text style='text-align:left'>DMARC Compliance</span>\n
		            <div class=perc-bar>\n
		              <div class=green-per style='width:$dmarc_comp_pct%'></div>\n
		            </div>\n
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
		          <div class=dov-bar-in-dstats-totals>\n
		            <table class=dov>\n
		              <tr class=dov>\n
		                <td class=dov>$total Messages</td>\n
		                <td class=dov>$policy_pct% $policy</td>\n
		                <td class=dov>$dmarc_quar Quarantined</td>\n
		                <td class=dov>$dmarc_rjct Rejected</td>\n
		              </tr>\n
		            </table>\n
		          </div>\n
		        </div>\n
		      </div>\n";
	}
}

// Domain Details -------------------------------
function domain_details($stats, $dateRange) {
	$entries = count($stats);
	$height = $entries * 100;
	echo "<h2 class=section>Domain Summary</h2>\n
	      <div class=dov-bar style='height:".$height."px'>\n
	        <div class=dov-bar-in style='height:".$height."px'>\n";

	foreach ($stats as $stat) {
		$compliant = 0;
		$none = 0;
		$quarantine = 0;
		$reject = 0;
		$dkim_pass = 0;
		$dkim_align = 0;
		$spf_pass = 0;
		$spf_align = 0;

		// extract stats - this'll be sorted by senderIP
		$ip         = get_ip($stat['ip'], $stat['ip6']);
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
		$dkim_comp_pct  = number_format(100 * ($dkim_align / $none));
		$dkim_pass_pct  = number_format(100 * ($dkim_pass  / $none));
		$spf_comp_pct   = number_format(100 * ($spf_align  / $none));
		$spf_pass_pct   = number_format(100 * ($spf_pass   / $dmarc_none));

		// now present
		echo "<div class=dov-bar-in-ip>\n
		        <div style='width:400px'>\n
		          <h3 class=dov-bar-in-ip-h3><a href=''>".$ip['ip']."</a></h3>\n
		          <span class=dov-bar-small>".gethostbyaddr($ip['ip'])."</span>\n
		        </div>\n
		        <div style='left:420px;'>\n
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
		              <td class=dov>$quarantine</td>\n
		              <td class=dov>$reject</td>\n
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

	echo "  </div>\n
	      </div>\n";
}
?>
