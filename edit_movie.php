<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// Fetch movie details
if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();

    if (!$movie) {
        echo "<div class='alert alert-danger'>Movie not found.</div>";
        exit();
    }
}

// Fetch categories for the dropdown
$categories = [];
$categoryQuery = "SELECT * FROM categories";
$categoryResult = $conn->query($categoryQuery);

if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch genres for the dropdown
$genres = [];
$genreQuery = "SELECT * FROM genres";
$genreResult = $conn->query($genreQuery);

if ($genreResult) {
    while ($row = $genreResult->fetch_assoc()) {
        $genres[] = $row;
    }
}

// Handle form submission for updating movie
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $cast = $_POST['cast'];
    $release_date = $_POST['release_date'];
    $trailer = $_POST['trailer'];
    $selectedGenres = $_POST['genres']; // Get selected genres

    // Prepare the new genre names as a comma-separated string
    $newGenres = [];
    foreach ($selectedGenres as $genre_id) {
        // Get genre name from the genres table
        $genreStmt = $conn->prepare("SELECT name FROM genres WHERE id = ?");
        $genreStmt->bind_param("i", $genre_id);
        $genreStmt->execute();
        $genreResult = $genreStmt->get_result();
        if ($genreRow = $genreResult->fetch_assoc()) {
            $newGenres[] = $genreRow['name'];
        }
        $genreStmt->close();
    }
    $newGenreNames = implode(", ", $newGenres);

    // Handle image upload if a new image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target_dir = __DIR__ . "/uploads/";
        $target_file = $target_dir . basename($image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        if (getimagesize($_FILES["image"]["tmp_name"]) === false || 
            !in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<div class='alert alert-danger'>Invalid image file.</div>";
            exit();
        }

        // Move the uploaded file
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        
        // Update movie with new image and genre names
        $stmt = $conn->prepare("UPDATE movies SET title=?, category=?, cast=?, release_date=?, trailer=?, image=?, genre=? WHERE id=?");
        $stmt->bind_param("sssssssi", $title, $category, $cast, $release_date, $trailer, $image, $newGenreNames, $movie_id);
    } else {
        // Update movie without changing the image and with genre names
        $stmt = $conn->prepare("UPDATE movies SET title=?, category=?, cast=?, release_date=?, trailer=?, genre=? WHERE id=?");
        $stmt->bind_param("ssssssi", $title, $category, $cast, $release_date, $trailer, $newGenreNames, $movie_id);
    }

    // Execute update
    if ($stmt->execute()) {
        // Clear existing genres
        $conn->query("DELETE FROM movie_genres WHERE movie_id = $movie_id");

        // Insert new genres
        foreach ($selectedGenres as $genre_id) {
            $insertGenreStmt = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
            $insertGenreStmt->bind_param("ii", $movie_id, $genre_id);
            $insertGenreStmt->execute();
            $insertGenreStmt->close();
        }

        header("Location: movie_details.php?success=true");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating movie: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white; /* Ensure text is readable against the background */
        }
        .container {
            background: rgba(255, 255, 255, 0.5); /* Light background for container */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            max-width: 600px; /* Set a maximum width for the form */
            margin: 0 auto; /* Center the form */
        }
        .form-group label {
            color: #333; /* Darker label color for better visibility */
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Edit Movie</h1>

    <!-- Edit Movie Form -->
    <form action="edit_movie.php?id=<?= $movie['id']; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Movie Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($movie['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="genre">Genre</label>
            <select class="form-control" id="genre" name="genres[]" multiple required>
                <?php
                // Fetch already selected genres
                $selectedGenres = [];
                $genreSelectQuery = "SELECT genre_id FROM movie_genres WHERE movie_id = ?";
                $genreSelectStmt = $conn->prepare($genreSelectQuery);
                $genreSelectStmt->bind_param("i", $movie_id);
                $genreSelectStmt->execute();
                $genreSelectResult = $genreSelectStmt->get_result();
                while ($row = $genreSelectResult->fetch_assoc()) {
                    $selectedGenres[] = $row['genre_id'];
                }
                $genreSelectStmt->close();
                ?>

                <?php foreach ($genres as $genre): ?>
                    <option value="<?= htmlspecialchars($genre['id']); ?>" <?= in_array($genre['id'], $selectedGenres) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($genre['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['name']); ?>" <?= $category['name'] == $movie['category'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cast">Cast</label>
            <input type="text" class="form-control" id="cast" name="cast" value="<?= htmlspecialchars($movie['cast']); ?>" required>
        </div>
        <div class="form-group">
            <label for="release_date">Release Date</label>
            <input type="date" class="form-control" id="release_date" name="release_date" value="<?= htmlspecialchars($movie['release_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="trailer">Trailer Link</label>
            <input type="url" class="form-control" id="trailer" name="trailer" value="<?= htmlspecialchars($movie['trailer']); ?>" required>
        </div>
        <div class="form-group">
            <label for="image">Movie Image (optional)</label>
            <input type="file" class="form-control-file" id="image" name="image">
        </div>
        <button type="submit" class="btn btn-primary">Update Movie</button>
    </form>
</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
