<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost"; // your database server, typically localhost for XAMPP
$username = "root"; // your database username (default is 'root' for XAMPP)
$password = ""; // your database password (default is empty for XAMPP)
$dbname = "BootcampDec"; // name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch album and artist data using a JOIN
$sql = "
    SELECT 
        albums.id, 
        albums.name AS album_name, 
        albums.image_url AS album_image, 
        artists.name AS artist_name,  -- Referencing 'name' from the artists table
        artists.image_url AS artist_image
    FROM albums
    JOIN artists ON albums.artist_id = artists.id  -- Corrected join condition
";

$result = $conn->query($sql);

// Check if the query is successful
if ($result === false) {
    echo "Error in query: " . $conn->error; // Display query errors if any
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album List</title>
    <link rel="stylesheet" href="albums.css">
</head>
<body>
    <header>
        <h1>My Albums</h1>
    </header>
    <main>
        <section class="album-wrapper">
            <?php
            // Check if there are results
            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    ?>
                    <!-- Make the album container clickable with the <a> tag -->
                    <a href="tracklist.php?id=<?php echo $row['id']; ?>" class="album-link">
                        <div class="album-card-container">
                            <h3 class="album-title"><?php echo htmlspecialchars($row['album_name']); ?></h3>
                            <section class="album-card-wrapper">
                                <article class="card">
                                    <!-- Display album artwork from the database -->
                                    <img src="<?php echo htmlspecialchars($row['album_image']); ?>" alt="Album artwork" class="card-img">
                                    <section class="card-content">
                                        <div class="artist-info">
                                            <!-- Display artist image from the database -->
                                            <img src="<?php echo htmlspecialchars($row['artist_image']); ?>" alt="Artist" class="artist-img">
                                            <div class="album-info">
                                                <!-- Display artist name from the database -->
                                                <p class="artist-name"><?php echo htmlspecialchars($row['artist_name']); ?></p>
                                            </div>
                                        </div>
                                    </section>
                                </article>
                            </section>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "No albums found.";
            }
            ?>
        </section>
    </main>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>
