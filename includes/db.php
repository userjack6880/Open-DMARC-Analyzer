<?php
/*
Open DMARC Analyzer - Open Source DMARC Analyzer
includes/db.php
2020 - John Bradley (userjack6880)

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
	debug("Connecting to DB");
	try {
		$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT, DB_USER, DB_PASS);
	} catch (PDOException $e) {
		echo 'Connection failed: '.$e->getMessage();
	}
	debug("Connection Successful\n");
	return $pdo;
}

?>
