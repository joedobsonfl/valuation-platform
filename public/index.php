<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$appName = 'Valuation Platform';
$env = getenv('APP_ENV') ?: 'development';

$dbHost = getenv('DB_HOST') ?: '';
$dbPort = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_DATABASE') ?: '';
$dbUser = getenv('DB_USERNAME') ?: '';
$dbPass = getenv('DB_PASSWORD') ?: '';

$dbStatus = 'Not tested';
$dbError = '';

try {
    if ($dbHost && $dbName && $dbUser) {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $dbHost, $dbPort, $dbName);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->query('SELECT version()');
        $row = $stmt->fetch();
        $dbStatus = 'Connected successfully';
        $dbVersion = $row['version'] ?? 'Unknown';
    } else {
        $dbStatus = 'Missing environment variables';
        $dbVersion = 'N/A';
    }
} catch (Throwable $e) {
    $dbStatus = 'Connection failed';
    $dbError = $e->getMessage();
    $dbVersion = 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.5; }
        .card { max-width: 900px; padding: 24px; border: 1px solid #ddd; border-radius: 10px; }
        .ok { color: #0a7a2f; }
        .bad { color: #b42318; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 4px; }
        pre { background: #f8f8f8; padding: 12px; border-radius: 8px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars($appName) ?></h1>
        <p>Render deployment is live.</p>

        <h2>App</h2>
        <p><strong>Environment:</strong> <?= htmlspecialchars($env) ?></p>

        <h2>Database</h2>
        <p>
            <strong>Status:</strong>
            <span class="<?= $dbStatus === 'Connected successfully' ? 'ok' : 'bad' ?>">
                <?= htmlspecialchars($dbStatus) ?>
            </span>
        </p>
        <p><strong>Host:</strong> <?= htmlspecialchars($dbHost ?: 'not set') ?></p>
        <p><strong>Database:</strong> <?= htmlspecialchars($dbName ?: 'not set') ?></p>
        <p><strong>Version:</strong> <?= htmlspecialchars($dbVersion ?? 'N/A') ?></p>

        <?php if ($dbError): ?>
            <h3>Error</h3>
            <pre><?= htmlspecialchars($dbError) ?></pre>
        <?php endif; ?>

        <h2>Next</h2>
        <ul>
            <li>Create the first core tables</li>
            <li>Add a simple companies list page</li>
            <li>Add document upload storage on the persistent disk</li>
        </ul>
    </div>
</body>
</html>