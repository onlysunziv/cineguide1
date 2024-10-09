<?php
include 'includes/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Fetch admin data
$adminId = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    echo "Admin not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['password'];

    // Update password without hashing (as per your request)
    $updateQuery = "UPDATE admin SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newPassword, $adminId);

    if ($stmt->execute()) {
        // Redirect to admin details page after a successful update
        header('Location: admin_details.php?update=success');
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error updating password: " . $stmt->error . "</div>";
    }
}

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Change Password</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
    <style>
        body {
            background-image: url('background.jpg'); /* Background image */
            background-size: cover; /* Cover the entire background */
            background-position: center; /* Center the background */
            color: #fff;
        }
        .container {
            max-width: 500px;
            margin-top: 30px;
        }
        .card {
            background-color: rgba(44, 44, 44, 0.7); /* Transparent background for the card */
            border: none;
            border-radius: 15px;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .back-button {
            position: absolute;
            top: 10px;
            right: 180%; /* Back button positioned on the left */
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container position-relative">
    <button class="back-button" onclick="window.location.href='admin_details.php'"><i class="fas fa-arrow-left"></i></button> <!-- Back arrow button -->
    <div class="card">
        <h1>Change Password</h1>

        <form action="change_pass.php" method="POST">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
            <a href="admin_details.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>
