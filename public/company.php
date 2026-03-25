<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid company ID.');
}

$pdo = Database::connection();

$stmt = $pdo->prepare("
    SELECT id, name, legal_name, industry, headquarters_city, headquarters_state,
           headquarters_country, status, created_at
    FROM companies
    WHERE id = :id
");
$stmt->execute([':id' => $id]);
$company = $stmt->fetch();

if (!$company) {
    http_response_code(404);
    exit('Company not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$company['name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .card { max-width: 900px; padding: 24px; border: 1px solid #ddd; border-radius: 10px; }
        .actions a, .actions form button {
            display: inline-block;
            padding: 10px 14px;
            border: 1px solid #333;
            border-radius: 8px;
            text-decoration: none;
            color: #000;
            background: white;
            cursor: pointer;
            margin-right: 10px;
        }
        .actions form { display: inline; }
        .row { margin-bottom: 12px; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars((string)$company['name']) ?></h1>

        <div class="row"><span class="label">Legal Name:</span> <?= htmlspecialchars((string)($company['legal_name'] ?? '')) ?></div>
        <div class="row"><span class="label">Industry:</span> <?= htmlspecialchars((string)($company['industry'] ?? '')) ?></div>
        <div class="row"><span class="label">City:</span> <?= htmlspecialchars((string)($company['headquarters_city'] ?? '')) ?></div>
        <div class="row"><span class="label">State:</span> <?= htmlspecialchars((string)($company['headquarters_state'] ?? '')) ?></div>
        <div class="row"><span class="label">Country:</span> <?= htmlspecialchars((string)($company['headquarters_country'] ?? '')) ?></div>
        <div class="row"><span class="label">Status:</span> <?= htmlspecialchars((string)$company['status']) ?></div>
        <div class="row"><span class="label">Created:</span> <?= htmlspecialchars((string)$company['created_at']) ?></div>

        <div class="actions" style="margin-top: 24px;">
            <a href="/companies.php">Back to Companies</a>

            <form method="post" action="/delete_company.php" onsubmit="return confirm('Delete this company? This cannot be undone.');">
                <input type="hidden" name="id" value="<?= (int)$company['id'] ?>">
                <button type="submit">Delete Company</button>
            </form>
        </div>
    </div>
</body>
</html>