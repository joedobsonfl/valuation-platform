<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

$pdo = Database::connection();

$companyId = (int)($_GET['company_id'] ?? $_POST['company_id'] ?? 0);

if ($companyId <= 0) {
    http_response_code(400);
    exit('Invalid company ID.');
}

$stmt = $pdo->prepare("SELECT id, name FROM companies WHERE id = :id");
$stmt->execute([':id' => $companyId]);
$company = $stmt->fetch();

if (!$company) {
    http_response_code(404);
    exit('Company not found.');
}

$categories = [
    'Financial',
    'Operations',
    'Management',
    'Customers',
    'Vendors',
    'Legal',
    'Industry / Market',
    'Risks',
    'Synergies / Value Creation',
    'HR / Personnel',
    'Technology / Systems',
    'Tax',
    'Insurance',
    'Real Estate / Facilities',
];

$priorities = ['low', 'medium', 'high'];
$statuses = ['open', 'in_progress', 'closed'];

$error = '';

$category = trim($_POST['category'] ?? '');
$subcategory = trim($_POST['subcategory'] ?? '');
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sourceType = trim($_POST['source_type'] ?? '');
$priority = trim($_POST['priority'] ?? 'medium');
$status = trim($_POST['status'] ?? 'open');
$owner = trim($_POST['owner'] ?? '');
$dueDate = trim($_POST['due_date'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($title === '') {
        $error = 'Title is required.';
    } elseif (!in_array($category, $categories, true)) {
        $error = 'Please select a valid category.';
    } elseif (!in_array($priority, $priorities, true)) {
        $error = 'Invalid priority.';
    } elseif (!in_array($status, $statuses, true)) {
        $error = 'Invalid status.';
    } else {
        try {
            $insert = $pdo->prepare("
                INSERT INTO diligence_items
                (company_id, category, subcategory, title, description, source_type, priority, status, owner, due_date)
                VALUES
                (:company_id, :category, :subcategory, :title, :description, :source_type, :priority, :status, :owner, :due_date)
            ");
            $insert->execute([
                ':company_id' => $companyId,
                ':category' => $category,
                ':subcategory' => $subcategory !== '' ? $subcategory : null,
                ':title' => $title,
                ':description' => $description !== '' ? $description : null,
                ':source_type' => $sourceType !== '' ? $sourceType : null,
                ':priority' => $priority,
                ':status' => $status,
                ':owner' => $owner !== '' ? $owner : null,
                ':due_date' => $dueDate !== '' ? $dueDate : null,
            ]);

            header('Location: /company.php?id=' . $companyId);
            exit;
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
    <title>Add Diligence Item</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .card { max-width: 900px; padding: 24px; border: 1px solid #ddd; border-radius: 10px; }
        label { display: block; margin-top: 14px; font-weight: bold; }
        input, select, textarea {
            width: 100%;
            max-width: 700px;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }
        textarea { min-height: 140px; }
        .error { color: #b42318; margin-bottom: 12px; white-space: pre-wrap; }
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
        <h1>Add Diligence Item</h1>
        <p><strong>Company:</strong> <?= htmlspecialchars((string)$company['name']) ?></p>

        <p>
            <a class="button" href="/company.php?id=<?= (int)$companyId ?>">Back to Company</a>
        </p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="company_id" value="<?= (int)$companyId ?>">

            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="subcategory">Subcategory</label>
            <input type="text" id="subcategory" name="subcategory" value="<?= htmlspecialchars($subcategory) ?>">

            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required value="<?= htmlspecialchars($title) ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($description) ?></textarea>

            <label for="source_type">Source Type</label>
            <input type="text" id="source_type" name="source_type" value="<?= htmlspecialchars($sourceType) ?>" placeholder="e.g. Financial Statement, Customer Interview, Lease, Tax Return">

            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <?php foreach ($priorities as $p): ?>
                    <option value="<?= $p ?>" <?= $priority === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="status">Status</label>
            <select id="status" name="status">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="owner">Owner</label>
            <input type="text" id="owner" name="owner" value="<?= htmlspecialchars($owner) ?>">

            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($dueDate) ?>">

            <button type="submit">Save Diligence Item</button>
        </form>
    </div>
</body>
</html>