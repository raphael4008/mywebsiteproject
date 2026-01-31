<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Properties – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="navbar-container"></div>

    <header class="bg-primary text-white py-5 text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/d.jpg') center/cover;">
        <div class="container">
            <h1 class="display-4 fw-bold">Compare Properties</h1>
            <p class="lead">View your selected properties side-by-side.</p>
        </div>
    </header>

    <main class="container py-5">
        <div id="compare-container" class="row">
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
        import { getCompareList, removeFromCompare } from './js/compare-manager.js';
        import { showNotification, formatCurrency } from './js/utils.js';

        const compareContainer = document.getElementById('compare-container');

        async function loadComparison() {
            const ids = getCompareList();
            if (ids.length === 0) {
                compareContainer.innerHTML = '<p class="text-center w-100">You have not selected any properties to compare. <a href="listings.php">Browse listings</a>.</p>';
                return;
            }

            try {
                const listings = await Promise.all(
                    ids.map(id => apiClient.request(`/listings/${id}`))
                );
                renderComparison(listings);
            } catch (error) {
                showNotification('Could not load comparison data.', 'error');
                compareContainer.innerHTML = '<p class="text-danger">Could not load comparison data.</p>';
            }
        }

        function renderComparison(listings) {
            compareContainer.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Feature</th>
                                ${listings.map(l => `<th scope="col">${l.title}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">Image</th>
                                ${listings.map(l => `<td><img src="${(l.images && l.images.length) ? l.images[0].path : 'images/placeholder.svg'}" class="img-fluid"></td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">Rent</th>
                                ${listings.map(l => `<td>${formatCurrency(l.rent_amount)}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">Deposit</th>
                                ${listings.map(l => `<td>${formatCurrency(l.deposit_amount)}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">Type</th>
                                ${listings.map(l => `<td>${l.htype}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">Style</th>
                                ${listings.map(l => `<td>${l.style}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">City</th>
                                ${listings.map(l => `<td>${l.city}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">Neighborhood</th>
                                ${listings.map(l => `<td>${l.neighborhood ? l.neighborhood.name : 'N/A'}</td>`).join('')}
                            </tr>
                             <tr>
                                <th scope="row">Furnished</th>
                                ${listings.map(l => `<td>${l.furnished ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row">Verified</th>
                                ${listings.map(l => `<td>${l.verified ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'}</td>`).join('')}
                            </tr>
                            <tr>
                                <th scope="row"></th>
                                ${listings.map(l => `<td><button class="btn btn-sm btn-outline-danger remove-btn" data-id="${l.id}">Remove</button></td>`).join('')}
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

            compareContainer.querySelectorAll('.remove-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    const id = e.target.dataset.id;
                    removeFromCompare(id);
                    loadComparison();
                    showNotification('Property removed from comparison.', 'info');
                });
            });
        }

        loadComparison();
    </script>
</body>
</html>
