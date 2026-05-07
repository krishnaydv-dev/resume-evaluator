<?php
// ============================================
// Start session so we can access and destroy it
// ============================================
session_start();

// ============================================
// Step 1 — Clear all session variables
// ============================================
// This empties the $_SESSION array
// user_id, user_name etc are all removed
// ============================================
$_SESSION = [];

// ============================================
// Step 2 — Destroy the session completely
// ============================================
// session_destroy() removes the session file
// from the server. The browser cookie becomes
// invalid so even if someone has the old
// cookie they can't use it anymore.
// ============================================
session_destroy();

// ============================================
// Step 3 — Redirect to login page
// ============================================
header('Location: login.php');
exit();
?>