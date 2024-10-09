<?php
include 'includes/db_connect.php';

// Start the session
session_start();

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $selectedGenres = $_POST['genre']; // This will be an array
    $category_name = $_POST['category'];
    $cast = $_POST['cast'];
    $release_date = $_POST['release_date'];
    $trailer = $_POST['trailer'];
    $description = $_POST['description'];

    // Check if movie already exists
    $checkMovieQuery = "SELECT COUNT(*) FROM movies WHERE title = ?";
    $stmt = $conn->prepare($checkMovieQuery);
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<div class='alert alert-danger'>Movie with the same title already exists!</div>";
    } else {
        // Handle file upload
        $image = $_FILES['image'];
        $imageName = time() . '_' . basename($image['name']);
        $targetDir = 'uploads/';
        $targetFilePath = $targetDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
            // Prepare the genres as a comma-separated string
            $genresString = implode(", ", $selectedGenres);

            // Prepare insert query for movies
            $insertQuery = "INSERT INTO movies (title, category, cast, release_date, trailer, description, image, genre) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssssssss", $title, $category_name, $cast, $release_date, $trailer, $description, $imageName, $genresString);

            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to movie_details.php upon successful addition
                header("Location: movie_details.php");
                exit(); // Ensure that the script stops executing after redirection
            } else {
                echo "<div class='alert alert-danger'>Error adding movie: " . $stmt->error . "</div>";
            }

            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error uploading image.</div>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie - Cineguide</title>
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
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group label {
            color: #333;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-secondary {
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Add a New Movie</h1>

    <!-- Add Movie Form -->
    <form action="add_movie.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Movie Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="genre">Genre</label>
            <select class="form-control" id="genre" name="genre[]" multiple size="5" required>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo htmlspecialchars($genre['id']); ?>"><?php echo htmlspecialchars($genre['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['name']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="cast">Cast</label>
            <input type="text" class="form-control" id="cast" name="cast" required>
        </div>
        <div class="form-group">
            <label for="release_date">Release Date</label>
            <input type="date" class="form-control" id="release_date" name="release_date" required>
        </div>
        <div class="form-group">
            <label for="trailer">Trailer Link</label>
            <input type="url" class="form-control" id="trailer" name="trailer" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="image">Movie Image</label>
            <input type="file" class="form-control-file" id="image" name="image" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Movie</button>
        <a href="movie_details.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
