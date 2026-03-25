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

$diligenceStmt = $pdo->prepare("
    SELECT id, category, subcategory, title, description, source_type, priority, status, owner, due_date, created_at
    FROM diligence_items
    WHERE company_id = :company_id
    ORDER BY category ASC, priority DESC, created_at DESC
");
$diligenceStmt->execute([':company_id' => $id]);
$diligenceItems = $diligenceStmt->fetchAll();

$groupedDiligence = [];
foreach ($diligenceItems as $item) {
    $groupedDiligence[$item['category']][] = $item;
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
            <a href="/edit_company.php?id=<?= (int)$company['id'] ?>">Edit Company</a>

            <form method="post" action="/delete_company.php" onsubmit="return confirm('Delete this company? This cannot be undone.');">
                <input type="hidden" name="id" value="<?= (int)$company['id'] ?>">
                <button type="submit">Delete Company</button>
            </form>
        </div>
    </div>
    <div class="section">
        <h2>Diligence Items</h2>

        <p>
            <a class="button" href="/add_diligence_item.php?company_id=<?= (int)$company['id'] ?>">Add Diligence Item</a>
        </p>

        <?php if (!$diligenceItems): ?>
            <p>No diligence items yet.</p>
        <?php else: ?>
            <?php foreach ($groupedDiligence as $category => $items): ?>
                <h3><?= htmlspecialchars((string)$category) ?></h3>
                <table style="width:100%; border-collapse: collapse; margin-bottom: 24px;">
                    <thead>
                        <tr>
                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Title</th>
                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Priority</th>
                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Status</th>
                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Owner</th>
                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Due Date</th>
                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td style="border-bottom:1px solid #eee; padding:8px;">
                                    <strong><?= htmlspecialchars((string)$item['title']) ?></strong>
                                    <?php if (!empty($item['description'])): ?>
                                        <div style="margin-top:4px; color:#444;">
                                            <?= nl2br(htmlspecialchars((string)$item['description'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= htmlspecialchars((string)$item['priority']) ?></td>
                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= htmlspecialchars((string)$item['status']) ?></td>
                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= htmlspecialchars((string)($item['owner'] ?? '')) ?></td>
                                <td style="border-bottom:1px solid #eee; padding:8px;"><?= htmlspecialchars((string)($item['due_date'] ?? '')) ?></td>
                                <td style="border-bottom:1px solid #eee; padding:8px; white-space: nowrap;">
                                    <a href="/edit_diligence_item.php?id=<?= (int)$item['id'] ?>">Edit</a>
                                    &nbsp;|&nbsp;
                                    <form method="post" action="/delete_diligence_item.php" style="display:inline;" onsubmit="return confirm('Delete this diligence item?');">
                                        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                        <input type="hidden" name="company_id" value="<?= (int)$company['id'] ?>">
                                        <button type="submit" style="border:none; background:none; color:#00f; padding:0; cursor:pointer;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>