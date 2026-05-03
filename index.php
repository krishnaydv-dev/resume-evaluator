<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit(); 
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // require_once here because we need $conn
    require_once 'config/db.php';

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic Validation
 
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        // filter_var() is PHP's built-in input validator
    } else {

        // Query Database

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // User found with that email
            $user = $result->fetch_assoc();

            // Verify Password
  
            if (password_verify($password, $user['password'])) {

                // ✅ Login successful!
                // Store user data in session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();

            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";

        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Resume Evaluator</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="auth-page">

<div class="auth-container">

    <!-- Logo / Branding -->
    <div class="auth-brand">
        <h1>📄 Resume Evaluator</h1>
        <p>AI-powered resume analysis</p>
    </div>

    <!-- Login Form -->
    <div class="auth-card">
        <h2>Welcome Back</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">


            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    placeholder="you@example.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password"
                    name="password" 
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">
                Login
            </button>

        </form>

        <p class="auth-switch">
            Don't have an account? 
            <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<script src="/js/main.js"></script>
</body>
</html>