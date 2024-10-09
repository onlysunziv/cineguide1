<?php
include 'includes/db_connect.php';
include 'admin.php';

// Fetch admin details
$adminId = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Details - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .container {
            backdrop-filter: blur(5px); /* Blurs the background */
            background-color: rgba(255, 255, 255, 0.5); /* Semi-transparent background */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        table {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent table background */
            border-radius: 8px;
            overflow: hidden; /* Ensures rounded corners are respected */
            margin-top: 20px;
        }
        th {
            background-color: #007bff; /* Opaque heading background */
            color: #fff; /* White heading text color */
            padding: 10px;
        }
        td {
            padding: 10px;
            color: #333; /* Darker text color for readability */
        }
        tr:nth-child(even) {
            background-color: rgba(240, 240, 240, 0.6); /* Light gray for even rows */
        }
        tr:hover {
            background-color: rgba(0, 123, 255, 0.1); /* Light blue on hover */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Admin Details</h1>
    <table class="table table-bordered">
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($admin['email']); ?></td>
            <th>Password</th>
            <td><?= htmlspecialchars($admin['password']); ?> <a href="change_pass.php" class="btn btn-link">Change Password</a></td>
        </tr>
    </table>
    
</div>
</body>
</html>
