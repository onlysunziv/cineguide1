<?php
session_start();

// Hardcoded admin credentials
$admin_email = "cineguide@gmail.com";
$admin_password = "cineguide"; // Use plain text for testing only, don't use in production

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Debugging output
    error_log("Email: $email, Password: $password");

    // Check credentials
    if ($email === $admin_email && $password === $admin_password) {
        $_SESSION['admin_id'] = 1; // Set session variable (or a unique admin ID)
        header("Location: adminhome.php"); // Redirect to admin dashboard
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cineguide - Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            height: 100vh; /* Full viewport height */
        }
        .logo-container {
            width: 65%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        .login-card {
            max-width: 400px; /* Same width as user login */
            padding: 2rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .btn-signup, .btn-admin {
            width: 100%;
            margin-top: 1rem;
        }
        .button-group {
            display: flex;
            justify-content: space-between; /* Space buttons evenly */
            margin-top: 1rem; /* Space above the button group */
        }
    </style>
</head>
<body>

<!-- Main Layout -->
<div class="container-fluid d-flex">
    <!-- Logo Section -->
    <div class="logo-container">
        <a href="landingpage.php">
            <img src="logo.png" alt="Cineguide Logo" class="img-fluid">
        </a>
    </div>

    <!-- Login Form Section -->
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-header">Admin-Login Cineguide</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary">Login</button>
                <a href="login.php" class="btn btn-secondary btn-signup">Back to user login</a>
            </form>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
