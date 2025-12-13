import apiClient from './apiClient.js';

async function renderFeaturedHomes(filters = {}) {
    const grid = document.getElementById('featuredGrid');
    if (!grid) return;
    grid.innerHTML = '';

    try {
        const queryParams = new URLSearchParams(filters).toString();
        const featuredHomes = await apiClient.request(`/listings?limit=4&${queryParams}`);

        if (!featuredHomes.data || featuredHomes.data.length === 0) {
            grid.innerHTML = '<p>No featured homes available at the moment.</p>';
            return;
        }

        grid.innerHTML = featuredHomes.data.map(item => `
            <div class="listing-card fade-in">
                <img src="${item.images[0]}" alt="${item.title}">
                <div class="listing-card-content">
                    <h3>${item.title}</h3>
                    <p>${item.city} • ${item.neighborhood}</p>
                    <p><b>KES ${item.rent_amount.toLocaleString()}</b> / month</p>
                    <div class="badges mt-3">
                        <span class="badge bg-secondary">${item.htype.replace('_', ' ')}</span>
                        ${item.furnished ? '<span class="badge bg-info">Furnished</span>' : ''}
                        ${item.verified ? '<span class="badge bg-success">Verified</span>' : ''}
                    </div>
                   <a href="listing.html?id=${item.id}" class="btn btn-primary mt-3 w-100">View Details</a>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error fetching featured homes:', error);
        grid.innerHTML = '<p>Could not load featured homes. Please try again later.</p>';
    }
}

function setupFilters() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const formData = new FormData(filterForm);
        const filters = Object.fromEntries(formData.entries());

        renderFeaturedHomes(filters);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderFeaturedHomes();
    setupFilters();
});
