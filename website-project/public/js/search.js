import apiClient from './apiClient.js';

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

const apiEndpoint = '/api/listings';
let map;
let markers = [];

function renderResults(listings) {
    const container = document.getElementById('results');
    if (!listings || !listings.length) {
        container.innerHTML = '<p class="text-gray-500">No listings found. Try different filters!</p>';
        return;
    }

    const html = listings.map(item => {
        const images = item.images && item.images.length ? item.images : [{ image_path: 'css/placeholder.jpg' }];
        const amenities = item.amenities && item.amenities.length ? item.amenities.map(amenity => `<li>${amenity.name}</li>`).join('') : '<li>No amenities listed</li>';
        const reviews = item.reviews && item.reviews.length ? item.reviews.map(review => `<div class="review">...</div>`).join('') : '<p>No reviews yet</p>';

        return `
            <div class="col-md-4 mb-4" data-id="${item.id}">
                <div class="card h-100">
                    <div id="carousel-${item.id}" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            ${images.map((image, index) => `
                                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                    <img src="${image.image_path}" class="d-block w-100" alt="...">
                                </div>
                            `).join('')}
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${item.id}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-${item.id}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${item.title}</h5>
                        <p class="card-text">${item.city} • ${item.neighborhood ? item.neighborhood.name : ''}</p>
                        <p class="card-text">KES ${Number(item.rent_amount).toLocaleString()} / month</p>
                        <h6>Amenities</h6>
                        <ul>${amenities}</ul>
                        <h6>Reviews</h6>
                        ${reviews}
                        <a href="listing.html?id=${item.id}" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;

    addMarkersToMap(listings);
}

async function fetchListings(params) {
    return apiClient.request(`/listings/search?${new URLSearchParams(params)}`);
}

function showLoading(on) {
    const container = document.getElementById('results');
    if (!container) return;
    if (on) {
        container.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
    } else {
        // The container will be overwritten by renderResults, so no need to remove skeletons manually.
    }
}

async function handleSearch() {
    const form = document.getElementById('searchForm');
    const params = {};
    if (form.ai_query && form.ai_query.value.trim() !== '') {
        params.ai_query = form.ai_query.value.trim();
    }

    if (map && document.getElementById('searchOnMapMove').checked) {
        const bounds = map.getBounds();
        params.sw_lat = bounds.getSouthWest().lat;
        params.sw_lng = bounds.getSouthWest().lng;
        params.ne_lat = bounds.getNorthEast().lat;
        params.ne_lng = bounds.getNorthEast().lng;
    }

    try {
        showLoading(true);
        const payload = await fetchListings(params);
        const data = payload.data || [];
        renderResults(data);
    } catch (err) {
        const container = document.getElementById('results');
        container.innerHTML = `<p class="text-red-500">Failed to fetch results: ${err.message}. Try again later.</p>`;
        console.error(err);
    } finally {
        showLoading(false);
    }
}

function initMap() {
    if (document.getElementById('map')) {
        map = L.map('map').setView([-1.286389, 36.817223], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        map.on('moveend', function() {
            if (document.getElementById('searchOnMapMove').checked) {
                handleSearch();
            }
        });
    }
}

function addMarkersToMap(listings) {
    if (!map) return;
    markers.forEach(marker => marker.remove());
    markers = [];

    listings.forEach(item => {
        if (item.latitude && item.longitude) {
            const marker = L.marker([item.latitude, item.longitude]).addTo(map)
                .bindPopup(`<b>${item.title}</b><br>${item.city} • ${item.neighborhood ? item.neighborhood.name : ''}`);
            
            marker.on('click', () => {
                window.open(`listing.html?id=${item.id}`, '_blank');
            });

            markers.push(marker);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleSearch();
        });
    }

    initMap();
    handleSearch();
});