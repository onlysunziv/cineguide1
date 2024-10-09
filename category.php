<?php
include 'includes/db_connect.php';
include 'admin.php';

// Initialize success and error messages
$success_message = '';
$error_message = '';

// Handle category submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        // Add category logic
        $category_name = trim($_POST['category_name']);

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
                    $success_message = "Category added successfully!";
                } else {
                    $error_message = "Error adding category.";
                }
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        // Edit category logic
        $category_id = $_POST['category_id'];
        $category_name = trim($_POST['category_name']);

        // Check if category name is empty
        if (empty($category_name)) {
            $error_message = "Category name is required.";
        } else {
            // Check if category already exists (exclude the current category)
            $query = "SELECT * FROM categories WHERE name = ? AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $category_name, $category_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "Category already exists.";
            } else {
                // Update category in database
                $query = "UPDATE categories SET name = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $category_name, $category_id);
                if ($stmt->execute()) {
                    $success_message = "Category updated successfully!";
                } else {
                    $error_message = "Error updating category.";
                }
            }
        }
    }
}

// Fetch categories after adding or editing a new one
$query = "SELECT * FROM categories";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        table {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.5);
        }
        th {
            background-color: rgba(0, 123, 255, 0.9);
            color: white;
        }
        tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.2);
        }
        .add-category-form, .edit-category-form {
            display: none; /* Initially hidden */
            margin-top: 20px;
        }
    </style>
    <script>
        function toggleAddCategoryForm() {
            const form = document.getElementById('add-category-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function showEditForm(id, name) {
            const form = document.getElementById('edit-category-form');
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            form.style.display = 'block';
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h1>Category Management</h1>

    <!-- Notification Message -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Add Category Button -->
    <button onclick="toggleAddCategoryForm()" class="btn btn-success mb-3">Add New Category</button>

    <!-- Inline Add Category Form -->
    <div id="add-category-form" class="add-category-form">
        <form method="POST" action="category.php">
            <div class="form-group">
                <label for="category_name">Category Name</label>
                <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            <button type="button" class="btn btn-secondary" onclick="toggleAddCategoryForm()">Cancel</button>
        </form>
    </div>

    <!-- Inline Edit Category Form -->
    <div id="edit-category-form" class="edit-category-form">
        <form method="POST" action="category.php">
            <input type="hidden" id="edit_category_id" name="category_id">
            <div class="form-group">
                <label for="edit_category_name">Category Name</label>
                <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
            </div>
            <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-category-form').style.display='none';">Cancel</button>
        </form>
    </div>

    <!-- Category Management Table -->
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($category = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($category['id']); ?></td>
                <td><?= htmlspecialchars($category['name']); ?></td>
                <td>
                    <button onclick="showEditForm(<?= $category['id']; ?>, '<?= htmlspecialchars($category['name']); ?>')" class="btn btn-sm btn-primary">Edit</button>
                    <a href="delete_category.php?id=<?= $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
