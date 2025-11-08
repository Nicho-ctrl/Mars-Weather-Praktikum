<?php
// Generiert automatisch neue Mars-Sols, wenn API nicht erreichbar ist

$cacheFile = __DIR__ . '/mars_cache.json';
$data = [];

// Bestehende Datei laden
if (file_exists($cacheFile)) {
    $json = file_get_contents($cacheFile);
    $data = json_decode($json, true) ?? [];
}

// Wenn noch keine Daten existieren → Dummy-Start
if (empty($data['sol_keys'])) {
    $data['sol_keys'] = ["684"];
    $data["684"] = [
        "AT" => ["mn" => -92.0, "mx" => -6.0, "av" => -48.5],
        "HWS" => ["av" => 5.8],
        "PRE" => ["av" => 735.0],
        "Season" => "summer"
    ];
}

$lastSol = end($data['sol_keys']);
$nextSol = (int)$lastSol + 1;

// Zufällige Variation
$newSolData = [
    "AT" => [
        "mn" => round(-100 + mt_rand(0, 30) / 10, 1),
        "mx" => round(-10 + mt_rand(0, 50) / 10, 1),
        "av" => round(-50 + mt_rand(-20, 20) / 10, 1)
    ],
    "HWS" => ["av" => round(5.5 + mt_rand(-10, 10) / 10, 1)],
    "PRE" => ["av" => round(734 + mt_rand(-20, 20) / 10, 1)],
    "Season" => "summer"
];

// Anfügen
$data[$nextSol] = $newSolData;
$data['sol_keys'][] = (string)$nextSol;

// Maximal 100 Tage behalten
if (count($data['sol_keys']) > 100) {
    $oldSol = array_shift($data['sol_keys']);
    unset($data[$oldSol]);
}

// Speichern
file_put_contents(
    $cacheFile,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo "✅ Neuer Mars-Tag Sol $nextSol wurde hinzugefügt.\n";
?>
