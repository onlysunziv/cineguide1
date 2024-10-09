<?php
include 'includes/db_connect.php';
session_start();

// Handle signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Initialize error variable
    $error = '';

    // Check if the fields are empty
    if (empty($name) || empty($email) || empty($phone) || empty($_POST['password'])) {
        $error = "All fields are required.";
    } else {
        // Check if name, email, or phone already exists
        $query = "SELECT * FROM users WHERE name = ? OR email = ? OR phone = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $name, $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Name, email, or phone number already exists.";
        } else {
            // Insert new user
            $query = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $name, $email, $phone, $password);
            if ($stmt->execute()) {
                header("Location: home.php"); // Redirect to home after signup
                exit();
            } else {
                $error = "Error creating account.";
            }
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Cineguide</title>
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
            <form method="POST" action="signup.php">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="signup" class="btn btn-primary">Sign Up</button>
                <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
