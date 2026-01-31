<?php
// This file now acts as a simple HTML host for our JavaScript-driven listings page.
// All dynamic content is loaded via the API.
require_once __DIR__ . '/../src/Config/Config.php';

// The page title can still be set via PHP
$pageTitle = 'Explore All Listings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - HouseHunting</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Include general styles and the new grid styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/listings-grid.css">
</head>
<body>

    <div id="navbar-container"></div>

    <main id="all-listings-container">
        <!-- The JavaScript will populate this grid -->
        <div id="listings-grid" class="grid-container">
            <!-- Skeleton loader will be rendered here initially by the JS -->
        </div>
    </main>
    
    <div id="footer-container"></div>

    <button id="backToTopBtn" class="back-to-top" title="Go to top">↑</button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js?v=4" type="module"></script>
    <script src="js/listing.js?v=4" type="module"></script>

</body>
</html>
