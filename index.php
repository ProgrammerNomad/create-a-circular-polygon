<?php

/**
 * Creates a geofence array in a 10 km radius from a given latitude and longitude.
 *
 * @param float $latitude The latitude of the center point.
 * @param float $longitude The longitude of the center point.
 * @param int $numberOfPoints The number of points in the geofence.
 *
 * @return array An array of latitude/longitude pairs representing the geofence.
 */
function createGeofence(float $latitude, float $longitude, int $numberOfPoints = 36): array
{
    $radius = 10; // Radius in kilometers

    $points = [];
    $angle = 360 / $numberOfPoints;

    for ($i = 0; $i < $numberOfPoints; $i++) {
        $bearing = $angle * $i;
        $points[] = calculateCoordinate($latitude, $longitude, $bearing, $radius);
    }

    return $points;
}

/**
 * Calculates a coordinate given a starting point, bearing, and distance.
 *
 * @param float $latitude The latitude of the starting point.
 * @param float $longitude The longitude of the starting point.
 * @param float $bearing The bearing in degrees.
 * @param float $distance The distance in kilometers.
 *
 * @return array An array containing the latitude and longitude of the calculated coordinate.
 */
function calculateCoordinate(float $latitude, float $longitude, float $bearing, float $distance): array
{
    $earthRadius = 6371; // Earth's radius in kilometers

    $latRad = deg2rad($latitude);
    $lonRad = deg2rad($longitude);
    $bearingRad = deg2rad($bearing);

    $newLatRad = asin(
        sin($latRad) * cos($distance / $earthRadius) +
        cos($latRad) * sin($distance / $earthRadius) * cos($bearingRad)
    );

    // Check if asin() returns a valid number
    if (is_nan($newLatRad)) {
        echo "Error: asin() returned NaN. Please check input values.\n";
        return [
            'latitude' => $latitude, // Return original latitude on error
            'longitude' => $longitude, // Return original longitude on error
        ];
    }

    $newLonRad = $lonRad + atan2(
        sin($bearingRad) * sin($distance / $earthRadius) * cos($latRad),
        cos($distance / $earthRadius) - sin($latRad) * sin($newLatRad)
    );

    return [
        'latitude' => rad2deg($newLatRad),
        'longitude' => rad2deg($newLonRad),
    ];
}

// Get the user's current location (for testing)
$userLatitude = 25.1422131; // Example latitude
$userLongitude = 81.4358595; // Example longitude

// Generate geofence points
$geofencePoints = createGeofence($userLatitude, $userLongitude);

// Validate and sanitize the geofence points
$validGeofencePoints = [];
foreach ($geofencePoints as $point) {
    if (
        isset($point['latitude']) && is_numeric($point['latitude']) && 
        isset($point['longitude']) && is_numeric($point['longitude']) &&
        !is_nan($point['latitude']) && !is_nan($point['longitude'])
    ) {
        $validGeofencePoints[] = [(float)$point['latitude'], (float)$point['longitude']];
    } else {
        echo "Invalid coordinate found: ";
        print_r($point);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Geofence Example</title>
  <style>
    #map { height: 400px; }
  </style>
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script> 
</head>
<body>

  <h1>Geofence Example</h1>

  <label for="latitude">Latitude:</label>
  <input type="text" id="latitude" value="<?php echo $userLatitude; ?>" readonly><br><br>

  <label for="longitude">Longitude:</label>
  <input type="text" id="longitude" value="<?php echo $userLongitude; ?>" readonly><br><br>

  <button id="showGeofence">Show Geofence</button>

  <div id="map"></div>

  <h2>Geofence Points:</h2>
  <pre><?php print_r($geofencePoints); ?></pre>

  <script>
    function initMap() {
      const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 13,
        center: { lat: <?php echo $userLatitude; ?>, lng: <?php echo $userLongitude; ?> },
      });

      var geofencePoints = <?php echo json_encode($validGeofencePoints); ?>;

      const geofenceCoords = geofencePoints.map(point => ({
        lat: point[0], 
        lng: point[1]  
      }));

      const geofencePolygon = new google.maps.Polygon({
        paths: geofenceCoords,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
      });

      document.getElementById('showGeofence').addEventListener('click', function() {
        geofencePolygon.setMap(map);
      });
    }
  </script>

</body>
</html>