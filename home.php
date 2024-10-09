<?php
include 'includes/db_connect.php';

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$categories = [];
$genres = [];
$movies = [];
$recommendedMovies = [];
$searchTerm = '';

// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Fetch categories and genres
if ($conn) {
    // Fetch categories
    $categoryQuery = "SELECT * FROM categories";
    if ($categoryResult = $conn->query($categoryQuery)) {
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        echo "Error fetching categories: " . $conn->error;
    }

    // Fetch genres
    $genreQuery = "SELECT * FROM genres";
    if ($genreResult = $conn->query($genreQuery)) {
        while ($row = $genreResult->fetch_assoc()) {
            $genres[] = $row;
        }
    } else {
        echo "Error fetching genres: " . $conn->error;
    }

    // Fetch movies based on selected genre, category name, or search term
    $query = "SELECT * FROM movies";
    $params = [];
    $conditions = [];

    if (!empty($_GET['genre'])) {
        $selectedGenre = $_GET['genre'];
        $conditions[] = "genre = ?";
        $params[] = $selectedGenre;
    }

    if (!empty($_GET['category_name'])) {
        $selectedCategoryName = $_GET['category_name'];
        $conditions[] = "category = ?";
        $params[] = $selectedCategoryName;
    }

    if (!empty($_POST['search'])) {
        $searchTerm = $_POST['search'];
        $conditions[] = "(title LIKE ? OR cast LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($query);

    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch movies
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $movies[] = $row;
            }
        } else {
            echo "Error fetching movies: " . $conn->error;
        }
    }

    // Fetch recommended movies based on user ratings
    function fetchRecommendedMovies($conn, $userId) {
        $recommendedMovies = [];
        $userRatings = [];
        $allRatings = [];

        // Fetch the logged-in user's ratings
        $userRatingsQuery = "SELECT movie_id, rating FROM ratings WHERE user_id = ?";
        $stmt = $conn->prepare($userRatingsQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userRatings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Fetch all user ratings
        $allUserRatingsQuery = "SELECT user_id, movie_id, rating FROM ratings";
        $allUserRatingsResult = $conn->query($allUserRatingsQuery);
        $allRatings = $allUserRatingsResult->fetch_all(MYSQLI_ASSOC);

        // Calculate similarity
        $similarities = calculateSimilarity($userRatings, $allRatings, $userId);

        // Generate recommendations
        $recommendations = [];

        foreach ($similarities as $similarUserId => $similarity) {
            $similarUserRatingsQuery = "SELECT movie_id, rating FROM ratings WHERE user_id = ?";
            $stmt = $conn->prepare($similarUserRatingsQuery);
            $stmt->bind_param("i", $similarUserId);
            $stmt->execute();
            $similarUserRatings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($similarUserRatings as $rating) {
                if (!in_array($rating['movie_id'], array_column($userRatings, 'movie_id'))) {
                    $recommendations[$rating['movie_id']] = isset($recommendations[$rating['movie_id']])
                        ? $recommendations[$rating['movie_id']] + $similarity * $rating['rating']
                        : $similarity * $rating['rating'];
                }
            }
        }

        // Sort recommendations by score
        arsort($recommendations);

        // Fetch movie details for recommendations
        $movieIds = array_keys($recommendations);
        if (!empty($movieIds)) {
            $placeholders = implode(',', array_fill(0, count($movieIds), '?'));
            $movieQuery = "SELECT * FROM movies WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($movieQuery);
            $stmt->bind_param(str_repeat("i", count($movieIds)), ...$movieIds);
            $stmt->execute();
            $recommendedMovies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $recommendedMovies;
    }

    function calculateSimilarity($userRatings, $allRatings, $currentUserId) {
        $similarities = [];
        $userMovieRatings = [];

        // Create a matrix of user ratings
        foreach ($allRatings as $rating) {
            $userMovieRatings[$rating['user_id']][$rating['movie_id']] = $rating['rating'];
        }

        foreach ($userMovieRatings as $otherUserId => $ratings) {
            if ($otherUserId === $currentUserId) continue;

            // Calculate cosine similarity
            $dotProduct = 0;
            $normA = 0;
            $normB = 0;

            foreach ($userRatings as $userRating) {
                if (isset($ratings[$userRating['movie_id']])) {
                    $dotProduct += $userRating['rating'] * $ratings[$userRating['movie_id']];
                }
                $normA += pow($userRating['rating'], 2);
            }

            foreach ($ratings as $rating) {
                $normB += pow($rating, 2);
            }

            if ($normA > 0 && $normB > 0) {
                $similarities[$otherUserId] = $dotProduct / (sqrt($normA) * sqrt($normB));
            }
        }

        // Sort similarities
        arsort($similarities);
        return $similarities;
    }

    // Fetch recommended movies
    $recommendedMovies = fetchRecommendedMovies($conn, $userId);

    // Close the connection
    $conn->close();
} else {
    echo "Database connection failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cineguide - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            position: relative;
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .blur-overlay {
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(4px);
            border-radius: 12px;
        }
        .navbar {
            background: rgba(255, 200, 200, 0.2);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .navbar .navbar-brand img {
            filter: brightness(4) invert(0);
            width: 100px;
            height: 50px;
        }
        .navbar .nav-link {
            color: #fff !important;
            font-weight: bold;
        }
        .navbar .nav-link:hover {
            color: #ffd700 !important;
        }
        .search-bar {
            position: absolute;
            left: 45%;
            transform: translateX(-50%);
            bottom: 20%;
            width: 400px;
        }
        .search-bar input {
            width: 140%;
            padding: 8px 12px;
            border-radius: 25px;
            opacity: 40%;
            border: 0px solid #ddd;
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            height: 90%;
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
        h1, h4 {
            color: white;
        }
        .dropdown-menu a {
            transition: background-color 0.4s ease;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }

        .navbar .dropdown-toggle::after {
            display: none;
        }

        .navbar .nav-item:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu {
            display: none;
            background: rgba(255, 200, 200, 0.5);
            backdrop-filter: blur(30px);
        }


    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php?authenticated=true">
            <img src="logo.png" alt="Logo" class="d-inline-block align-top" width="60" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <form action="" method="POST" class="search-bar">
                        <input type="text" name="search" class="form-control" placeholder="Search movies..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </form>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" href="home.php?authenticated=true">Home</a></li>
                
                <li class="nav-item dropdown category-dropdown">
                    <a class="nav-link" href="#" id="categoryDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Category
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="home.php?category_name=<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item dropdown genre-dropdown">
                    <a class="nav-link" href="#" id="genreDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Genre
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="genreDropdown">
                        <?php foreach ($genres as $genre): ?>
                            <li>
                                <a class="dropdown-item" href="home.php?genre_name=<?php echo htmlspecialchars($genre['name']); ?>">
                                    <?php echo htmlspecialchars($genre['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <h1 class="text-center mb-4">Welcome to Cineguide</h1>

    <h4 class="text-center mb-4">Recommended Movies</h4>
    <div class="row">
        <?php if (!empty($recommendedMovies)): ?>
            <?php foreach ($recommendedMovies as $movie): ?>
                <div class="col-md-4 mb-4">
                    <a href="movie_page.php?movie_id=<?php echo htmlspecialchars($movie['id']); ?>" class="text-decoration-none">
                        <div class="card card-hover blur-overlay">
                            <?php if (!empty($movie['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-image">
                            <?php else: ?>
                                <span>No image</span>
                            <?php endif; ?>
                            <div class="card-body">
                                <h4 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <h4 class="text-center">No recommended movies found.</h4>
        <?php endif; ?>
    </div>

    <h4 class="text-center mb-4">All Movies</h4>
    <div class="row">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="col-md-4 mb-4">
                    <a href="movie_page.php?movie_id=<?php echo htmlspecialchars($movie['id']); ?>" class="text-decoration-none">
                        <div class="card card-hover blur-overlay">
                            <?php if (!empty($movie['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-image">
                            <?php else: ?>
                                <span>No image</span>
                            <?php endif; ?>
                            <div class="card-body">
                                <h4 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h4>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <h4 class="text-center">No movies found.</h4>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap and jQuery scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
