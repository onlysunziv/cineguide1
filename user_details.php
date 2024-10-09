<?php
include 'includes/db_connect.php';

// Fetch users
$query = "SELECT * FROM users";
$result = $conn->query($query);

// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white; /* Ensure text is readable against the background */
        }
        .container {
            background: rgba(255, 255, 255, 0.5); /* Light background for container */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            margin-top: 20px; /* Margin to separate from navbar */
        }
        table {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.5); /* More transparent background for table */
        }
        th {
            background-color: rgba(0, 123, 255, 0.9); /* Semi-transparent bootstrap primary color */
            color: white;
        }
        tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.2); /* Light hover effect */
        }
        .btn-custom {
            margin-right: 5px;
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
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>User Management</h1>

    <!-- User Management Section -->
    <section id="user-management">
        <h2>Manage Users</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><?= htmlspecialchars($user['name']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= htmlspecialchars($user['phone']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
