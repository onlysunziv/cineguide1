<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Handle genre submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_genre'])) {
    $genre_name = trim($_POST['genre_name']);
    
    // Initialize error and success messages
    $success_message = '';
    $error_message = '';

    // Check if genre name is empty
    if (empty($genre_name)) {
        $error_message = "Genre name is required.";
    } else {
        // Check if genre already exists
        $query = "SELECT * FROM genres WHERE name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $genre_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Genre already exists.";
        } else {
            // Insert genre into database
            $query = "INSERT INTO genres (name) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $genre_name);
            if ($stmt->execute()) {
                $success_message = "Genre added successfully!";
            } else {
                $error_message = "Error adding genre.";
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
    <title>Add Genre - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background color */
        }
        .form-container {
            background-color: rgba(255, 255, 255, 0.8); /* White background with transparency */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3); /* Subtle shadow */
        }
        h1 {
            color: #343a40; /* Darker text color */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="form-container">
        <h1>Add New Genre</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="genre_name">Genre Name</label>
                <input type="text" class="form-control" id="genre_name" name="genre_name" required>
            </div>
            <button type="submit" name="add_genre" class="btn btn-primary">Add Genre</button>
            <a href="genre.php" class="btn btn-secondary">Back to Genres</a>
        </form>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
