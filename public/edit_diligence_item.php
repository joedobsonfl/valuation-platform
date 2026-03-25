<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

$pdo = Database::connection();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid diligence item ID.');
}

$stmt = $pdo->prepare("
    SELECT di.*, c.name AS company_name
    FROM diligence_items di
    INNER JOIN companies c ON c.id = di.company_id
    WHERE di.id = :id
");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    exit('Diligence item not found.');
}

$categories = [
    'Financial', 'Operations', 'Management', 'Customers', 'Vendors', 'Legal',
    'Industry / Market', 'Risks', 'Synergies / Value Creation', 'HR / Personnel',
    'Technology / Systems', 'Tax', 'Insurance', 'Real Estate / Facilities',
];

$priorities = ['low', 'medium', 'high'];
$statuses = ['open', 'in_progress', 'closed'];

$error = '';

$category = trim($_POST['category'] ?? (string)$item['category']);
$subcategory = trim($_POST['subcategory'] ?? (string)($item['subcategory'] ?? ''));
$title = trim($_POST['title'] ?? (string)$item['title']);
$description = trim($_POST['description'] ?? (string)($item['description'] ?? ''));
$sourceType = trim($_POST['source_type'] ?? (string)($item['source_type'] ?? ''));
$priority = trim($_POST['priority'] ?? (string)$item['priority']);
$status = trim($_POST['status'] ?? (string)$item['status']);
$owner = trim($_POST['owner'] ?? (string)($item['owner'] ?? ''));
$dueDate = trim($_POST['due_date'] ?? (string)($item['due_date'] ?? ''));

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
            $update = $pdo->prepare("
                UPDATE diligence_items
                SET
                    category = :category,
                    subcategory = :subcategory,
                    title = :title,
                    description = :description,
                    source_type = :source_type,
                    priority = :priority,
                    status = :status,
                    owner = :owner,
                    due_date = :due_date,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $update->execute([
                ':category' => $category,
                ':subcategory' => $subcategory !== '' ? $subcategory : null,
                ':title' => $title,
                ':description' => $description !== '' ? $description : null,
                ':source_type' => $sourceType !== '' ? $sourceType : null,
                ':priority' => $priority,
                ':status' => $status,
                ':owner' => $owner !== '' ? $owner : null,
                ':due_date' => $dueDate !== '' ? $dueDate : null,
                ':id' => $id,
            ]);

            header('Location: /company.php?id=' . (int)$item['company_id']);
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
    <title>Edit Diligence Item</title>
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
        <h1>Edit Diligence Item</h1>
        <p><strong>Company:</strong> <?= htmlspecialchars((string)$item['company_name']) ?></p>

        <p>
            <a class="button" href="/company.php?id=<?= (int)$item['company_id'] ?>">Back to Company</a>
        </p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?= (int)$id ?>">

            <label for="category">Category *</label>
            <select id="category" name="category" required>
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
            <input type="text" id="source_type" name="source_type" value="<?= htmlspecialchars($sourceType) ?>">

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

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>