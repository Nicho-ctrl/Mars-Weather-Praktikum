<!DOCTYPE html>
<html lang="de">
<head>
      <link href="src/output.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Wetter auf dem Mars</title>
</head>
<body>
    <h1>Mars-Wetter</h1>
    <div class="weather-container">
        <div class="box">
            <h2>Min-Temperatur</h2>
            <p><?= isset($tempMin) ? $tempMin.'°C' : 'N/A' ?></p>
        </div>
        <div class="box">
            <h2>Max-Temperatur</h2>
            <p><?= isset($tempMax) ? $tempMax.'°C' : 'N/A' ?></p>
        </div>
        <div class="box" >
            <h1 text="lg">Windgeschwindigkeit</h1>
            <p><?= isset($wind) ? $wind.' m/s' : 'N/A' ?></p>
        </div>
    </div>

    <nav>
        <?php foreach ($sols as $sol): ?>
            <a href="?sol=<?= $sol ?>" class="<?= $sol === $selectedSol ? 'selected' : '' ?>">
                Sol <?= htmlspecialchars($sol) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</body>
</html>
