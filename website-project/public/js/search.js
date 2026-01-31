import apiClient from './apiClient.js';
import { formatCurrency, getImageUrl } from './utils.js';
import { toggleFavorite, getFavoriteButtonHtml } from './wishlist.js';

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

let map;
let markers = [];

function renderResults(listings) {
    const container = document.getElementById('results');
    if (!listings || !listings.length) {
        container.innerHTML = '<p class="text-gray-500">No listings found. Try different filters!</p>';
        return;
    }

    const html = listings.map(item => {
        const images = item.images && item.images.length ? item.images : ['css/placeholder.jpg'];
        // const amenities = item.amenities && item.amenities.length ? item.amenities.map(amenity => `<li>${amenity.name}</li>`).join('') : '<li>No amenities listed</li>';
        // const reviews = item.reviews && item.reviews.length ? item.reviews.map(review => `<div class="review">...</div>`).join('') : '<p>No reviews yet</p>';

        return `
            <div class="col-md-4 mb-4" data-id="${item.id}" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm listing-card">
                    <div id="carousel-${item.id}" class="carousel slide position-relative" data-bs-ride="carousel">
                        ${getFavoriteButtonHtml(item.id, item.is_favorite)}
                        <div class="carousel-inner">
                            ${images.map((image, index) => `
                                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                    <img src="${getImageUrl(image)}" class="d-block w-100" alt="${item.title}" style="height: 250px; object-fit: cover;">
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
                        <div class="position-absolute top-0 end-0 m-3 z-index-10">
                             ${item.verified ? '<span class="badge bg-success me-1"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                            <span class="badge bg-primary">${item.status}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-truncate">${item.title}</h5>
                        <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i> ${item.city} ${item.neighborhood ? '• ' + item.neighborhood.name : ''}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 text-primary mb-0 fw-bold">${formatCurrency(item.rent_amount)}</span>
                            <a href="listings.php?id=${item.id}" class="btn btn-outline-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;

    // Attach event listeners for favorite buttons
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleFavorite(btn, btn.dataset.id);
        });
    });

    addMarkersToMap(listings);
}

async function fetchListings(params) {
    return apiClient.request(`/listings?${new URLSearchParams(params)}`); // Assuming standard endpoint supports filters
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
    
    // AI / Natural Language Processing Logic
    if (form.ai_query && form.ai_query.value.trim() !== '') {
        const aiParams = parseAIQuery(form.ai_query.value.trim());
        Object.assign(params, aiParams);
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
        const data = Array.isArray(payload) ? payload : (payload.data || []);
        renderResults(data);
    } catch (err) {
        const container = document.getElementById('results');
        container.innerHTML = `<p class="text-red-500">Failed to fetch results: ${err.message}. Try again later.</p>`;
        console.error(err);
    } finally {
        showLoading(false);
    }
}

/**
 * Client-side "AI" Parser
 * Converts natural language queries into structured API filters.
 * e.g. "Apartment in Nairobi under 50k" -> { htype: 'apartment', city: 'Nairobi', max_price: 50000 }
 */
function parseAIQuery(query) {
    const params = {};
    const lowerQuery = query.toLowerCase();

    // 1. Extract Price (e.g., "under 50000", "50k", "budget 50,000")
    const priceMatch = lowerQuery.match(/(?:under|below|max|budget|less than) \s*(\d+(?:,\d+)*(?:k)?)/);
    if (priceMatch) {
        let price = priceMatch[1].replace(/,/g, '');
        if (price.endsWith('k')) {
            price = parseInt(price) * 1000;
        }
        params.max_price = price;
    }

    // 2. Extract Property Type
    const types = ['apartment', 'house', 'studio', 'single_room'];
    for (const type of types) {
        if (lowerQuery.includes(type.replace('_', ' '))) {
            params.htype = type;
            break;
        }
    }

    // 3. Extract City (Simple keyword matching)
    const cities = ['nairobi', 'mombasa', 'kisumu', 'nakuru', 'eldoret'];
    for (const city of cities) {
        if (lowerQuery.includes(city)) {
            params.city = city.charAt(0).toUpperCase() + city.slice(1);
            break;
        }
    }

    // 4. Fallback for general text search
    if (Object.keys(params).length === 0) {
        params.q = query;
    }

    return params;
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
                window.open(`listings.php?id=${item.id}`, '_blank');
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
    
    // Voice Search Setup
    const voiceBtn = document.getElementById('voiceSearchBtn');
    const aiInput = document.querySelector('input[name="ai_query"]');
    
    if (voiceBtn && aiInput && ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        
        recognition.continuous = false;
        recognition.lang = 'en-KE'; // Default to Kenyan English
        recognition.interimResults = false;

        voiceBtn.addEventListener('click', () => {
            recognition.start();
            voiceBtn.classList.add('text-danger', 'pulse-animation'); // Visual feedback
        });

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            aiInput.value = transcript;
            handleSearch(); // Auto-submit
            voiceBtn.classList.remove('text-danger', 'pulse-animation');
        };

        recognition.onerror = (event) => {
            console.error('Voice recognition error', event.error);
            voiceBtn.classList.remove('text-danger', 'pulse-animation');
        };
    } else if (voiceBtn) {
        voiceBtn.style.display = 'none'; // Hide if not supported
    }

    initMap();
    handleSearch();
});