<?php
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "BootcampDec"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if album ID is passed correctly in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $album_id = intval($_GET['id']); // Sanitize the input
} else {
    echo "No valid album ID provided.";
    exit;
}

// Prepare and execute query to fetch album details
$albumSql = "
    SELECT 
        albums.id AS album_id, 
        albums.name AS album_name, 
        albums.image_url AS album_image, 
        artists.name AS artist_name,  
        artists.image_url AS artist_image, 
        artists.id AS artist_id,
        artists.bio AS artist_bio  -- Include bio field
    FROM albums
    JOIN artists ON albums.artist_id = artists.id
    WHERE albums.id = ?
";
$stmt = $conn->prepare($albumSql);
$stmt->bind_param("i", $album_id); // Bind the album_id as an integer
$stmt->execute();
$albumResult = $stmt->get_result();

// Check if album details were found
if ($albumResult->num_rows > 0) {
    $album = $albumResult->fetch_assoc();
} else {
    echo "No album found with ID $album_id.";
    exit;
}

// Prepare and execute query to fetch other albums from the same artist
$artistAlbumsSql = "
    SELECT id, name, image_url
    FROM albums
    WHERE artist_id = ?
    AND id != ?  -- Exclude the current album
";
$stmt = $conn->prepare($artistAlbumsSql);
$stmt->bind_param("ii", $album['artist_id'], $album_id); // Bind artist_id and exclude the current album_id
$stmt->execute();
$artistAlbumsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album['album_name']); ?> - Tracklist</title>
    <link rel="stylesheet" href="Artist.css">
</head>
<body>
<header>
<nav>
    <a href="index.php">
        <button>Back</button>
    </a>
</nav>
</header>

<main>
    <section class="album-wrapper">
        <!-- Album Card -->
        <div class="album-card-container">
            <section class="album-card-wrapper">
                <article class="card">
                    <!-- Artist Image instead of Album Image -->
                    <img src="<?php echo htmlspecialchars($album['artist_image']); ?>" alt="Artist artwork" class="card-img">
                    
                    <section class="card-content">
                        <div class="artist-info">
                            <div class="album-info">
                                <!-- Display the artist's bio -->
                                <p class="artist-bio"><?php echo nl2br(htmlspecialchars($album['artist_bio'])); ?></p>
                            </div>
                        </div>
                    </section>
                </article>
            </section>
        </div>

        <!-- Grid of Other Albums by the Artist -->
        <section class="artist-albums-wrapper">
            <h2>Other Albums by <?php echo htmlspecialchars($album['artist_name']); ?></h2>
            <div class="album-grid">
                <?php 
                if ($artistAlbumsResult->num_rows > 0) {
                    while ($artistAlbum = $artistAlbumsResult->fetch_assoc()) {
                        echo '<div class="album-card">';
                        echo '<img src="' . htmlspecialchars($artistAlbum['image_url']) . '" alt="Album artwork" class="album-card-img">';
                        echo '<h3>' . htmlspecialchars($artistAlbum['name']) . '</h3>';
                        // Update link to tracklist.php
                        echo '<a href="tracklist.php?id=' . htmlspecialchars($artistAlbum['id']) . '" class="view-album-link">View Album</a>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No other albums by this artist.</p>";
                }
                ?>
            </div>
        </section>
    </section>
</main>

</body>
</html>

<?php
$conn->close();
?>
    