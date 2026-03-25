<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid company ID.');
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: /companies.php');
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Delete failed: ' . htmlspecialchars($e->getMessage());
}