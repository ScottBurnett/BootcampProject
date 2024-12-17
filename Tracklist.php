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
        artists.id AS artist_id
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

// Prepare and execute query to fetch tracklist details for the album, including YouTube URL
$trackSql = "
    SELECT title AS track_title, track_number, duration AS track_duration, youtube_url 
    FROM songs 
    WHERE album_id = ? 
    ORDER BY track_number
";
$stmt = $conn->prepare($trackSql);
$stmt->bind_param("i", $album_id); // Bind the album_id as an integer
$stmt->execute();
$trackResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album['album_name']); ?> - Tracklist</title>
    <link rel="stylesheet" href="Tracklist.css">
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
                    <img src="<?php echo htmlspecialchars($album['album_image']); ?>" alt="Album artwork" class="card-img">
                    <section class="card-content">
                        <div class="artist-info">
                            <img src="<?php echo htmlspecialchars($album['artist_image']); ?>" alt="Artist" class="artist-img">
                            <div class="album-info">
                                <!-- Make artist name clickable to go to artist's page -->
                                <a href="artist.php?id=<?php echo htmlspecialchars($album['artist_id']); ?>" class="artist-name"><?php echo htmlspecialchars($album['artist_name']); ?></a>
                            </div>
                        </div>
                    </section>
                </article>
            </section>
        </div>

        <!-- Tracklist -->
        <section class="tracklist-wrapper">
            <header class="album-header">
                <h1><?php echo htmlspecialchars($album['album_name']); ?></h1>
                <p>By <a href="artist.php?id=<?php echo htmlspecialchars($album['artist_id']); ?>" class="artist-name"><?php echo htmlspecialchars($album['artist_name']); ?></a></p>
            </header>
            <ol class="tracklist">
                <?php 
                if ($trackResult->num_rows > 0) {
                    while ($track = $trackResult->fetch_assoc()) {
                        echo '<li class="track">';
                        echo '<div class="track-info">';
                        echo '<span class="track-number">' . htmlspecialchars($track['track_number']) . '</span>';
                        echo '<span class="track-title">' . htmlspecialchars($track['track_title']) . '</span>';
                        echo '</div>';
                        echo '<div class="track-duration-play-wrapper">';
                        echo '<span class="track-duration">' . htmlspecialchars($track['track_duration']) . '</span>';

                        // If a YouTube URL is available, make the play button a link to the YouTube video
                        if (!empty($track['youtube_url'])) {
                            echo '<a href="' . htmlspecialchars($track['youtube_url']) . '" target="_blank" class="play-button">▶</a>';
                        } else {
                            echo '<button class="play-button">▶</button>';
                        }

                        echo '</div>';
                        echo '</li>';
                    }
                } else {
                    echo "<li>No tracks available for this album.</li>";
                }
                ?>
            </ol>
        </section>
    </section>
</main>

</body>
</html>

<?php
$conn->close();
?>
