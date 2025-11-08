<?php
/**
 * Führt den cURL-Request zur NASA-API aus und cached das Ergebnis.
 * Gibt bei Fehlern null zurück.
 */
function curlMars(string $apiUrl): ?array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => "PHP Mars Weather Client"
    ]);

    $json = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($json === false || empty($json)) {
        return null;
    }

    // Wenn alles ok, speichern wir es im Cache
    file_put_contents(__DIR__ . '/mars_cache.json', $json);
    return [
        'json' => $json,
        'info' => $info,
        'error' => $error
    ];
}

/**
 * Generiert simulierte Wetterdaten für einen Bereich von Sols.
 * Die Werte variieren leicht, damit es realistisch aussieht.
 */
function generateFakeMarsData(int $startSol, int $days = 17): array {
    $data = [];
    $solKeys = [];

    $baseMin = -80;  // durchschnittliche Min-Temperatur
    $baseMax = -10;  // durchschnittliche Max-Temperatur
    $baseWind = 5;   // durchschnittliche Windgeschwindigkeit

    for ($i = 0; $i < $days; $i++) {
        $sol = $startSol + $i;
        $tempMin = $baseMin + rand(-5, 5);
        $tempMax = $baseMax + rand(-5, 5);
        $wind    = $baseWind + rand(-2, 4);

        $data[$sol] = [
            'AT' => [
                'mn' => $tempMin,
                'mx' => $tempMax,
                'av' => ($tempMin + $tempMax) / 2
            ],
            'HWS' => [
                'av' => $wind
            ],
            'Season' => 'winter'
        ];

        $solKeys[] = $sol;
    }

    $data['sol_keys'] = $solKeys;
    return $data;
}

/**
 * Fügt neue Tage zu bestehenden Cache-Daten hinzu.
 * Wenn keine Cache-Datei vorhanden ist, wird eine neue erstellt.
 */
function mergeAndCacheData(string $cacheFile, array $newData): array {
    $existingData = [];

    if (file_exists($cacheFile)) {
        $existingJson = file_get_contents($cacheFile);
        $existingData = json_decode($existingJson, true) ?? [];
    }

    // Mergen ohne doppelte Sols
    foreach ($newData['sol_keys'] as $sol) {
        $existingData[$sol] = $newData[$sol];
    }

    // Sol Keys aktualisieren
    $allSols = array_unique(array_merge($existingData['sol_keys'] ?? [], $newData['sol_keys']));
    sort($allSols);
    $existingData['sol_keys'] = $allSols;

    // Neuen Cache schreiben
    file_put_contents($cacheFile, json_encode($existingData, JSON_PRETTY_PRINT));
    return $existingData;
}

/**
 * Liefert Wetterdaten für einen bestimmten oder letzten Sol.
 */
function getMarsWeather(array $data, ?string $sol = null): array {
    $sols = $data['sol_keys'] ?? [];
    if (empty($sols)) {
        return ['error' => 'Keine Sol-Daten verfügbar.'];
    }

    $sol = $sol ?? end($sols);

    if (!isset($data[$sol])) {
        return ['error' => "Keine Daten für Sol $sol gefunden."];
    }

    return $data[$sol];
}
?>
