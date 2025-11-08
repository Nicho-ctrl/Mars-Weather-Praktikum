<?php
// === Mars Weather Projekt ===
// Hauptskript zum Abrufen, Cachen und Anzeigen der Mars-Wetterdaten

// NASA API URL
$marsApi = "https://api.nasa.gov/insight_weather/?api_key=7YR4MrcXDHcnGN2yvmvr3n2pCe1YVbzB2jhb4Lys&feedtype=json&ver=1.0";

include "functions.php";

// Cache-Setup
$cacheFile = __DIR__ . '/mars_cache.json';
$cacheTime = 60 * 10; // 10 Minuten gültig

// === 1. Cache laden, falls aktuell ===
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $json = file_get_contents($cacheFile);
    $data = json_decode($json, true);
} else {
    // === 2. API versuchen ===
    $result = curlMars($marsApi);
include "generateMarsData.php";

    if ($result === null) {
        // API nicht erreichbar → Fake-Daten generieren
        $startSol = 667;

        // Falls bereits Cache existiert → letzten Sol finden und fortsetzen
        if (file_exists($cacheFile)) {
            $existing = json_decode(file_get_contents($cacheFile), true);
            $solKeys = $existing['sol_keys'] ?? [];
            $startSol = !empty($solKeys) ? max($solKeys) + 1 : 667;
        }

        $fakeData = generateFakeMarsData($startSol, 2); // 2 neue Sols
        $data = mergeAndCacheData($cacheFile, $fakeData);
    } else {
        // API erfolgreich → echte Daten mergen
        $apiData = json_decode($result['json'], true);
        $data = mergeAndCacheData($cacheFile, $apiData);
    }
}

// === 3. Sicherstellen, dass Daten korrekt sind ===
if (!isset($data['sol_keys']) || empty($data['sol_keys'])) {
    die("Keine Sol-Daten verfügbar.");
}

$sols = $data['sol_keys'];
$latestSol = end($sols);

// Gewählter Sol (z. B. per ?sol=678)
$selectedSol = isset($_GET['sol']) && in_array($_GET['sol'], $sols)
    ? $_GET['sol']
    : $latestSol;

// === 4. Wetterdaten für Sol laden ===
$solData = $data[$selectedSol] ?? null;
if (!$solData || !isset($solData['AT'], $solData['HWS'])) {
    die("Keine Wetterdaten für Sol $selectedSol verfügbar.");
}

$tempMin = round($solData['AT']['mn']);
$tempMax = round($solData['AT']['mx']);
$wind    = $solData['HWS']['av'];

// === 5. Wetter auslesen (optional über helper) ===
$latestWeather = getMarsWeather($data);
if (isset($latestWeather['error'])) {
    die($latestWeather['error']);
}

$selectedWeather = getMarsWeather($data, $_GET['sol'] ?? null);
if (isset($selectedWeather['error'])) {
    die($selectedWeather['error']);
}

// === 6. Ausgabe an HTML (Frontend) ===
include "marsWeather.html.php";
?>
