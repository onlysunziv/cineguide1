<?php
include 'includes/db_connect.php';
include 'admin.php';

// Fetch movies
$query = "SELECT * FROM movies";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Management - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }
        h2 {
            text-align: center;
            color: #333;
            font-weight: 700;
            margin-bottom: 20px;
        }
        table {
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            overflow: hidden;
            width: 100%;
        }
        thead {
            background-color: rgba(0, 123, 255, 0.7);
            color: white;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(222, 226, 230, 0.6);
        }
        tbody tr {
            transition: background-color 0.3s ease;
        }
        tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.2);
        }
        .movie-image {
            width: 80px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-custom {
            margin-right: 5px;
        }
        .btn-primary, .btn-danger {
            border-radius: 20px;
            padding: 5px 12px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .table-actions {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="container mt-5">

    <!-- Add Movie Button (Navigates Directly to add_movie.php) -->
    <a href="add_movie.php" class="btn btn-success mb-3">
        Add New Movie
    </a>

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Movie added successfully!</div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Movie deleted successfully!</div>
    <?php endif; ?>

    <!-- Movie Management Section -->
    <section id="movie-management" class="mb-5">
        <h2>Manage Movies</h2>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Genre</th>
                    <th>Category</th>
                    <th>Cast</th>
                    <th>Release Date</th>
                    <th>Trailer Link</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($movie = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($movie['id']); ?></td>
                        <td><?= htmlspecialchars($movie['title']); ?></td>
                        <td><?= htmlspecialchars($movie['genre']); ?></td>
                        <td><?= htmlspecialchars($movie['category']); ?></td>
                        <td><?= htmlspecialchars($movie['cast']); ?></td>
                        <td><?= htmlspecialchars($movie['release_date']); ?></td>
                        <td><?= htmlspecialchars($movie['trailer']); ?></td>
                        <td>
                            <?php if (!empty($movie['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($movie['image']); ?>" alt="<?= htmlspecialchars($movie['title']); ?>" class="movie-image">
                            <?php else: ?>
                                <span>No image</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <a href="edit_movie.php?id=<?= $movie['id']; ?>" class="btn btn-sm btn-primary btn-custom">Edit</a>
                            <a href="delete_movie.php?id=<?= $movie['id']; ?>" class="btn btn-sm btn-danger btn-custom" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
