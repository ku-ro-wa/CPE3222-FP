<?php
require_once __DIR__ . '/../config.php';
require_admin();

$pdo = pdo();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $id_number = trim($_GET['id_number'] ?? '');
    if ($id_number === '') {
        flash('error', 'Missing user identifier.');
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM visitors WHERE id_number = ? LIMIT 1');
    $stmt->execute([$id_number]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        flash('error', 'User not found.');
        header('Location: dashboard.php');
        exit;
    }
    $id = (int)$row['id'];
}

$stmt = $pdo->prepare('DELETE FROM visitors WHERE id = ?');
$stmt->execute([$id]);
flash('success', 'User and related visits deleted.');
header('Location: dashboard.php');
exit;
