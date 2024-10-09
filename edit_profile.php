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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Check for duplicate values in the database
    $duplicateCheckQuery = "SELECT * FROM users WHERE (email = ? OR phone = ?) AND id != ?";
    $checkStmt = $conn->prepare($duplicateCheckQuery);
    $checkStmt->bind_param("ssi", $email, $phone, $userId);
    $checkStmt->execute();
    $duplicateResult = $checkStmt->get_result();

    if ($duplicateResult->num_rows > 0) {
        echo "<div class='alert alert-danger'>Email or Phone number already exists.</div>";
    } else {
        // Update user data
        $updateQuery = "UPDATE users SET name = ?, email = ?, address = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $name, $email, $address, $phone, $userId);

        if ($stmt->execute()) {
            // Redirect to the user profile page after a successful update
            header('Location: profile.php?update=success');
            exit;
        } else {
            echo "<div class='alert alert-danger'>Error updating profile: " . $stmt->error . "</div>";
        }
    }

    $checkStmt->close();
}

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Edit Profile</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
    <style>
        body {
            background-image: url('back.jpg'); /* Add your background image path here */
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
    <button class="back-button" onclick="window.location.href='profile.php'"><i class="fas fa-arrow-left"></i></button> <!-- Back arrow button -->
    <div class="card">
        <h1>Edit Profile</h1>

        <form action="edit_profile.php" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="profile.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>
