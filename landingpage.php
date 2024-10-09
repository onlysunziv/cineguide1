<?php
include 'includes/db_connect.php';

session_start();

// Initialize variables
$categories = [];
$genres = [];
$movies = [];
$searchTerm = '';
$selectedCategoryName = '';
$selectedGenre = '';

// Fetch genres and categories
if ($conn) {
    // Fetch all genres
    $genreQuery = "SELECT * FROM genres";
    if ($genreResult = $conn->query($genreQuery)) {
        while ($row = $genreResult->fetch_assoc()) {
            $genres[] = $row;
        }
    } else {
        echo "Error fetching genres: " . $conn->error;
    }

    // Fetch all categories
    $categoryQuery = "SELECT * FROM categories";
    if ($categoryResult = $conn->query($categoryQuery)) {
        while ($row = $categoryResult->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        echo "Error fetching categories: " . $conn->error;
    }

    // Check if genre, category, or search is set
    $selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';
    $selectedCategoryName = isset($_GET['category_name']) ? $_GET['category_name'] : '';
    $searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

    // Fetch movies based on selected genre, category name, or search term
    $query = "SELECT * FROM movies";
    $params = [];
    $conditions = [];

    if (!empty($selectedGenre)) {
        $conditions[] = "genre = ?";
        $params[] = $selectedGenre;
    }

    if (!empty($selectedCategoryName)) {
        $conditions[] = "category = ?";
        $params[] = $selectedCategoryName;
    }

    if (!empty($searchTerm)) {
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
        while ($row = $result->fetch_assoc()) {
            $movies[] = $row;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

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

        /* New styles for text color */
        h1, h4 {
            color: white; /* Set color to white for both h1 and h4 elements */
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="landingpage.php">
            <img src="logo.png" alt="Logo" class="d-inline-block align-top" width="80" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <form action="" method="GET" class="search-bar">
                        <input type="text" name="search" class="form-control" placeholder="Search movies..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        
                    </form>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" href="landingpage.php">Home</a></li>
                
                <li class="nav-item dropdown category-dropdown">
                    <a class="nav-link" href="#" id="categoryDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Category
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="landingpage.php?category_name=<?php echo htmlspecialchars($category['name']); ?>">
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
                                <a class="dropdown-item" href="landingpage.php?genre=<?php echo htmlspecialchars($genre['name']); ?>">
                                    <?php echo htmlspecialchars($genre['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content -->
<div class="container mt-5">
    <h1 class="text-center mb-4">Welcome to Cineguide</h1>

    <?php if (!empty($searchTerm)): ?>
        <h4 class="text-center mb-4">Search Results for "<?= htmlspecialchars($searchTerm) ?>"</h4>
    <?php endif; ?>

    <div class="row">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="col-md-4 mb-2">
                    <a href="movie_page2.php?movie_id=<?= $movie['id'] ?>" class="text-decoration-none">
                        <div class="card card-hover blur-overlay">
                            <?php if (!empty($movie['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($movie['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No movies available.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
