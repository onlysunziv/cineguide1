<?php
session_start();
include 'includes/db_connect.php';

// Initialize variables
$movie = null;
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null; // Fetch user ID from session
$show_modal = false; // Flag to show the modal

// Fetch movie details based on the provided movie ID
if (isset($_GET['movie_id'])) {
    $movie_id = intval($_GET['movie_id']);
    
    // Fetch the movie details
    $query = "SELECT * FROM movies WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $movie_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $movie = mysqli_fetch_assoc($result);
    } else {
        die("Movie not found.");
    }

    // Handle rating form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rating = intval($_POST['rating']);
        
        // Validate rating
        if ($rating >= 1 && $rating <= 5) {
            // Check if the user has already rated this movie
            $check_query = "SELECT * FROM ratings WHERE user_id = ? AND movie_id = ?";
            $check_stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $movie_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($check_result) > 0) {
                // Set flag to show the modal
                $show_modal = true;
            } else {
                // Insert the new rating
                $rating_query = "INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)";
                $rating_stmt = mysqli_prepare($conn, $rating_query);
                mysqli_stmt_bind_param($rating_stmt, "iii", $user_id, $movie_id, $rating);

                if (mysqli_stmt_execute($rating_stmt)) {
                    // Rating submitted successfully
                } else {
                    echo "<div class='alert alert-danger'>Failed to submit rating. Please try again.</div>";
                }
                mysqli_stmt_close($rating_stmt);
            }
            mysqli_stmt_close($check_stmt);
        } else {
            echo "<div class='alert alert-warning'>Please provide a rating between 1 and 5.</div>";
        }
    }

    // Fetch average rating
    $average_query = "SELECT AVG(rating) AS average_rating FROM ratings WHERE movie_id = ?";
    $average_stmt = mysqli_prepare($conn, $average_query);
    mysqli_stmt_bind_param($average_stmt, "i", $movie_id);
    mysqli_stmt_execute($average_stmt);
    $average_result = mysqli_stmt_get_result($average_stmt);
    $average_rating = mysqli_fetch_assoc($average_result)['average_rating'];
    mysqli_stmt_close($average_stmt);
} else {
    die("No movie ID provided.");
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['title']); ?> - Cineguide</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('back.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .blur-overlay {
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(4px);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .movie-poster {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
        }

        .movie-details {
            margin-top: 20px;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 200, 200, 0.2);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar .navbar-brand img {
            filter: brightness(4) invert(0);
            width: 60px;
            height: 50px;
        }

        .navbar .nav-link {
            color: #fff !important;
            font-weight: bold;
        }

        .navbar .nav-link:hover {
            color: #ffd700 !important;
        }

        .dropdown-menu {
            background: rgba(255, 200, 200, 0.5);
            backdrop-filter: blur(30px);
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

        /* Custom Modal Styles */
        .modal-dialog {
            position: fixed;
            top: 35%;
            left: 35%;
            transform: translate(-50%, -50%);
            margin: 0;
            width: 35%;
        }

        .modal-content {
            border-radius: 9px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php?authenticated=true">
                <img src="logo.png" alt="Logo" class="d-inline-block align-top">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                   
                </ul>
                <ul class="navbar-nav">
                    <!-- Navigation Items -->
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="home.php?authenticated=true">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            Category
                        </a>
                        <div class="dropdown-menu" aria-labelledby="categoryDropdown">
                            <?php foreach ($categories as $category): ?>
                                <a class="dropdown-item" href="category.php?category=<?php echo urlencode($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container">
        <div class="blur-overlay">
            <div class="row">
                <div class="col-md-4">
                    <img src="uploads/<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                </div>
                <div class="col-md-8 movie-details">
                    <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
                    <p><strong>Release Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($movie['release_date']))); ?></p>
                    <p><strong>Cast:</strong> <?php echo htmlspecialchars($movie['cast']); ?></p>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                    
                    <!-- Trailer Link -->
                    <?php if (!empty($movie['trailer'])): ?>
                        <p><strong></strong> <a href="<?php echo htmlspecialchars($movie['trailer']); ?>" target="_blank" class="btn btn-primary">Watch Trailer</a></p>
                    <?php else: ?>
                        <p><strong>Trailer:</strong> Not available.</p>
                    <?php endif; ?>
                    
                    <!-- Average Rating -->
                    <p><strong>Average Rating:</strong> <?php echo number_format($average_rating, 1); ?> / 5</p>
                    
                    <!-- Rating Form -->
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="rating">Rate this movie:</label>
                            <select id="rating" name="rating" class="form-control">
                                <option value="">Select rating</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Rating</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for existing rating -->
    <?php if ($show_modal): ?>
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Rating Already Submitted</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>You have already rated this movie.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            if (<?php echo $show_modal ? 'true' : 'false'; ?>) {
                $('.modal').modal('show');
            }
        });
    </script>
</body>
</html>
