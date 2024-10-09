<?php
include 'includes/db_connect.php';

session_start();

// Get the selected category name from the URL
$category_name = isset($_GET['name']) ? $_GET['name'] : '';

// Fetch the selected category details and its movies
$category = null;
$movies = [];
if ($conn && !empty($category_name)) {
    // Fetch category details by category name
    $categoryQuery = "SELECT * FROM categories WHERE name = ?";
    $stmt = $conn->prepare($categoryQuery);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();

    // Fetch movies belonging to the selected category using category name
    if ($category) {
        $moviesQuery = "SELECT * FROM movies WHERE category = ?";
        $stmt = $conn->prepare($moviesQuery);
        $stmt->bind_param("s", $name); // Use the category name
        $stmt->execute();
        $moviesResult = $stmt->get_result();

        while ($row = $moviesResult->fetch_assoc()) {
            $movies[] = $row;
        }
    }

    $stmt->close();
} else {
    $error_message = "Invalid category or database connection failed.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies by Category</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            position: relative;
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 3;
            padding: 3;
        }

        .blur-overlay {
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(4px);
            border-radius: 12px;
        }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 75%;
            overflow: hidden;
            margin-left: 10px;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .card-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            background-color: rgba(255, 255, 255, 0.3);
        }

        .card-hover img {
            border-radius: 15px 15px 0 0;
            transition: opacity 0.3s ease;
            width: 100%;
            max-width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: contain;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 9px;
            flex: 4;
        }

        .card-title {
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
            color: lightgrey;
        }

        .card-text {
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>

<!-- Content -->
<div class="container mt-5">
    <?php if ($category): ?>
        <h1 class="text-center mb-4">Movies in Category: <?= htmlspecialchars($category['name']); ?></h1>
        <div class="row">
            <?php if (!empty($movies)): ?>
                <?php foreach ($movies as $movie): ?>
                    <div class="col-md-4 mb-4">
                        <a href="movie_page.php?movie_id=<?= $movie['id']; ?>" class="text-decoration-none">
                            <div class="card card-hover blur-overlay">
                                <?php if (!empty($movie['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($movie['image']); ?>" alt="<?= htmlspecialchars($movie['title']); ?>" class="movie-image">
                                <?php else: ?>
                                    <span>No image</span>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($movie['title']); ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($movie['cast']); ?></p>
                                    <p class="card-text"><strong>Release Date:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($movie['release_date']))); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No movies available in this category.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <h1 class="text-center">Category not found</h1>
    <?php endif; ?>
</div>

</body>
</html>
