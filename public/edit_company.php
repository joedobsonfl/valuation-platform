<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

$pdo = Database::connection();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid company ID.');
}

$error = '';

$stmt = $pdo->prepare("
    SELECT id, name, legal_name, industry, headquarters_city, headquarters_state,
           headquarters_country, status
    FROM companies
    WHERE id = :id
");
$stmt->execute([':id' => $id]);
$company = $stmt->fetch();

if (!$company) {
    http_response_code(404);
    exit('Company not found.');
}

$name = trim($_POST['name'] ?? (string)$company['name']);
$legalName = trim($_POST['legal_name'] ?? (string)($company['legal_name'] ?? ''));
$industry = trim($_POST['industry'] ?? (string)($company['industry'] ?? ''));
$city = trim($_POST['headquarters_city'] ?? (string)($company['headquarters_city'] ?? ''));
$state = trim($_POST['headquarters_state'] ?? (string)($company['headquarters_state'] ?? ''));
$country = trim($_POST['headquarters_country'] ?? (string)($company['headquarters_country'] ?? 'USA'));
$status = trim($_POST['status'] ?? (string)$company['status']);

$allowedStatuses = ['active', 'inactive'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $error = 'Company name is required.';
    } elseif (!in_array($status, $allowedStatuses, true)) {
        $error = 'Invalid status.';
    } else {
        try {
            $check = $pdo->prepare("
                SELECT id
                FROM companies
                WHERE LOWER(name) = LOWER(:name)
                  AND id <> :id
                LIMIT 1
            ");
            $check->execute([
                ':name' => $name,
                ':id' => $id,
            ]);
            $existing = $check->fetch();

            if ($existing) {
                $error = 'Another company with that name already exists.';
            } else {
                $update = $pdo->prepare("
                    UPDATE companies
                    SET
                        name = :name,
                        legal_name = :legal_name,
                        industry = :industry,
                        headquarters_city = :city,
                        headquarters_state = :state,
                        headquarters_country = :country,
                        status = :status,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $update->execute([
                    ':name' => $name,
                    ':legal_name' => $legalName !== '' ? $legalName : null,
                    ':industry' => $industry !== '' ? $industry : null,
                    ':city' => $city !== '' ? $city : null,
                    ':state' => $state !== '' ? $state : null,
                    ':country' => $country !== '' ? $country : 'USA',
                    ':status' => $status,
                    ':id' => $id,
                ]);

                header('Location: /company.php?id=' . $id);
                exit;
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .card { max-width: 850px; padding: 24px; border: 1px solid #ddd; border-radius: 10px; }
        label { display: block; margin-top: 14px; font-weight: bold; }
        input, select {
            width: 100%;
            max-width: 650px;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }
        .error {
            color: #b42318;
            white-space: pre-wrap;
            margin-bottom: 12px;
        }
        button, a.button {
            display: inline-block;
            margin-top: 18px;
            margin-right: 10px;
            padding: 10px 14px;
            border: 1px solid #333;
            border-radius: 8px;
            text-decoration: none;
            color: #000;
            background: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Edit Company</h1>

        <p>
            <a class="button" href="/company.php?id=<?= (int)$id ?>">Back to Company</a>
            <a class="button" href="/companies.php">Back to Companies</a>
        </p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?= (int)$id ?>">

            <label for="name">Company Name *</label>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($name) ?>">

            <label for="legal_name">Legal Name</label>
            <input type="text" id="legal_name" name="legal_name" value="<?= htmlspecialchars($legalName) ?>">

            <label for="industry">Industry</label>
            <input type="text" id="industry" name="industry" value="<?= htmlspecialchars($industry) ?>">

            <label for="headquarters_city">Headquarters City</label>
            <input type="text" id="headquarters_city" name="headquarters_city" value="<?= htmlspecialchars($city) ?>">

            <label for="headquarters_state">Headquarters State</label>
            <input type="text" id="headquarters_state" name="headquarters_state" value="<?= htmlspecialchars($state) ?>">

            <label for="headquarters_country">Headquarters Country</label>
            <input type="text" id="headquarters_country" name="headquarters_country" value="<?= htmlspecialchars($country) ?>">

            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>