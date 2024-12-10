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

  $newLatRad = asin(sin($latRad) * cos($distance / $earthRadius) + cos($latRad) * sin($distance / $earthRadius) * cos($bearingRad));
  $newLonRad = $lonRad + atan2(sin($bearingRad) * sin($distance / $earthRadius) * cos($latRad), cos($distance / $earthRadius) - sin($latRad) * sin($newLatRad));

  return [
    'latitude' => rad2deg($newLatRad),
    'longitude' => rad2deg($newLonRad),
  ];
}

// Get the user's current location (replace with actual implementation)
$userLatitude = 28.6139; // Example latitude
$userLongitude = 77.4403; // Example longitude

// Generate geofence points
$geofencePoints = createGeofence($userLatitude, $userLongitude);

?>

<!DOCTYPE html>
<html>
<head>
  <title>Geofence Example</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    #map { height: 400px; }
  </style>
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

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    var map = L.map('map').setView([<?php echo $userLatitude; ?>, <?php echo $userLongitude; ?>], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var geofencePoints = <?php echo json_encode($geofencePoints); ?>;

    var polygon = L.polygon(geofencePoints); // Create polygon but don't add it yet

    document.getElementById('showGeofence').addEventListener('click', function() {
      polygon.addTo(map); // Add polygon to map when button is clicked
    });
  </script>

</body>
</html>