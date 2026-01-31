<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Models/Listing.php';
require_once __DIR__ . '/../src/Models/Image.php'; 
require_once __DIR__ . '/../src/Config/DatabaseConnection.php';

use App\Models\Listing;

$listingId = $_GET['id'] ?? null;
$listing = null;

if ($listingId) {
    try {
        \App\Config\DatabaseConnection::getInstance()->getConnection();
        $listing = Listing::findByIdWithDetails((int)$listingId);
    } catch (Exception $e) {
        // In a real app, you'd log this error.
        // For now, we'll just let it proceed to the not found state.
    }
}

if (!$listing) {
    http_response_code(404);
    // You can render a pretty 404 page here
    echo "Listing not found.";
    exit;
}

$pageTitle = htmlspecialchars($listing['title']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.1/css/lightgallery.min.css" integrity="sha512-F2E+YYE1gkt0T5TVajAslgDfTEUQKtlu4ralVqIJzUEXlMszJasgxIgpGZQagGTgpOFKzDRmikHQRKoCsHWQEA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/custom.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div id="navbar-container"></div>

    <main class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div id="listing-details-content">
                    <!-- Listing details will be rendered here by JavaScript -->
                </div>
                <hr class="my-5">
                <div id="amenities-section">
                    <h3 class="fw-bold mb-4">Amenities</h3>
                    <div id="amenities-list" class="row g-3"></div>
                </div>
                <hr class="my-5">
                <div id="map-section">
                    <h3 class="fw-bold mb-4">Location</h3>
                    <div id="map" style="height: 400px; border-radius: 1rem;"></div>
                </div>
                 <hr class="my-5">
                <div id="video-section">
                    <h3 class="fw-bold mb-4">Videos</h3>
                    <div id="video-list" class="row g-4"></div>
                </div>
                 <hr class="my-5">
                 <div id="neighborhood-section">
                    <h3 class="fw-bold mb-4">About the Neighborhood</h3>
                    <div id="neighborhood-details"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 2rem;">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Contact Agent</h5>
                            <div id="agent-info" class="text-center mt-3">
                                <!-- Agent info rendered here -->
                            </div>
                            <form id="contactAgentForm" class="mt-4">
                                <div class="mb-3">
                                    <label for="contactName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="contactName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="contactEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Message</label>
                                    <textarea class="form-control" id="contactMessage" rows="4" required>I'm interested in this listing.</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="similar-listings-container" class="mt-5">
            <!-- Similar listings will be loaded here -->
        </div>
    </main>

    <div id="footer-container"></div>
    <button id="backToTopBtn" class="back-to-top" title="Go to top">↑</button>
    
    <script>
        const initialListingData = <?php echo json_encode($listing); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.1/lightgallery.min.js" integrity="sha512-dGnPryMeOe8KFKPUplGaQJTxVoHMIbWaoBrpYe2AZynkH33roUow1zgubKavLaojeTlofs4HgdeZdcux+Vbb/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="js/main.js?v=4" type="module"></script>
    <script src="js/auth.js?v=4" type="module"></script>
    <script src="js/listing.js?v=4" type="module"></script>
</body>
</html>