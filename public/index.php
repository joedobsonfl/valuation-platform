<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$appName = 'Valuation Platform';
$env = getenv('APP_ENV') ?: 'development';
$dbHost = getenv('DB_HOST') ?: 'not set';
$dbName = getenv('DB_DATABASE') ?: 'not set';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.5;
        }
        .card {
            max-width: 800px;
            padding: 24px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        h1 { margin-top: 0; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
        }
        ul { padding-left: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars($appName) ?></h1>
        <p>Your Render deployment is working.</p>

        <h2>Status</h2>
        <ul>
            <li><strong>Environment:</strong> <?= htmlspecialchars($env) ?></li>
            <li><strong>DB Host:</strong> <?= htmlspecialchars($dbHost) ?></li>
            <li><strong>DB Name:</strong> <?= htmlspecialchars($dbName) ?></li>
        </ul>

        <h2>Next Steps</h2>
        <ul>
            <li>Connect the Postgres environment variables</li>
            <li>Add a database connection test</li>
            <li>Create the first tables: <code>companies</code>, <code>documents</code>, <code>financial_statements</code>, <code>line_items</code></li>
        </ul>
    </div>
</body>
</html>
