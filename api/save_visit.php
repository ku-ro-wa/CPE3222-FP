<?php
require_once __DIR__ . '/../config.php';

function redirect_with(string $type, string $message): void {
    flash($type, $message);
    header('Location: ../index.php');
    exit;
}

$action       = $_POST['action']        ?? '';
$id_number    = trim($_POST['id_number']    ?? '');
$first_name   = trim($_POST['first_name']   ?? '');
$middle_name  = trim($_POST['middle_name']  ?? '');
$last_name    = trim($_POST['last_name']    ?? '');
$barangay     = trim($_POST['barangay']     ?? '');
$city         = trim($_POST['city']         ?? '');
$province     = trim($_POST['province']     ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$email        = trim($_POST['email']        ?? '');
$usertype     = trim($_POST['user_type']    ?? '');

$is_guest = ($usertype === 'Guest');

// USC users must supply an ID; all users must supply full name including middle name
if (!$is_guest && $id_number === '') {
    redirect_with('error', 'ID number is required for Student, Faculty, or Staff.');
}
if ($first_name === '' || $middle_name === '' || $last_name === '') {
    redirect_with('error', 'First, middle, and last name are all required.');
}

$pdo = pdo();

function record_visit(PDO $pdo, int $user_id, string $action): void {
    if ($action === 'in') {
        $stmt = $pdo->prepare(
            'INSERT INTO check_ins (visitor_id, check_in_time) VALUES (?, NOW())'
        );
        $stmt->execute([$user_id]);
    } elseif ($action === 'out') {
        $stmt = $pdo->prepare(
            'UPDATE check_ins SET check_out_time = NOW()
              WHERE visitor_id = ? AND check_out_time IS NULL
              ORDER BY check_in_time DESC LIMIT 1'
        );
        $stmt->execute([$user_id]);
    }
}

/**
 * Find a user row.
 * USC users  → look up by id_number
 * Guest users → look up by first_name + middle_name + last_name (no id_number)
 */
function find_user(PDO $pdo, bool $is_guest, string $id_number, string $first_name, string $middle_name, string $last_name): array|false {
    if ($is_guest) {
        $stmt = $pdo->prepare(
            "SELECT id FROM visitors
              WHERE first_name  = ?
                AND middle_name = ?
                AND last_name   = ?
                AND (id_number IS NULL OR id_number = '')
              LIMIT 1"
        );
        $stmt->execute([$first_name, $middle_name, $last_name]);
    } else {
        $stmt = $pdo->prepare('SELECT id FROM visitors WHERE id_number = ? LIMIT 1');
        $stmt->execute([$id_number]);
    }
    return $stmt->fetch();
}

try {
    // ── REGISTER & SIGN IN ────────────────────────────────────────────────────
    if ($action === 'register') {
        foreach ([$barangay, $city, $province, $phone_number, $email] as $value) {
            if ($value === '') {
                redirect_with('error', 'Please complete all required fields.');
            }
        }

        $pdo->beginTransaction();

        $existing = find_user($pdo, $is_guest, $id_number, $first_name, $middle_name, $last_name);

        if ($existing) {
            // Existing record: update contact details and sign in
            $user_id = (int)$existing['id'];
            $stmt = $pdo->prepare(
                'UPDATE visitors
                    SET barangay=?, city=?, province=?, phone_number=?, email=?
                  WHERE id=?'
            );
            $stmt->execute([$barangay, $city, $province, $phone_number, $email, $user_id]);
        } else {
            // New user: insert full record
            $stmt = $pdo->prepare(
                'INSERT INTO visitors
                    (id_number, first_name, middle_name, last_name, barangay, city, province, phone_number, email)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $is_guest ? null : $id_number,
                $first_name,
                $middle_name,
                $last_name,
                $barangay,
                $city,
                $province,
                $phone_number,
                $email,
            ]);
            $user_id = (int)$pdo->lastInsertId();
        }

        record_visit($pdo, $user_id, 'in');
        $pdo->commit();
        redirect_with('success', 'Registration completed and entry time recorded.');
    }

    // ── SIGN IN / SIGN OUT ────────────────────────────────────────────────────
    $user = find_user($pdo, $is_guest, $id_number, $first_name, $middle_name, $last_name);

    if (!$user) {
        redirect_with('error', 'No matching record found. Please register first.');
    }

    $user_id = (int)$user['id'];

    if ($action === 'signin') {
        $stmt = $pdo->prepare(
            'SELECT id FROM check_ins WHERE visitor_id = ? AND check_out_time IS NULL LIMIT 1'
        );
        $stmt->execute([$user_id]);

        if ($stmt->fetch()) {
            redirect_with('error', 'This visitor is already signed in. Please sign out first.');
        }

        record_visit($pdo, $user_id, 'in');
        redirect_with('success', 'Sign-in successful. Entry time recorded.');
    }

    if ($action === 'signout') {
        $stmt = $pdo->prepare(
            'SELECT id FROM check_ins WHERE visitor_id = ? AND check_out_time IS NULL LIMIT 1'
        );
        $stmt->execute([$user_id]);

        if (!$stmt->fetch()) {
            redirect_with('error', 'No active sign-in found. Please sign in first.');
        }

        record_visit($pdo, $user_id, 'out');
        redirect_with('success', 'Sign-out successful. Exit time recorded.');
    }

    redirect_with('error', 'Invalid action.');

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with('error', 'Unable to process request. Please try again.');
}