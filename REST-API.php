<?php
function searchPlace($place) {
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($place);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP-map-opdracht/1.0");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ["error" => "Request failed: $error"];
    }

    $data = json_decode($response, true);

    if (empty($data)) {
        return ["error" => "No results found."];
    }

    return [
        "name" => $data[0]["display_name"],
        "lat"  => (float) $data[0]["lat"],
        "lon"  => (float) $data[0]["lon"],
    ];
}

$place = $_GET["place"] ?? "Roermond";
$result = searchPlace($place);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Kaart met marker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 500px; margin-top: 10px; }
    </style>
</head>
<body>
    <form method="get">
        <input type="text" name="place" value="<?= htmlspecialchars($place) ?>">
        <button type="submit">Zoek</button>
    </form>

    <?php if (isset($result["error"])): ?>
        <p><?= htmlspecialchars($result["error"]) ?></p>
    <?php else: ?>
        <p><?= htmlspecialchars($result["name"]) ?><br>
           Latitude: <?= $result["lat"] ?>, Longitude: <?= $result["lon"] ?></p>
        <div id="map"></div>
        <script>
            const lat = <?= json_encode($result["lat"]) ?>;
            const lon = <?= json_encode($result["lon"]) ?>;
            const name = <?= json_encode($result["name"]) ?>;

            const map = L.map("map").setView([lat, lon], 13);
            L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; OpenStreetMap-bijdragers"
            }).addTo(map);
            L.marker([lat, lon]).addTo(map).bindPopup(name).openPopup();
        </script>
    <?php endif; ?>
</body>
</html>