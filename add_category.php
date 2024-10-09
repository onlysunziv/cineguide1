<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Handle category submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    
    // Initialize error and success messages
    $success_message = '';
    $error_message = '';

    // Check if category name is empty
    if (empty($category_name)) {
        $error_message = "Category name is required.";
    } else {
        // Check if category already exists
        $query = "SELECT * FROM categories WHERE name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Category already exists.";
        } else {
            // Insert category into database
            $query = "INSERT INTO categories (name) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $category_name);
            if ($stmt->execute()) {
                // Set session variable and redirect to admin panel
                $_SESSION['success_message'] = "Category added successfully!";
                header("Location: admin.php"); // Redirect to admin.php
                exit();
            } else {
                $error_message = "Error adding category.";
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
    <title>Add Category - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Add New Category</h1>

    <?php if (isset($error_message) && !empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="category_name">Category Name</label>
            <input type="text" class="form-control" id="category_name" name="category_name" required>
        </div>
        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
        <a href="category.php" class="btn btn-secondary">Back to Categories</a>
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
