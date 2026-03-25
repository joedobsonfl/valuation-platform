<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

$error = '';
$companies = [];

try {
    $pdo = Database::connection();
    $stmt = $pdo->query("
        SELECT id, name, legal_name, industry, headquarters_city, headquarters_state, status, created_at
        FROM companies
        ORDER BY name ASC
    ");
    $companies = $stmt->fetchAll();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .card { max-width: 1100px; padding: 24px; border: 1px solid #ddd; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f5f5f5; }
        .error { color: #b42318; white-space: pre-wrap; }
        a.button {
            display: inline-block;
            padding: 10px 14px;
            border: 1px solid #333;
            border-radius: 8px;
            text-decoration: none;
            color: #000;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Companies</h1>

        <p>
            <a class="button" href="/">Home</a>
            <a class="button" href="/new_company.php">Add Company</a>
        </p>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!$companies): ?>
            <p>No companies yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Legal Name</th>
                        <th>Industry</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td>
                                <a href="/company.php?id=<?= (int)$company['id'] ?>">
                                    <?= htmlspecialchars((string)$company['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars((string)($company['legal_name'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($company['industry'] ?? '')) ?></td>
                            <td>
                                <?= htmlspecialchars(trim(
                                    ((string)($company['headquarters_city'] ?? '')) .
                                    (
                                        !empty($company['headquarters_city']) && !empty($company['headquarters_state'])
                                        ? ', '
                                        : ''
                                    ) .
                                    ((string)($company['headquarters_state'] ?? ''))
                                )) ?>
                            </td>
                            <td><?= htmlspecialchars((string)$company['status']) ?></td>
                            <td><?= htmlspecialchars((string)$company['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>