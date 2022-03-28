<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
includes/db.php
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

// Connect to DB //
function dbConn() {
	try {
		$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT, DB_USER, DB_PASS);
	} 
	catch (PDOException $e) {
		echo 'Connection failed: '.$e->getMessage();
	}
	return $pdo;
}

// Perform a Query
function dbQuery($pdo, $statement, $params) {
	if(!isset($statement)) {
		echo "No query statement given!";
		die;
	}
	else {
		try {
			$query = $pdo->prepare($statement);
			if (isset($params)) {
				$query->execute($params);
			}
			else {
				$query->execute();
			}
		}
		catch (PDOException $e) {
			echo 'Could not perform query: '.$e->getMessage();
		}

		$rows = [];

		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$row = array_map('htmlspecialchars', $row);
			array_push($rows, $row);
		}
		$query = null;
		return $rows;
	}
}
?>
