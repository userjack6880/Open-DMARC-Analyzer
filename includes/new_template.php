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

// Versioning
function oda_version() {
	echo "0-&alpha;8-devPre";
}

// General Page Templates
function page_title($page) {
	echo "Open DMARC Analyzer";
	if ($page == "index") {
		echo " - Dashboard";
	}
}

// Javascripts
function javascript() { ?>
<?php }

// Control Bar
function control_bar($page, $domain, $dateRange) {
	if ($page == "index") {
		echo "<div id=controlbar>\n";
	}
	else {
		return; // if it's not the specified pages, this controlbar is irrelvant
	}
	// Show if all domains are being shown or a single domain
	echo "<div id=controlbarleft>\n";
	if ($domain == "all") {
		echo "<h2>All Domains</h2>";
	}
	else {
		echo "<h2>$domain</h2>";
	}
	echo "</div>\n";

	// Domain Selection and Date Selection
	$domains = getDomains();
	echo "<div id=controlbarright>\n";
	?><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<select name="domain">
			<option value="all">All Domains</option>
	<?php
	foreach ($domains as $listDomain) {
		?><option value="<?php echo $listDomain['domain']; ?>"><?php echo $listDomain['domain']; ?></option><?php
	}
	?></select>
	<input type="hidden" name="page" value="<?php echo $page; ?>">
	<input type="hidden" name="range" value="<?php echo $dateRange; ?>">
	<input type="submit" value="Go"></form><br />
	<?php

	// get some variables out of current daterange
	preg_match('/\-(\d+)(\w)/', $dateRange, $match);
	if ($match[2] == 'd') {
		$dateWord = "Day";
	}
	elseif ($match[2] == 'w') {
		$dateWord = "Week";
	}
	else {
		$dateWord = "Month";
	}

	// date selection -1 unit in config
	$datePrev = $match[1]+1;
	echo "Range Start: &#91; <a href=\"".$_SERVER['PHP_SELF']."?range=-$datePrev$match[2]&page=$page&domain=$domain\">&larr; 1 $dateWord</a>";
	
	// date selectoin +1 unit in config
	if ($match[1] == 1) {
		echo " &#93;\n";
	}
	else {
		$dateNext = $match[1]-1;
		echo " | <a href=\"".$_SERVER['PHP_SELF']."?range=-$dateNext$match[2]&page=$page&domain=$domain\">1 $dateWord &rarr;</a> &#93;\n";
	}

	echo "</div>\n";

	// close up the control bar
	echo "</div>\n";
}

?>