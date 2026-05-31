<?php
require_once __DIR__ . '/../config.php';
require_admin();

$pdo = pdo();

$q          = trim($_GET['q']          ?? '');
$search_by  = trim($_GET['search_by']  ?? '');
$date_from  = trim($_GET['date_from']  ?? '');
$date_to    = trim($_GET['date_to']    ?? '');

$where  = [];
$params = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $field_map = [
        'id_number'  => 'v.id_number',
        'first_name' => 'v.first_name',
        'last_name'  => 'v.last_name',
        'barangay'   => 'v.barangay',
        'city'       => 'v.city',
        'province'   => 'v.province',
    ];
    if (isset($field_map[$search_by])) {
        $where[]  = $field_map[$search_by] . ' LIKE ?';
        $params[] = $like;
    } else {
        $where[]  = '(v.id_number LIKE ? OR v.first_name LIKE ? OR v.middle_name LIKE ? OR v.last_name LIKE ? OR CONCAT(v.first_name, " ", v.last_name) LIKE ? OR v.barangay LIKE ? OR v.city LIKE ? OR v.province LIKE ?)';
        $params   = [...$params, ...array_fill(0, 8, $like)];
    }
}
if ($date_from !== '') {
    $where[]  = 'DATE(c.check_in_time) >= ?';
    $params[] = $date_from;
}
if ($date_to !== '') {
    $where[]  = 'DATE(c.check_in_time) <= ?';
    $params[] = $date_to;
}

$sql = 'SELECT c.id AS check_in_id, c.check_in_time, c.check_out_time,
               v.id AS visitor_id, v.id_number, v.first_name, v.middle_name,
               v.last_name, v.barangay, v.city, v.province, v.phone_number, v.email
        FROM check_ins c
        INNER JOIN visitors v ON v.id = c.visitor_id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY c.check_in_time DESC, c.id DESC LIMIT 500';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$visits = $stmt->fetchAll();

$userStmt = $pdo->query('SELECT id, id_number, first_name, middle_name, last_name, barangay, city, province, phone_number, email FROM visitors ORDER BY last_name ASC, first_name ASC');
$users = $userStmt->fetchAll();

$totalUsers  = (int)$pdo->query('SELECT COUNT(*) FROM visitors')->fetchColumn();
$totalVisits = (int)$pdo->query('SELECT COUNT(*) FROM check_ins')->fetchColumn();
$activeIn    = (int)$pdo->query('SELECT COUNT(*) FROM check_ins WHERE check_out_time IS NULL')->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
  <link href='https://fonts.googleapis.com/css?family=Bricolage Grotesque' rel='stylesheet'>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <nav class="header">
    <div class="header-left">
      <img src="../images/dcpe2.png" style="width: 70px; height: 70px;">
      <div class="brand">
        <h1>Contact Tracing Application</h1>
        <p>USC Department of Computer Engineering</p>
      </div>
    </div>
    <div class="header-right">
      <div class="actions">
        <a class="btn btn-primary" href="../index.php">Manage User Session</a>
        <a class="btn btn-primary" href="login.php">Administrator Login</a>
      </div>
    <div>
  </nav>

  <div class="main">
    <div class="container">
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-error"><?= e($msg) ?></div>
      <?php endif; ?>
      <div class="topbar">
        <div class="caption">
          <h1>Administrator Dashboard</h1>
          <p class="text">Search, review, and delete users and visit logs.</p>
        </div>
        <div class="actions">
          <a class="btn btn-danger" href="logout.php">Logout <img src="../images/logout.png" class="h-icon-2"></a>
        </div>
      </div>

      <div class="card card-pad" style="margin-top:18px;">
        <div class="top-card">
          <div>
            <img src="../images/search.png" class="h-icon">
            <h2>Search Visit Logs</h2>
          </div>
        </div>
        <form method="get" class="grid grid-2" style="margin-top:14px;">
            <div>
              <label>Enter term to search</label>
              <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search term">
            </div>
            <div>
              <label>Search By</label>
              <select name="search_by">
                <option value="">All Fields</option>
                <option value="id_number"  <?= $search_by === 'id_number'  ? 'selected' : '' ?>>ID Number</option>
                <option value="first_name" <?= $search_by === 'first_name' ? 'selected' : '' ?>>First Name</option>
                <option value="last_name"  <?= $search_by === 'last_name'  ? 'selected' : '' ?>>Last Name</option>
                <option value="barangay"   <?= $search_by === 'barangay'   ? 'selected' : '' ?>>Barangay</option>
                <option value="city"       <?= $search_by === 'city'       ? 'selected' : '' ?>>City</option>
                <option value="province"   <?= $search_by === 'province'   ? 'selected' : '' ?>>Province</option>
              </select>
            </div>

            <div>
              <label>From Date</label>
              <input type="date" name="date_from" value="<?= e($date_from) ?>">
            </div>
            <div>
              <label>To Date</label>
              <input type="date" name="date_to" value="<?= e($date_to) ?>">
            </div>

            <div class="actions" style="align-self:end;">
              <button class="btn btn-primary" type="submit">Search<img src="../images/search.png" class="h-icon-2"></button>
              <a class="btn btn-primary" href="dashboard.php">Reset</a>
            </div>

            <span class="total">Total Users: <?= $totalUsers ?></span>

        </form>
        <div class="table-wrap" style="margin-top:16px;">
          <table>
            <thead>
              <tr>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Status</th>
                <th>ID Number</th>
                <th>Name</th>
                <th>Account Type</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Manage</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$visits): ?>
                <tr><td colspan="10">No records found.</td></tr>
              <?php endif; ?>
              <?php foreach ($visits as $row): ?>
                <?php $is_in = $row['check_out_time'] === null; ?>
                <?php $account_type = empty($row['id_number']) ? 'Guest' : 'USC'; ?>
                <tr>
                  <td><?= e(date('M d, Y h:i A', strtotime($row['check_in_time']))) ?></td>
                  <td><?= $row['check_out_time'] ? e(date('M d, Y h:i A', strtotime($row['check_out_time']))) : '—' ?></td>
                  <td><span class="badge <?= $is_in ? 'badge-in' : 'badge-out' ?>"><?= $is_in ? 'IN' : 'OUT' ?></span></td>
                  <td><?= e($row['id_number']) ?></td>
                  <td><?= e(trim($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'])) ?></td>
                  <td><?= e($account_type) ?></td>
                  <td><?= e($row['barangay']) ?>, <?= e($row['city']) ?>, <?= e($row['province']) ?></td>
                  <td><?= e($row['phone_number']) ?></td>
                  <td><?= e($row['email']) ?></td>
                  <td class="actions">
                    <a class="btn btn-danger" href="user_delete.php?id=<?= (int)$row['visitor_id'] ?>" onclick="return confirm('Delete this user and all related visits?')">Delete<img src="../images/delete.png" class="h-icon-2"></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card card-pad" style="margin-top:18px;">
        <div class="top-card">
          <div>
            <img src="../images/user-list.png" class="h-icon">
            <h2>User Directory</h2>
          </div>
        </div>
        <div class="table-wrap" style="margin-top:16px;">
          <table>
            <thead>
              <tr>
                <th>ID Number</th>
                <th>Name</th>
                <th>Account Type</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Manage</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <?php $account_type = empty($u['id_number']) ? 'Guest' : 'USC'; ?>
                <tr>
                  <td><?= e($u['id_number']) ?></td>
                  <td><?= e(trim($u['last_name'] . ', ' . $u['first_name'] . ' ' . $u['middle_name'])) ?></td>
                  <td><?= e($account_type) ?></td>
                  <td><?= e($u['barangay']) ?>, <?= e($u['city']) ?>, <?= e($u['province']) ?></td>
                  <td><?= e($u['phone_number']) ?></td>
                  <td><?= e($u['email']) ?></td>
                  <td class="actions">
                    <a class="btn btn-danger" href="user_delete.php?id=<?= (int)$u['id'] ?>" onclick="return confirm('Delete this user and all related visits?')">Delete<img src="../images/delete.png" class="h-icon-2"></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <footer>
    <div class="footer-logos">
        <img src="../images/usc.png" style="width: 35px; height: 35px;">
        <img src="../images/dcpe2.png" style="width: 35px; height: 35px;">
    </div>
    <p>© 2026 University of San Carlos Department of Computer Engineering</p>
</footer>
</body>
</html>
