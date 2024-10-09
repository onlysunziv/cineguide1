<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Fetch the category to edit
if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='alert alert-danger'>Category not found.</div>";
        exit();
    }

    $category = $result->fetch_assoc();
} else {
    header("Location: category.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        // Check if the category name already exists
        $stmt = $conn->prepare("SELECT * FROM categories WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $category_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 0) {
            // Proceed to update the category
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $category_id);

            if ($stmt->execute()) {
                header("Location: category.php?message=Category updated successfully.");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error updating category: " . $stmt->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Category name already exists. Please choose a different name.</div>";
        }

        $stmt->close();
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
    <title>Edit Category - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Edit Category</h1>

    <!-- Edit Category Form -->
    <form action="edit_category.php?id=<?= $category['id']; ?>" method="POST">
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="category.php" class="btn btn-secondary">Back to Categories</a>
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
