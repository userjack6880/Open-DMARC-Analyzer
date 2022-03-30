<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
install.php
2022 - John Bradley (userjack6880)

Available at: https://github.com/userjack6880/Open DMARC Analyzer

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

// Includes
include_once 'includes.php';

// Connect to database

echo "connecting to database...";
$pdo = dbConn();
echo " success<br>";

// Read in file and build statement
$statement ='';

echo "opening file...";
$lines = file('mysql.sql');
echo " success<br>";

foreach ($lines as $line)
{
	echo "&rarr; $line<br>";
	// skip comments
	if (substr($line, 0, 2) == '--' || $line == '') {	continue; }

	// add line to statement
	$statement .= $line;

	// check for end of query and run it
	if (substr(trim($line), -1, 1) == ';') {
		try {
			echo "performing query...";
			$query = $pdo->prepare($statement);
			$query->execute();

			if($query->errorCode() != 0) {
				$errors = $query->errorInfo();
				echo " failed: ".$errors[2]."<br>";
				exit();
			}
		}
		catch (PDOException $e) {
			echo " failed: ".$e->getMessage()."<br>";
			exit();
		}
		echo " success<br>";
		$query = NULL;
		$statement = '';
	}
}

echo "database successfully updated<br>";

// kill the PDO
$pdo = NULL;

echo "deleting installation files<br>";

if (unlink('mysql.sql') == true) {
	echo "DELETED &rarr; mysql.sql<br>";
}
else {
	echo "FAILED &rarr; mysql.sql<br>";
}

if (unlink(__FILE__) == true) {
	echo "DELETED &rarr; install.php<br>";
}
else {
	echo "FAILED &rarr; install.php<br>";
}
?>
