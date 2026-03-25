<?php
declare(strict_types=1);

require_once '/var/www/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$id = (int)($_POST['id'] ?? 0);
$companyId = (int)($_POST['company_id'] ?? 0);

if ($id <= 0 || $companyId <= 0) {
    http_response_code(400);
    exit('Invalid request.');
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("DELETE FROM diligence_items WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: /company.php?id=' . $companyId);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Delete failed: ' . htmlspecialchars($e->getMessage());
}