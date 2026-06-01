<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$user_type   = trim($_GET['user_type']   ?? '');
$id_number   = trim($_GET['id_number']   ?? '');
$first_name  = trim($_GET['first_name']  ?? '');
$middle_name = trim($_GET['middle_name'] ?? '');
$last_name   = trim($_GET['last_name']   ?? '');

try {
    if ($user_type === 'Guest') {
        if ($first_name === '' || $last_name === '') {
            echo json_encode(['ok' => false, 'message' => 'First and last name are required for guests.', 'user' => null]);
            exit;
        }

        $stmt = pdo()->prepare(
            'SELECT * FROM visitors
              WHERE first_name  = ?
                AND last_name   = ?
                AND (
                    (? = "" AND (middle_name IS NULL OR middle_name = ""))
                    OR middle_name = ?
                )
                AND (id_number IS NULL OR id_number = "")
              LIMIT 1'
        );
        $stmt->execute([$first_name, $last_name, $middle_name, $middle_name]);
    } else {
        if ($id_number === '') {
            echo json_encode(['ok' => false, 'message' => 'Missing ID number', 'user' => null]);
            exit;
        }

        $stmt = pdo()->prepare('SELECT * FROM visitors WHERE id_number = ? LIMIT 1');
        $stmt->execute([$id_number]);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'   => (bool)$user,
        'user' => $user ?: null,
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'ok'      => false,
        'message' => 'Unable to look up user',
        'user'    => null,
    ]);
}
