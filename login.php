<?php
include 'includes/db_connect.php';
session_start();

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check credentials
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: home.php?authenticated=true");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cineguide - Login</title>
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
        }
        .login-card {
            max-width: 400px;
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
        .logo-container img {
            max-width: 80%; /* Ensure logo doesn't exceed 80% of the container */
            max-height: 80%; /* Ensure logo doesn't exceed 80% of the container height */
            height: auto; /* Maintain aspect ratio */
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
            <h1 class="login-header">Login to Cineguide</h1>
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
                <a href="admin_login.php" class="btn btn-warning btn-admin">Login as Admin</a>
                <a href="signup.php" class="btn btn-secondary btn-signup">Sign Up</a>
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
s