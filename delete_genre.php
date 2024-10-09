<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Check if genre ID is set in the URL
if (isset($_GET['id'])) {
    $genre_id = intval($_GET['id']); // Get and sanitize the genre ID

    // Prepare SQL statement to delete the genre
    $stmt = $conn->prepare("DELETE FROM genres WHERE id = ?");
    $stmt->bind_param("i", $genre_id);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: genre.php?message=Genre deleted successfully.");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting genre: " . $stmt->error . "</div>";
    }

    // Close statement
    $stmt->close();
} else {
    header("Location: genre.php");
    exit();
}

$conn->close();
?>
