<?php
/*
OpenDAnalyzer - Open Source DMARC Analyzer
templates/openda/header.php
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

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="templates/openda/style.css" />

		<script>
			window.onload = function() {
				const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

				const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
					v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
					)(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

				// do the work...
				document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
					const table = th.closest('table');
						Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
							.sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
							.forEach(tr => table.appendChild(tr) );
				})));
			}
		</script>

		<title><?php page_title(); ?></title>
	</head>
	<body>
		<div id="wrapper">

