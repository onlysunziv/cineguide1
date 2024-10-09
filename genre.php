<?php
include 'includes/db_connect.php';
include 'admin.php';

// Initialize success and error messages
$success_message = '';
$error_message = '';

// Handle genre submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_genre'])) {
        // Add genre logic
        $genre_name = trim($_POST['genre_name']);

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
    } elseif (isset($_POST['edit_genre'])) {
        // Edit genre logic
        $genre_id = $_POST['genre_id'];
        $genre_name = trim($_POST['genre_name']);

        // Check if genre name is empty
        if (empty($genre_name)) {
            $error_message = "Genre name is required.";
        } else {
            // Check if genre already exists (exclude the current genre)
            $query = "SELECT * FROM genres WHERE name = ? AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $genre_name, $genre_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "Genre already exists.";
            } else {
                // Update genre in database
                $query = "UPDATE genres SET name = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $genre_name, $genre_id);
                if ($stmt->execute()) {
                    $success_message = "Genre updated successfully!";
                } else {
                    $error_message = "Error updating genre.";
                }
            }
        }
    }
}

// Fetch genres after adding or editing a new one
$query = "SELECT * FROM genres";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genre Management - Cineguide</title>
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
        .add-genre-form, .edit-genre-form {
            display: none; /* Initially hidden */
            margin-top: 20px;
        }
    </style>
    <script>
        function toggleAddGenreForm() {
            const form = document.getElementById('add-genre-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function showEditForm(id, name) {
            const form = document.getElementById('edit-genre-form');
            document.getElementById('edit_genre_id').value = id;
            document.getElementById('edit_genre_name').value = name;
            form.style.display = 'block';
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h1>Genre Management</h1>

    <!-- Notification Message -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Add Genre Button -->
    <button onclick="toggleAddGenreForm()" class="btn btn-success mb-3">Add New Genre</button>

    <!-- Inline Add Genre Form -->
    <div id="add-genre-form" class="add-genre-form">
        <form method="POST" action="genre.php">
            <div class="form-group">
                <label for="genre_name">Genre Name</label>
                <input type="text" class="form-control" id="genre_name" name="genre_name" required>
            </div>
            <button type="submit" name="add_genre" class="btn btn-primary">Add Genre</button>
            <button type="button" class="btn btn-secondary" onclick="toggleAddGenreForm()">Cancel</button>
        </form>
    </div>

    <!-- Inline Edit Genre Form -->
    <div id="edit-genre-form" class="edit-genre-form">
        <form method="POST" action="genre.php">
            <input type="hidden" id="edit_genre_id" name="genre_id">
            <div class="form-group">
                <label for="edit_genre_name">Genre Name</label>
                <input type="text" class="form-control" id="edit_genre_name" name="genre_name" required>
            </div>
            <button type="submit" name="edit_genre" class="btn btn-primary">Update Genre</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-genre-form').style.display='none';">Cancel</button>
        </form>
    </div>

    <!-- Genre Management Table -->
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Genre Name</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($genre = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($genre['id']); ?></td>
                <td><?= htmlspecialchars($genre['name']); ?></td>
                <td>
                    <button onclick="showEditForm(<?= $genre['id']; ?>, '<?= htmlspecialchars($genre['name']); ?>')" class="btn btn-sm btn-primary">Edit</button>
                    <a href="delete_genre.php?id=<?= $genre['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this genre?');">Delete</a>
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
