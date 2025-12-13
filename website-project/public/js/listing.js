import apiClient from './apiClient.js';

function renderListing(listing) {
    const container = document.getElementById('listingDetails');
    if (!listing) {
        container.innerHTML = '<p class="text-danger">Listing not found.</p>';
        return;
    }

    const imageCarousel = listing.images && listing.images.length ? `
        <div id="listingImageCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                ${listing.images.map((img, index) => `
                    <button type="button" data-bs-target="#listingImageCarousel" data-bs-slide-to="${index}" class="${index === 0 ? 'active' : ''}" aria-current="${index === 0 ? 'true' : 'false'}" aria-label="Slide ${index + 1}"></button>
                `).join('')}
            </div>
            <div class="carousel-inner" style="border-radius: 1rem;">
                ${listing.images.map((img, index) => `
                    <div class="carousel-item ${index === 0 ? 'active' : ''}">
                        <img src="${img.image_path}" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="Listing image ${index + 1}">
                    </div>
                `).join('')}
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#listingImageCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#listingImageCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    ` : `<img src="css/placeholder.jpg" class="d-block w-100" style="height: 450px; object-fit: cover; border-radius: 1rem;" alt="Placeholder image">`;

    container.innerHTML = `
        <div class="mb-4">
            ${imageCarousel}
        </div>
        <h1 class="display-5 fw-bold mb-3">${listing.title}</h1>
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-success me-2">${listing.status}</span>
            <span class="text-muted">${listing.city}</span>
        </div>
        <p class="lead">${listing.description}</p>
        <div class="my-4 p-3 bg-light rounded">
            <div class="row">
                <div class="col-md-4">
                    <p class="h5"><strong>Rent:</strong> KES ${Number(listing.rent_amount).toLocaleString()}/mo</p>
                </div>
                <div class="col-md-4">
                    <p class="h5"><strong>Deposit:</strong> KES ${Number(listing.deposit_amount).toLocaleString()}</p>
                </div>
                 <div class="col-md-4">
                    <p class="h5"><strong>Type:</strong> ${listing.htype.replace('_', ' ')}</p>
                </div>
            </div>
        </div>
    `;
}


document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const listingId = urlParams.get('id');

    if (listingId) {
        try {
            const listing = await apiClient.request(`/listings/${listingId}`);
            renderListing(listing);
        } catch (error) {
            document.getElementById('listingDetails').innerHTML = `<p class="text-danger">Error loading listing: ${error.message}</p>`;
        }
    } else {
        document.getElementById('listingDetails').innerHTML = '<p class="text-warning">No listing ID provided.</p>';
    }

    const reserveBtn = document.getElementById('reserveBtn');
    if (reserveBtn) {
        reserveBtn.addEventListener('click', () => {
            if (listingId) {
                window.location.href = `payment.html?id=${listingId}`;
            }
        });
    }
});