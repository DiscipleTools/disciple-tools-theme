<?php 
	if(isset($_GET['county'])) {
		$county = $_GET['county'];
		$where = "WHERE COUNTYFP = '$county'";
	} elseif (isset($_GET['tract'])) {
		$tract = $_GET['tract'];
		$where = "WHERE TRACTCE = '$tract'";
		
	} else {
		$where = "";
	}
	?>

<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>KML Click Capture Sample</title>
    <style>
     /* Always set the map height explicitly to define the size of the div
 * element that contains the map. */
#map {
  height: 100%;
}
/* Optional: Makes the sample page fill the window. */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}
    </style>
  </head>
  <body>
    <div id="map"></div>

    <script>
    // This example creates a simple polygon representing the Bermuda Triangle.

		function initMap() {
		  var map = new google.maps.Map(document.getElementById('map'), {
		    zoom: 5,
		    center: {lng: -104.884664, lat: 39.747467},
		    mapTypeId: 'terrain'
		  });
		
		  // Define the LatLng coordinates for the polygon's path.
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
				
				$sql = "SELECT * FROM Coordinates $where";
				$results = mysqli_query($conn, $sql);
				$rowCount = mysqli_affected_rows($conn);
				$count = 1;
				
				print "var coords = [";
				
				foreach($results as $value) {
					$coordinates = $value['coordinates'];
					if ($rowCount > $count ) {$coordinates .= ",";}
					$count++;
					print $coordinates;
				}
				
				print "];";
			
			?>  

		  
			
			var tracts = [];
			
			for (i = 0; i < coords.length; i++) {
				tracts.push(new google.maps.Polygon({
				    paths: coords[i],
				    strokeColor: '#FF0000',
				    strokeOpacity: 0.8,
				    strokeWeight: 2,
				    fillColor: '',
				    fillOpacity: 0.1
				  }));
				  
				  tracts[i].setMap(map);
				  
				}	  
		  
		}
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcddCscCo-Uyfa3HJQVe0JdBaMCORA9eY&callback=initMap">
    </script>
  </body>
</html>