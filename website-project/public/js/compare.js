import apiClient from './apiClient.js';
import { getCompareList, removeFromCompare } from './compare-manager.js';
import { formatCurrency, getImageUrl } from './utils.js';

document.addEventListener('DOMContentLoaded', () => {
    loadComparison();
});

async function loadComparison() {
    const container = document.getElementById('compare-content');
    const compareIds = getCompareList();

    if (compareIds.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <h3>No properties selected</h3>
                <p class="text-muted">Browse listings and click "Compare" to add them here.</p>
                <a href="listings.php" class="btn btn-primary">Browse Listings</a>
            </div>
        `;
        return;
    }

    try {
        // Fetch details for all IDs
        const promises = compareIds.map(id => apiClient.request(`/listings/${id}`));
        const listings = await Promise.all(promises);

        renderComparisonTable(listings, container);
    } catch (error) {
        console.error('Error loading comparison:', error);
        container.innerHTML = '<p class="text-danger text-center">Failed to load comparison data.</p>';
    }
}

function renderComparisonTable(listings, container) {
    let html = '<table class="table table-bordered compare-table shadow-sm bg-white">';
    
    // Header Row (Images & Titles)
    html += '<thead><tr><th class="text-center">Property</th>';
    listings.forEach(item => {
        html += `
            <td class="position-relative p-3">
                <button class="btn btn-sm btn-danger rounded-circle remove-btn" data-id="${item.id}" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
                <img src="${getImageUrl(item.images && item.images[0])}" class="compare-img mb-3" alt="${item.title}">
                <h5 class="fw-bold mb-1"><a href="listings.php?id=${item.id}" class="text-decoration-none text-dark">${item.title}</a></h5>
                <span class="badge bg-primary">${item.status}</span>
            </td>
        `;
    });
    html += '</tr></thead><tbody>';

    // Price
    html += '<tr><th>Price</th>';
    listings.forEach(item => html += `<td class="fw-bold text-primary fs-5">${formatCurrency(item.rent_amount)}/mo</td>`);
    html += '</tr>';

    // Location
    html += '<tr><th>Location</th>';
    listings.forEach(item => html += `<td>${item.city}, ${item.neighborhood ? item.neighborhood.name : ''}</td>`);
    html += '</tr>';

    // Type
    html += '<tr><th>Type</th>';
    listings.forEach(item => html += `<td class="text-capitalize">${item.htype.replace('_', ' ')}</td>`);
    html += '</tr>';

    // Deposit
    html += '<tr><th>Deposit</th>';
    listings.forEach(item => html += `<td>${formatCurrency(item.deposit_amount)}</td>`);
    html += '</tr>';

    // Amenities
    html += '<tr><th>Amenities</th>';
    listings.forEach(item => {
        const amenities = item.amenities ? item.amenities.map(a => `<span class="badge bg-light text-dark border me-1 mb-1">${a.name}</span>`).join('') : '-';
        html += `<td>${amenities}</td>`;
    });
    html += '</tr>';

    // Action
    html += '<tr><th>Action</th>';
    listings.forEach(item => html += `<td><a href="listings.php?id=${item.id}" class="btn btn-outline-primary w-100">View Details</a></td>`);
    html += '</tr>';

    html += '</tbody></table>';
    container.innerHTML = html;

    // Attach remove listeners
    container.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            removeFromCompare(btn.dataset.id);
            loadComparison(); // Reload to refresh table
        });
    });
}