<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neighborhoods – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="navbar-container"></div>

    <header class="bg-primary text-white py-5 text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/f.jpeg') center/cover;">
        <div class="container">
            <h1 class="display-4 fw-bold">Explore Our Neighborhoods</h1>
            <p class="lead">Find the perfect community to call home.</p>
        </div>
    </header>

    <main class="container py-5">
        <div id="neighborhoods-container" class="row">
            <div class="text-center w-100">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script type="module">
        import apiClient from './js/apiClient.js';
        import { showNotification } from './js/utils.js';

        const container = document.getElementById('neighborhoods-container');

        async function loadNeighborhoods() {
            try {
                const neighborhoods = await apiClient.request('/neighborhoods');
                renderNeighborhoods(neighborhoods);
            } catch (error) {
                showNotification('Could not load neighborhoods.', 'error');
                container.innerHTML = '<p class="text-danger">Could not load neighborhoods.</p>';
            }
        }

        function renderNeighborhoods(neighborhoods) {
            if (!neighborhoods || neighborhoods.length === 0) {
                container.innerHTML = '<p class="text-center">No neighborhoods found.</p>';
                return;
            }

            container.innerHTML = neighborhoods.map(n => `
                <div class="col-md-4 mb-4">
                    <a href="listings.php?neighborhood=${encodeURIComponent(n.name)}" class="card h-100 text-decoration-none text-dark border-0 shadow-sm">
                        <img src="${n.image_url || 'images/placeholder.svg'}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${n.name}">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">${n.name}</h5>
                            <p class="card-text text-muted">${n.city}, ${n.state}</p>
                        </div>
                    </a>
                </div>
            `).join('');
        }

        loadNeighborhoods();
    </script>
</body>
</html>
