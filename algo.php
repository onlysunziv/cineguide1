<?php
// Includes database connection
include 'includes/db_connect.php';

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$userId = $_SESSION['user_id'];

// Initialize arrays
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

// Function to calculate similarity
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

// Calculate similarities
$similarities = calculateSimilarity($userRatings, $allRatings, $userId);

// Generate recommendations
$recommendations = [];

foreach ($similarities as $similarUserId => $similarity) {
    // Fetch the similar user's ratings
    $similarUserRatingsQuery = "SELECT movie_id, rating FROM ratings WHERE user_id = ?";
    $stmt = $conn->prepare($similarUserRatingsQuery);
    $stmt->bind_param("i", $similarUserId);
    $stmt->execute();
    $similarUserRatings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($similarUserRatings as $rating) {
        // Check if the current user hasn't rated this movie
        if (!in_array($rating['movie_id'], array_column($userRatings, 'movie_id'))) {
            $recommendations[$rating['movie_id']] = isset($recommendations[$rating['movie_id']]) ? $recommendations[$rating['movie_id']] + $similarity * $rating['rating'] : $similarity * $rating['rating'];
        }
    }
}

// Sort recommendations by score
arsort($recommendations);

// Fetch movie details for recommendations
$recommendedMovies = [];
$movieIds = array_keys($recommendations);
if (!empty($movieIds)) {
    $placeholders = implode(',', array_fill(0, count($movieIds), '?'));
    $movieQuery = "SELECT * FROM movies WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($movieQuery);
    $stmt->bind_param(str_repeat("i", count($movieIds)), ...$movieIds);
    $stmt->execute();
    $recommendedMovies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Output recommendations
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Recommendations</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Recommended Movies</h1>
    <div class="row">
        <?php if (!empty($recommendedMovies)): ?>
            <?php foreach ($recommendedMovies as $movie): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($movie['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="card-img-top">
                        <?php else: ?>
                            <span>No image</span>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <h4 class="text-center">No recommendations available.</h4>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
