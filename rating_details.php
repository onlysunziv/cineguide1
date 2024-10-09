<?php
include 'includes/db_connect.php';
include 'admin.php';

// Fetch rating details
$query = "SELECT r.user_id, r.movie_id, m.title AS movie_title, r.rating, r.rating_date 
          FROM ratings r
          JOIN movies m ON r.movie_id = m.id
          ORDER BY r.rating_date DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating Details - Cineguide</title>
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
        }
        
        th {
            background-color: rgba(0, 123, 255, 0.9); /* Semi-transparent bootstrap primary color */
            color: white;
        }
        tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.2); /* Light hover effect */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Rating Details</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Movie ID</th>
                    <th>Movie Title</th>
                    <th>Rating</th>
                    <th>Rating Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['movie_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['movie_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['rating']); ?></td>
                        <td><?php echo htmlspecialchars(date('F j, Y', strtotime($row['rating_date']))); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No ratings found.</div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
