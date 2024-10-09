<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Check if category ID is set in the URL
if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']); // Get and sanitize the category ID

    // Prepare SQL statement to delete the category
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: category.php?message=Category deleted successfully.");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting category: " . $stmt->error . "</div>";
    }

    // Close statement
    $stmt->close();
} else {
    header("Location: category.php");
    exit();
}

$conn->close();
?>
