<?php
require_once __DIR__ . '/../config.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid administrator credentials.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
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
  <div class="container" style="max-width: 820px;">
    <div class="card card-pad">
      <div class="top-card">
        <div>
          <img src="../images/admin.png" class="h-icon">
          <h1>Administrator Login</h1>
        </div>
      </div>
      
      <p class="muted" style="margin-bottom: 18px; margin-top: 18px;">Enter your username and password to login as administrator.</p>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      <form method="post" class="row">
        <div>
          <label>Username</label>
          <input type="text" name="username" required>
        </div>
        <div>
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
        <div class="actions">
          <button class="btn btn-primary" type="submit">Login <img src="../images/submit.png" class="h-icon-2"></button>
          <a class="btn btn-primary" href="../index.php">Back to Public Page</a>
        </div>
      </form>
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
