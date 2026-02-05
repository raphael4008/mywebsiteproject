import apiClient from './apiClient.js';

function createListingCard(listing) {
    const col = document.createElement('div');
    col.className = 'col-md-4';

    const card = document.createElement('div');
    card.className = 'card h-100 shadow-sm listing-card';

    const imageUrl = (listing.images && listing.images.length > 0) ? `${window.basePath}/${listing.images[0].path}` : `${window.basePath}/images/placeholder.jpg`;
    const price = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(listing.price);

    card.innerHTML = `
        <a href="/listing/${listing.id}" class="text-decoration-none text-dark">
            <img src="${imageUrl}" class="card-img-top" alt="${listing.title}" style="height: 200px; object-fit: cover;">
            <div class="card-body">
                <h5 class="card-title">${listing.title}</h5>
                <p class="card-text text-muted">${listing.city}, ${listing.neighborhood}</p>
                <p class="card-text fs-5 fw-bold">${price}</p>
                <div class="d-flex justify-content-between text-muted small">
                    <span><i class="fas fa-bed"></i> ${listing.bedrooms} Beds</span>
                    <span><i class="fas fa-bath"></i> ${listing.bathrooms} Baths</span>
                </div>
            </div>
        </a>
    `;

    col.appendChild(card);
    return col;
}

async function fetchFeaturedListings() {
    const featuredPropertiesContainer = document.getElementById('featured-properties-container');
    if (!featuredPropertiesContainer) return;

    featuredPropertiesContainer.innerHTML = '<div class="col text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading featured properties...</p></div>';

    try {
        const response = await apiClient.request('listings/featured');
        const listings = response.data;

        featuredPropertiesContainer.innerHTML = '';

        if (!listings || listings.length === 0) {
            featuredPropertiesContainer.innerHTML = '<div class="col text-center"><p>No featured properties available at the moment. Please check back later.</p></div>';
            return;
        }

        listings.forEach(listing => {
            const card = createListingCard(listing);
            featuredPropertiesContainer.appendChild(card);
        });

    } catch (error) {
        console.error('Error fetching featured listings:', error);
        featuredPropertiesContainer.innerHTML = '<div class="col text-center"><p class="text-danger">Sorry, we were unable to load featured properties. Please try again later.</p></div>';
    }
}

export function initHome() {
    // AI Search Bar Logic
    const aiSearchInput = document.getElementById('ai-search-input');
    const aiSearchBtn = document.getElementById('ai-search-btn');

    if (aiSearchBtn) {
        aiSearchBtn.addEventListener('click', () => {
            const query = aiSearchInput.value;
            if (query) {
                window.location.href = `listings.php?ai_query=${encodeURIComponent(query)}`;
            }
        });
    }

    // Standard Search Logic
    const standardSearchForm = document.getElementById('standard-search-form');
    if (standardSearchForm) {
        standardSearchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(standardSearchForm);
            const params = new URLSearchParams();

            const city = formData.get('city');
            const maxRent = formData.get('maxRent');

            if (city) params.append('city', city);
            if (maxRent) params.append('maxRent', maxRent);

            window.location.href = `listings.php?${params.toString()}`;
        });
    }
    
    fetchFeaturedListings();
}

document.addEventListener('DOMContentLoaded', () => {
    initHome();
});