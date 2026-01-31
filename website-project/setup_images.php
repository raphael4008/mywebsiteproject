<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\DatabaseConnection;

// This script populates the 'images' table using files from the 'uploads' folder.
// It ensures your project loads actual image data from the database.

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (\Throwable $e) {
    die("Database connection failed: " . $e->getMessage());
}

echo "1. Checking 'images' table...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
)");

echo "2. Checking 'assets' folder (for fallbacks)...\n";
$assetsDir = __DIR__ . '/assets';
if (!is_dir($assetsDir)) {
    mkdir($assetsDir, 0777, true);
    echo "   Created 'assets' directory.\n";
}

// Create placeholder images if they don't exist (requires GD extension, or simply skips)
$placeholders = ['default.jpg', 'default-thumb.jpg'];
foreach ($placeholders as $file) {
    if (!file_exists($assetsDir . '/' . $file) && extension_loaded('gd')) {
        $im = imagecreatetruecolor(400, 300);
        $bg = imagecolorallocate($im, 220, 220, 220);
        $text = imagecolorallocate($im, 100, 100, 100);
        imagefilledrectangle($im, 0, 0, 400, 300, $bg);
        imagestring($im, 5, 150, 140, "No Image", $text);
        imagejpeg($im, $assetsDir . '/' . $file);
        imagedestroy($im);
        echo "   Generated placeholder: $file\n";
    }
}

echo "3. Scanning 'uploads' folder...\n";
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo "   Created 'uploads' directory. Please put your images there!\n";
}

$files = scandir($uploadDir);
$imageFiles = [];
foreach ($files as $file) {
    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'])) {
        $imageFiles[] = $file;
    }
}

if (empty($imageFiles)) {
    die("   No images found in '$uploadDir'. Please add some images and run this script again.\n");
}
echo "   Found " . count($imageFiles) . " images.\n";

echo "4. Fetching listings...\n";
$stmt = $pdo->query("SELECT id FROM listings");
$listingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($listingIds)) {
    die("   No listings found in database. Please create some listings first.\n");
}

echo "5. Assigning images to listings...\n";
$pdo->exec("TRUNCATE TABLE images"); // Clear old data for a fresh start

$insertStmt = $pdo->prepare("INSERT INTO images (listing_id, image_path) VALUES (?, ?)");

foreach ($listingIds as $id) {
    // Assign 2 to 5 random images to each listing
    $count = rand(2, 5);
    for ($i = 0; $i < $count; $i++) {
        $randomImage = $imageFiles[array_rand($imageFiles)];
        $insertStmt->execute([$id, $randomImage]);
    }
}

echo "Success! Images have been assigned to your listings.\n";