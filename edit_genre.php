<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Fetch the genre to edit
if (isset($_GET['id'])) {
    $genre_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM genres WHERE id = ?");
    $stmt->bind_param("i", $genre_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='alert alert-danger'>Genre not found.</div>";
        exit();
    }

    $genre = $result->fetch_assoc();
} else {
    header("Location: genre.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    if (!empty($name)) {
        // Check if the new genre name already exists
        $checkQuery = "SELECT COUNT(*) FROM genres WHERE name = ? AND id != ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("si", $name, $genre_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo "<div class='alert alert-danger'>Genre already exists. Please choose a different name.</div>";
        } else {
            // Update the genre
            $stmt = $conn->prepare("UPDATE genres SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $genre_id);

            if ($stmt->execute()) {
                header("Location: genre.php?message=Genre updated successfully.");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error updating genre: " . $stmt->error . "</div>";
            }

            $stmt->close();
        }
    } else {
        echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Genre - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Edit Genre</h1>

    <!-- Edit Genre Form -->
    <form action="edit_genre.php?id=<?= $genre['id']; ?>" method="POST">
        <div class="form-group">
            <label for="name">Genre Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($genre['name']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Genre</button>
        <a href="genre.php" class="btn btn-secondary">Back</a> <!-- Back button -->
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
