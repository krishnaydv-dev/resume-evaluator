<?php
// ============================================
// Session Check
// ============================================
// session_start() must be called at the top
// of every page that uses sessions (login state).
// We put it here in header.php so every page
// that includes this file gets sessions automatically.
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // We check PHP_SESSION_NONE first to avoid
    // "session already started" errors
}

// ============================================
// $pageTitle variable
// ============================================
// Each page sets its own $pageTitle before
// including this header. That way the browser
// tab shows the right title per page.
// Example: $pageTitle = "Dashboard";
// ============================================

$pageTitle = $pageTitle ?? 'Resume Evaluator';
// ?? is the null coalescing operator —
// if $pageTitle wasn't set, use default
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Resume Evaluator</title>

    <!-- Our main stylesheet -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- ============================================
     NAVBAR
     Only shown when user is logged in
     $_SESSION['user_id'] is set on login
     and destroyed on logout
     ============================================ -->

<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar">
    <div class="nav-brand">
        📄 Resume Evaluator
    </div>
    <ul class="nav-links">
        <li><a href="/dashboard.php">Dashboard</a></li>
        <li><a href="/upload.php">New Evaluation</a></li>
        <li><a href="/history.php">History</a></li>
        <li><a href="/logout.php" class="btn-logout">Logout</a></li>
    </ul>
    <div class="nav-user">
        <!-- Show logged in user's name from session -->
        👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        <!-- htmlspecialchars() prevents XSS attacks —
             converts < > & characters to safe HTML entities -->
    </div>
</nav>
<?php endif; ?>

<!-- Page content starts here — each page adds its own content below this -->