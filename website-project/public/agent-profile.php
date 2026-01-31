<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Profile – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <div id="navbar-container"></div>

    <main class="container py-5">
        <div id="agent-profile-content" class="row">
            <!-- Agent profile will be rendered here -->
        </div>
        <hr class="my-5">
        <h2 class="fw-bold mb-4">Listings by this Agent</h2>
        <div id="agent-listings-container" class="row">
            <!-- Agent's listings will be rendered here -->
        </div>
    </main>

    <div id="footer-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="js/main.js" type="module"></script>
    <script src="js/auth.js" type="module"></script>
    <script src="js/agent.js" type="module"></script>
</body>
</html>
