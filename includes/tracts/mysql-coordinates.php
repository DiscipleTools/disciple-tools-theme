<?php
	// Took the .kml file to codebeautify.org and converted it to .json and put into a local .json file.
		$servername = "localhost";
		$username = "root";
		$password = "chasm";
		$database = "mapping_data";
		
		// Create connection
		$conn = new mysqli($servername, $username, $password, $database);
		
		
		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		} 
		
		$sql = 'SELECT * FROM Coordinates';
		$results = mysqli_query($conn, $sql);
		$rowCount = mysqli_affected_rows($conn);
		$count = 1;
		
		foreach($results as $value) {
			$coordinates = $value['coordinates'];
			if ($rowCount > $count ) {$coordinates .= ",";}
			$count++;
			print $coordinates;
		}
	
	?>