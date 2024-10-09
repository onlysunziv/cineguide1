<?php
include 'includes/db_connect.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Fetch user data
$userId = $_SESSION['user_id'];
$userQuery = "SELECT name, email, address, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>User Profile</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
    <style>
        body {
            background-image: url('back.jpg'); 
            background-size: cover; /* Cover the entire background */
            background-position: center; /* Center the background */
            color: #fff;
        }
        .card {
            width: 350px;
            background-color: rgba(44, 44, 44, 0.7); 
            border: none;
            border-radius: 15px;
        }
        .name {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
        }
        .text span {
            font-size: 14px;
            color: #ccc;
            display: block;
            margin: 5px 0;
        }
        .btn {
            width: 100%;
            margin-top: 15px;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px; 
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container mt-4 mb-4 d-flex justify-content-center position-relative">
    <button class="back-button" onclick="window.location.href='home.php'"><i class="fas fa-arrow-left"></i> Back</button> <!-- Back arrow button -->
    <div class="card p-4">
        <div class="d-flex flex-column justify-content-center align-items-center">
            <span class="name"><?php echo htmlspecialchars($user['name']); ?></span>
            <div class="text">
                <span><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></span>
                <span><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></span>
                <span><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></span>
            </div>
            <button class="btn btn-dark" onclick="window.location.href='edit_profile.php'">Edit Profile</button>
            <button class="btn btn-danger" onclick="logout()">Logout</button>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
<script>
function logout() {
    // Close the modal first
    var modal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
    if (modal) {
        modal.hide();
    }
    // Redirect to logout.php to handle the logout
    window.location.href = 'logout.php';
}
</script>
</body>
</html>
