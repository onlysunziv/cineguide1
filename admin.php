<?php
include 'includes/db_connect.php';

session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect if not logged in
    exit();
}

// Hardcoded admin details (for demonstration purposes)
$admin_name = "Admin User";
$admin_email = "cineguide@gmail.com";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background for the whole body */
        }
        .content {
            padding: 20px;
            background: url('background.jpg') no-repeat center center fixed; /* Background image only for content */
            background-size: cover;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            height: calc(100vh - 56px); /* Adjust height for navbar */
            color: white; /* Ensure text is readable */
        }
        .admin-header {
            margin-bottom: 2rem;
        }
        .logout-btn {
            margin-left: 15px;
        }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="adminhome.php">Cineguide Admin</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="user_details.php">User Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="movie_details.php">Movie Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="genre.php">Genre Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="category.php">Category Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="rating_details.php">Ratings Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_details.php">Admin Details</a>
            </li>
        </ul>
        <a href="adminlogout.php" class="btn btn-danger logout-btn">Logout</a>
    </div>
</nav>


<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
