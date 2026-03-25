<?php

declare(strict_types=1);

require_once '/var/www/src/Database.php';

$error = '';
$success = '';

$name = trim($_POST['name'] ?? '');
$legalName = trim($_POST['legal_name'] ?? '');
$industry = trim($_POST['industry'] ?? '');
$city = trim($_POST['headquarters_city'] ?? '');
$state = trim($_POST['headquarters_state'] ?? '');
$country = trim($_POST['headquarters_country'] ?? 'USA');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $error = 'Company name is required.';
    } else {
        try {
            $pdo = Database::connection();

            $check = $pdo->prepare("
                SELECT id
                FROM companies
                WHERE LOWER(name) = LOWER(:name)
                LIMIT 1
            ");
            $check->execute([':name' => $name]);
            $existing = $check->fetch();

            if ($existing) {
                $error = 'A company with that name already exists.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO companies
                    (name, legal_name, industry, headquarters_city, headquarters_state, headquarters_country, status)
                    VALUES
                    (:name, :legal_name, :industry, :city, :state, :country, 'active')
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':legal_name' => $legalName !== '' ? $legalName : null,
                    ':industry' => $industry !== '' ? $industry : null,
                    ':city' => $city !== '' ? $city : null,
                    ':state' => $state !== '' ? $state : null,
                    ':country' => $country !== '' ? $country : 'USA',
                ]);

                header('Location: /companies.php');
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
    <title>New Company</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .card { max-width: 800px; padding: 24px; border: 1px solid #ddd; border-radius: 10px; }
        label { display: block; margin-top: 14px; font-weight: bold; }
        input {
            width: 100%;
            max-width: 600px;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }
        .error { color: #b42318; white-space: pre-wrap; margin-bottom: 12px; }
        button, a.button {
            display: inline-block;
            margin-top: 18px;
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
        <h1>Add Company</h1>

        <p><a class="button" href="/companies.php">Back to Companies</a></p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
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

            <button type="submit">Save Company</button>
        </form>
    </div>
</body>
</html>
