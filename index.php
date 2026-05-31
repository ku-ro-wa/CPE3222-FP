<?php
require_once __DIR__ . '/config.php';
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Department of Computer Engineering Contact Tracing</title>
  <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
  <link href='https://fonts.googleapis.com/css?family=Bricolage+Grotesque' rel='stylesheet'>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <nav class="header">
    <div class="header-left">
      <img src="images/dcpe2.png" style="width: 70px; height: 70px;">
      <div class="brand">
        <h1>Contact Tracing Application</h1>
        <p>USC Department of Computer Engineering</p>
      </div>
    </div>
    <div class="header-right">
      <div class="actions">
        <a class="btn btn-primary" href="index.php">Manage User Session</a>
        <a class="btn btn-primary" href="admin/login.php">Administrator Login</a>
      </div>
    <div>
  </nav>

  <div class="main">
    
    <div class="container">
      <div class="caption" style="margin-bottom: 50px;">
        <h1>Welcome to the USC Department of Computer Engineering!</h1>
        <p class="muted">Enter your information below to sign-in and sign-out.</p>
      </div>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <div class="grid">

        <!-- ============================================
             USC Sign-In (Student / Faculty / Staff)
             All element IDs are prefixed with "usc_"
        ============================================= -->
        <div class="card card-pad">
          <div class="top-card">
            <div>
              <img src="images/signin.png" class="h-icon">
              <h2>USC User Session</h2>
            </div>
          </div>
          <p class="muted" style="margin-top: 18px;">Enter your USC ID number to sign in or out. For Students, Faculty, and Staff.</p>

          <div class="row" style="margin-top:16px;">
            <div>
              <label for="usc_id_lookup">ID Number</label>
              <div class="actions">
                <input id="usc_id_lookup" type="text" placeholder="e.g. 23101234">
                <button type="button" class="btn btn-primary" onclick="lookupUser()">Submit</button>
              </div>
            </div>
            <p class="muted">
              New User? <a href="#" id="registerLink">Click Here to Register</a>
            </p>
            <div id="usc_statusBox"></div>
          </div>

          <form id="uscTraceForm" class="row" action="api/save_visit.php" method="post" style="margin-top:18px;">
            <input type="hidden" id="usc_action" name="action" value="register">

            <div id="usc_formFields" class="hide">
              <div class="grid grid-3">
                <div>
                  <label for="usc_first_name">First Name *</label>
                  <input id="usc_first_name" name="first_name" type="text"
                         placeholder="First Name" required>
                </div>
                <div>
                  <label for="usc_middle_name">Middle Name *</label>
                  <input id="usc_middle_name" name="middle_name" type="text"
                         placeholder="Middle Name">
                </div>
                <div>
                  <label for="usc_last_name">Last Name *</label>
                  <input id="usc_last_name" name="last_name" type="text"
                         placeholder="Last Name" required>
                </div>
              </div>
              <div class="grid grid-2">

                <div>
                  <label for="usc_id_display">ID Number *</label>
                  <input id="usc_id_display" name="id_number" type="text"
                         placeholder="e.g. 23101234" required>
                </div>

                <div>
                  <label for="usc_user_type">User *</label>
                  <select id="usc_user_type" name="user_type" required>
                    <option value="" disabled selected>Select User</option>
                    <option>Student</option>
                    <option>Faculty</option>
                    <option>Staff</option>
                  </select>
                </div>

                

                <div>
                  <label for="usc_contact_number">Contact Number *</label>
                  <input id="usc_contact_number" name="phone_number" type="tel"
                         placeholder="09XXXXXXXXX" required>
                </div>

                <div>
                  <label for="usc_email">Email *</label>
                  <input id="usc_email" name="email" type="email"
                         placeholder="name@example.com" required>
                </div>

              </div>

              <div class="grid grid-3" style="margin-top:14px;">
                <div>
                  <label for="usc_barangay">Barangay *</label>
                  <input id="usc_barangay" name="barangay" type="text"
                         placeholder="Barangay" required>
                </div>
                <div>
                  <label for="usc_city">City / Town *</label>
                  <input id="usc_city" name="city" type="text"
                         placeholder="City or Town" required>
                </div>
                <div>
                  <label for="usc_province">Province *</label>
                  <input id="usc_province" name="province" type="text"
                         placeholder="Province" required>
                </div>
              </div>
            </div><!-- /usc_formFields -->

            <div id="usc_actionBox" class="actions hide">
              <button type="submit" class="btn btn-primary"
                      id="usc_registerBtn"
                      onclick="return uscPrepareAction('register')">Register &amp; Sign In <img src="images/submit.png" class="h-icon-2"></button>
              <button type="submit" class="btn btn-primary hide"
                      id="usc_signInBtn"
                      onclick="return uscPrepareAction('signin')">Sign In <img src="images/submit.png" class="h-icon-2"></button>
              <button type="submit" class="btn btn-warning hide"
                      id="usc_signOutBtn"
                      onclick="return uscPrepareAction('signout')">Sign Out <img src="images/logout.png" class="h-icon-2"></button>
            </div>
          </form>
        </div><!-- /USC card -->


        <!-- ═══════════════════════════════════════════════════════════
             Guest Sign-In
             All element IDs are prefixed with "guest_"
             No USC ID field — guests are looked up by name.
        ════════════════════════════════════════════════════════════════ -->
        <div class="card card-pad">
          <div class="top-card">
            <div>
              <img src="images/signin.png" class="h-icon">
              <h2>Guest User Session</h2>
            </div>
          </div>
          <p class="muted" style="margin-top: 18px;">Enter your full name to sign in or out. For visitors and guests.</p>

          <div class="row" style="margin-top:16px;">
            <div>
              <div class="grid grid-3">
                <div>
                  <label for="guest_fname_lookup">First Name</label>
                  <input id="guest_fname_lookup" type="text" placeholder="First Name">
                </div>
                <div>
                  <label for="guest_mname_lookup">Middle Name</label>
                  <input id="guest_mname_lookup" type="text" placeholder="Middle Name">
                </div>
                <div>
                  <label for="guest_lname_lookup">Last Name</label>
                  <input id="guest_lname_lookup" type="text" placeholder="Last Name">
                </div>
              </div>
              <div class="actions" style="margin-top:20px;">
                <button type="button" class="btn btn-primary" onclick="lookupGuest()">Submit</button>
              </div>
            </div>
            <p class="muted">
              New Guest? <a href="#" id="guest_registerLink">Click Here to Register</a>
            </p>
            <div id="guest_statusBox"></div>
          </div>

          <form id="guestTraceForm" class="row" action="api/save_visit.php" method="post" style="margin-top:18px;">
            <input type="hidden" id="guest_action" name="action" value="register">
            <input type="hidden" name="user_type" value="Guest">
            <input type="hidden" id="guest_first_name" name="guest_first_name" value="">
            <input type="hidden" id="guest_middle_name" name="guest_middle_name" value="">
            <input type="hidden" id="guest_last_name" name="guest_last_name" value="">

            <div id="guest_formFields" class="hide">
              <div class="grid grid-3">
                <div>
                  <label for="guest_fname_display">First Name *</label>
                  <input id="guest_fname_display" name="first_name" type="text"
                         placeholder="First Name" required>
                </div>
                <div>
                  <label for="guest_mname_display">Middle Name *</label>
                  <input id="guest_mname_display" name="middle_name" type="text"
                         placeholder="Middle Name">
                </div>
                <div>
                  <label for="guest_lname_display">Last Name *</label>
                  <input id="guest_lname_display" name="last_name" type="text"
                         placeholder="Last Name" required>
                </div>
              </div>
              <div class="grid grid-2">
                <div>
                  <label for="guest_contact_number">Contact Number *</label>
                  <input id="guest_contact_number" name="phone_number" type="tel"
                         placeholder="09XXXXXXXXX" required>
                </div>

                <div>
                  <label for="guest_email">Email *</label>
                  <input id="guest_email" name="email" type="email"
                         placeholder="name@example.com" required>
                </div>

              </div>

              <div class="grid grid-3" style="margin-top:14px;">
                <div>
                  <label for="guest_barangay">Barangay *</label>
                  <input id="guest_barangay" name="barangay" type="text"
                         placeholder="Barangay" required>
                </div>
                <div>
                  <label for="guest_city">City / Town *</label>
                  <input id="guest_city" name="city" type="text"
                         placeholder="City or Town" required>
                </div>
                <div>
                  <label for="guest_province">Province *</label>
                  <input id="guest_province" name="province" type="text"
                         placeholder="Province" required>
                </div>
              </div>
            </div><!-- /guest_formFields -->

            <div id="guest_actionBox" class="actions hide">
              <button type="submit" class="btn btn-primary"
                      id="guest_registerBtn"
                      onclick="return guestPrepareAction('register')">Register &amp; Sign In <img src="images/submit.png" class="h-icon-2"></button>
              <button type="submit" class="btn btn-primary hide"
                      id="guest_signInBtn"
                      onclick="return guestPrepareAction('signin')">Sign In <img src="images/submit.png" class="h-icon-2"></button>
              <button type="submit" class="btn btn-warning hide"
                      id="guest_signOutBtn"
                      onclick="return guestPrepareAction('signout')">Sign Out <img src="images/logout.png" class="h-icon-2"></button>
            </div>
          </form>
        </div><!-- /Guest card -->

      </div><!-- /grid -->
    </div><!-- /container -->
  </div><!-- /main -->

  <script src="assets/public.js"></script>

  <footer>
      <div class="footer-logos">
          <img src="images/usc.png" style="width: 35px; height: 35px;">
          <img src="images/dcpe2.png" style="width: 35px; height: 35px;">
      </div>
      <p>© 2026 University of San Carlos Department of Computer Engineering</p>
  </footer>
</body>
</html>
