import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    loadUserProfile();
    loadSavedSearches();
    loadReservedListings();
});

async function loadUserProfile() {
    const userNameEl = document.getElementById('userName');
    const userEmailEl = document.getElementById('userEmail');

    if (!userNameEl || !userEmailEl) return;

    try {
        const user = await apiClient.request('/users/me');
        userNameEl.textContent = user.name;
        userEmailEl.textContent = user.email;
    } catch (error) {
        console.error('Failed to load user profile:', error);
        document.getElementById('userInfo').innerHTML = `<p class="text-danger">Could not load profile information.</p>`;
    }
}

async function loadSavedSearches() {
    const container = document.getElementById('savedSearchesList');
    if (!container) return;
    container.innerHTML = '<p>Loading saved searches...</p>';

    try {
        const searches = await apiClient.request('/users/me/searches');
        if (!searches || searches.length === 0) {
            container.innerHTML = '<p>You have no saved searches.</p>';
            return;
        }

        container.innerHTML = searches.map(search => {
            const params = new URLSearchParams(search.criteria).toString();
            const criteriaText = Object.entries(search.criteria).map(([key, value]) => `${key}: ${value}`).join(', ');
            return `
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span>${criteriaText}</span>
                        <a href="/search.html?${params}" class="btn btn-sm btn-outline-primary">Run Search</a>
                    </div>
                </div>
            `;
        }).join('');

    } catch (error) {
        console.error('Failed to load saved searches:', error);
        container.innerHTML = `<p class="text-danger">Could not load saved searches.</p>`;
    }
}

async function loadReservedListings() {
    const container = document.getElementById('reservedListingsList');
    if (!container) return;
    container.innerHTML = '<p>Loading reserved listings...</p>';
    
    try {
        const reservations = await apiClient.request('/users/me/reservations');
        if (!reservations || reservations.length === 0) {
            container.innerHTML = '<p>You have no reserved listings.</p>';
            return;
        }

        container.innerHTML = reservations.map(res => {
            const listing = res.listing; // Assuming the backend returns the nested listing object
            const imageUrl = listing.images && listing.images.length > 0 ? listing.images[0] : 'css/placeholder.jpg';
            return `
                <div class="card mb-3">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="${imageUrl}" class="img-fluid rounded-start" alt="${listing.title}" style="height: 100%; object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <h5 class="card-title">${listing.title}</h5>
                                <p class="card-text"><small class="text-muted">Reserved on: ${new Date(res.created_at).toLocaleDateString()}</small></p>
                                <a href="/listing.html?id=${listing.id}" class="btn btn-sm btn-primary">View Listing</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Failed to load reserved listings:', error);
        container.innerHTML = `<p class="text-danger">Could not load reserved listings.</p>`;
    }
}