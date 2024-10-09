<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Check if an ID is provided
if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];

    // Prepare SQL statement to delete the movie
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to movie_details.php with a success message
        header("Location: movie_details.php?deleted=true");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting movie: " . $stmt->error . "</div>";
    }

    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>No movie ID provided.</div>";
}

mysqli_close($conn);
?>
