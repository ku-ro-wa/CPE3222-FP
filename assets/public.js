// ── Validation helpers ────────────────────────────────────────────────────────

function isValidName(v)  { return /^[a-zA-ZÀ-ÖØ-öø-ÿ0-9\s'\-]+$/.test(v.trim()); }
function isValidId(v)    { return /^\d+$/.test(v.trim()); }
function isValidPhone(v) { return /^\d{7,15}$/.test(v.replace(/[\s\-\+\(\)]/g, '')); }
function isValidEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); }

function showErrors(statusBoxId, errors) {
  const box = document.getElementById(statusBoxId);
  if (!box) return;
  if (!errors.length) return;
  const items = errors.map(e => `<li>${e}</li>`).join('');
  box.innerHTML = `<div class="alert alert-error"><ul style="margin:0;padding-left:18px;">${items}</ul></div>`;
}

function validateUscForm() {
  const errors = [];
  const get = id => document.getElementById(id)?.value.trim() ?? '';

  const firstName = get('usc_first_name');
  const middleName = get('usc_middle_name');
  const lastName   = get('usc_last_name');
  const idNumber   = get('usc_id_display');
  const phone      = get('usc_contact_number');
  const email      = get('usc_email');

  if (firstName  && !isValidName(firstName))   errors.push('First name contains invalid characters.');
  if (middleName && !isValidName(middleName))  errors.push('Middle name contains invalid characters.');
  if (lastName   && !isValidName(lastName))    errors.push('Last name contains invalid characters.');
  if (idNumber   && !isValidId(idNumber))      errors.push('ID number must be numeric.');
  if (phone      && !isValidPhone(phone))      errors.push('Phone number must be 7–15 digits.');
  if (email      && !isValidEmail(email))      errors.push('Email must contain an @ symbol and a valid domain.');

  showErrors('usc_statusBox', errors);
  return errors.length === 0;
}

function validateGuestForm() {
  const errors = [];
  const get = id => document.getElementById(id)?.value.trim() ?? '';

  const firstName  = get('guest_fname_display');
  const middleName = get('guest_mname_display');
  const lastName   = get('guest_lname_display');
  const phone      = get('guest_contact_number');
  const email      = get('guest_email');

  if (firstName  && !isValidName(firstName))   errors.push('First name contains invalid characters.');
  if (middleName && !isValidName(middleName))  errors.push('Middle name contains invalid characters.');
  if (lastName   && !isValidName(lastName))    errors.push('Last name contains invalid characters.');
  if (phone      && !isValidPhone(phone))      errors.push('Phone number must be 7–15 digits.');
  if (email      && !isValidEmail(email))      errors.push('Email must contain an @ symbol and a valid domain.');

  showErrors('guest_statusBox', errors);
  return errors.length === 0;
}


// ── USC Sign-In ───────────────────────────────────────────────────────────────

async function lookupUser() {
  const idField     = document.getElementById('usc_id_lookup');
  const id          = idField.value.trim();
  const statusBox   = document.getElementById('usc_statusBox');
  const formFields  = document.getElementById('usc_formFields');
  const actionBox   = document.getElementById('usc_actionBox');
  const signInBtn   = document.getElementById('usc_signInBtn');
  const signOutBtn  = document.getElementById('usc_signOutBtn');
  const registerBtn = document.getElementById('usc_registerBtn');

  if (!id) {
    statusBox.innerHTML = '<div class="alert alert-error">Please enter an ID number first.</div>';
    formFields.classList.add('hide');
    actionBox.classList.add('hide');
    return;
  }

  if (!isValidId(id)) {
    statusBox.innerHTML = '<div class="alert alert-error">ID number must be numeric.</div>';
    formFields.classList.add('hide');
    actionBox.classList.add('hide');
    return;
  }

  document.getElementById('usc_id_display').value = id;
  statusBox.innerHTML = '<div class="alert alert-info">Checking record...</div>';

  try {
    const res  = await fetch('api/lookup.php?id_number=' + encodeURIComponent(id));
    const data = await res.json();

    if (data.ok && data.user) {
      uscFillForm(data.user);
      uscSetReadonly(true);
      statusBox.innerHTML = '<div class="alert alert-success">Record found. Please verify your details before signing in or out.</div>';
      formFields.classList.remove('hide');
      actionBox.classList.remove('hide');
      registerBtn.classList.add('hide');
      signInBtn.classList.remove('hide');
      signOutBtn.classList.remove('hide');
    } else {
      uscClearForm();
      uscSetReadonly(false);
      statusBox.innerHTML = '<div class="alert alert-info">No previous record found. Please complete the registration form.</div>';
      formFields.classList.remove('hide');
      actionBox.classList.remove('hide');
      registerBtn.classList.remove('hide');
      signInBtn.classList.add('hide');
      signOutBtn.classList.add('hide');
    }
  } catch {
    statusBox.innerHTML = '<div class="alert alert-error">Unable to reach the lookup service. Please try again.</div>';
    formFields.classList.add('hide');
    actionBox.classList.add('hide');
  }
}

function uscFillForm(user) {
  const fields = ['first_name', 'middle_name', 'last_name', 'barangay', 'city', 'province', 'email'];
  fields.forEach(k => {
    const el = document.getElementById('usc_' + k);
    if (el) el.value = user[k] || '';
  });
  // DB column is phone_number; element ID is usc_contact_number
  const phoneEl = document.getElementById('usc_contact_number');
  if (phoneEl) phoneEl.value = user.phone_number || '';

  const display = document.getElementById('usc_id_display');
  if (display) display.value = user.id_number || '';
  const userType = document.getElementById('usc_user_type');
  if (userType) userType.value = user.user_type || 'Student';
}

function uscClearForm() {
  const fields = ['first_name', 'middle_name', 'last_name', 'barangay', 'city', 'province', 'email'];
  fields.forEach(k => {
    const el = document.getElementById('usc_' + k);
    if (el) el.value = '';
  });
  const phoneEl = document.getElementById('usc_contact_number');
  if (phoneEl) phoneEl.value = '';
  const display = document.getElementById('usc_id_display');
  if (display) display.value = '';
  const userType = document.getElementById('usc_user_type');
  if (userType) userType.selectedIndex = 0;
}

function uscSetReadonly(readonly) {
  const fields = ['first_name', 'middle_name', 'last_name', 'barangay', 'city', 'province', 'email'];
  fields.forEach(k => {
    const el = document.getElementById('usc_' + k);
    if (el) el.readOnly = readonly;
  });
}

function uscPrepareAction(action) {
  if (!validateUscForm()) return false;
  const actionField = document.getElementById('usc_action');
  if (actionField) actionField.value = action;
  return true;
}


// ── Guest Sign-In ─────────────────────────────────────────────────────────────

async function lookupGuest() {
  const fname      = document.getElementById('guest_fname_lookup').value.trim();
  const mname      = document.getElementById('guest_mname_lookup').value.trim();
  const lname      = document.getElementById('guest_lname_lookup').value.trim();
  const statusBox  = document.getElementById('guest_statusBox');
  const formFields = document.getElementById('guest_formFields');
  const actionBox  = document.getElementById('guest_actionBox');
  const signInBtn  = document.getElementById('guest_signInBtn');
  const signOutBtn = document.getElementById('guest_signOutBtn');
  const registerBtn= document.getElementById('guest_registerBtn');

  if (!fname || !lname) {
    statusBox.innerHTML = '<div class="alert alert-error">Please enter both your first and last name.</div>';
    formFields.classList.add('hide');
    actionBox.classList.add('hide');
    return;
  }

  if (!isValidName(fname) || (mname && !isValidName(mname)) || !isValidName(lname)) {
    statusBox.innerHTML = '<div class="alert alert-error">Names must contain only letters, spaces, hyphens, or apostrophes.</div>';
    formFields.classList.add('hide');
    actionBox.classList.add('hide');
    return;
  }

  document.getElementById('guest_first_name').value  = fname;
  document.getElementById('guest_middle_name').value = mname;
  document.getElementById('guest_last_name').value   = lname;
  document.getElementById('guest_fname_display').value = fname;
  document.getElementById('guest_mname_display').value = mname;
  document.getElementById('guest_lname_display').value = lname;
  statusBox.innerHTML = '<div class="alert alert-info">Checking record...</div>';

  const res  = await fetch(
    'api/lookup.php?first_name=' + encodeURIComponent(fname) +
    '&last_name='                + encodeURIComponent(lname) +
    '&user_type=Guest'
  );
  const data = await res.json();

  if (data.ok && data.user) {
    guestFillForm(data.user);
    guestSetReadonly(true);
    statusBox.innerHTML = '<div class="alert alert-success">Record found. Please verify your details before signing in or out.</div>';
    formFields.classList.remove('hide');
    actionBox.classList.remove('hide');
    registerBtn.classList.add('hide');
    signInBtn.classList.remove('hide');
    signOutBtn.classList.remove('hide');
  } else {
    guestClearForm();
    guestSetReadonly(false);
    const fnEl = document.getElementById('guest_fname_display');
    const lnEl = document.getElementById('guest_lname_display');
    if (fnEl) fnEl.value = fname;
    if (lnEl) lnEl.value = lname;
    statusBox.innerHTML = '<div class="alert alert-info">No previous record found. Please complete the registration form.</div>';
    formFields.classList.remove('hide');
    actionBox.classList.remove('hide');
    registerBtn.classList.remove('hide');
    signInBtn.classList.add('hide');
    signOutBtn.classList.add('hide');
  }
}

function guestFillForm(user) {
  const fields = ['first_name', 'middle_name', 'last_name', 'barangay', 'city', 'province', 'email'];
  fields.forEach(k => {
    const el = document.getElementById('guest_' + k);
    if (el) el.value = user[k] || '';
  });
  // DB column is phone_number; element ID is guest_contact_number
  const phoneEl = document.getElementById('guest_contact_number');
  if (phoneEl) phoneEl.value = user.phone_number || '';
}

function guestClearForm() {
  const fields = ['first_name', 'middle_name', 'last_name', 'barangay', 'city', 'province', 'email'];
  fields.forEach(k => {
    const el = document.getElementById('guest_' + k);
    if (el) el.value = '';
  });
  const phoneEl = document.getElementById('guest_contact_number');
  if (phoneEl) phoneEl.value = '';
}

function guestSetReadonly(readonly) {
  const fields = ['first_name', 'middle_name', 'last_name', 'barangay', 'city', 'province', 'email'];
  fields.forEach(k => {
    const el = document.getElementById('guest_' + k);
    if (el) el.readOnly = readonly;
  });
}

function guestPrepareAction(action) {
  if (!validateGuestForm()) return false;
  const actionField = document.getElementById('guest_action');
  if (actionField) actionField.value = action;
  return true;
}


// ── DOMContentLoaded ──────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
  // Sync id_display with lookup input
  const lookup  = document.getElementById('usc_id_lookup');
  const display = document.getElementById('usc_id_display');
  if (lookup && display) {
    lookup.addEventListener('input', () => { display.value = lookup.value; });
  }

  // "New User?" register link
  const registerLink = document.getElementById('registerLink');
  if (registerLink) {
    registerLink.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('usc_formFields').classList.remove('hide');
      document.getElementById('usc_actionBox').classList.remove('hide');
      document.getElementById('usc_registerBtn').classList.remove('hide');
      document.getElementById('usc_signInBtn').classList.add('hide');
      document.getElementById('usc_signOutBtn').classList.add('hide');
      uscSetReadonly(false);
    });
  }

  // "New Guest?" register link
  const g_registerLink = document.getElementById('guest_registerLink');
  if (g_registerLink) {
    g_registerLink.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('guest_formFields').classList.remove('hide');
      document.getElementById('guest_actionBox').classList.remove('hide');
      document.getElementById('guest_registerBtn').classList.remove('hide');
      document.getElementById('guest_signInBtn').classList.add('hide');
      document.getElementById('guest_signOutBtn').classList.add('hide');
      guestSetReadonly(false);
    });
  }
});
