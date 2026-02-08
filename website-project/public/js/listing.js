import apiClient from './apiClient.js?v=2';
import { formatCurrency, getImageUrl, showNotification } from './utils.js';
import { toggleFavorite, getFavoriteButtonHtml } from './wishlist.js';
import { initLazyLoading } from './main.js';

// --- SINGLE LISTING VIEW FUNCTIONS (listing-details.php) ---

function renderListingDetails(listing) {
    const container = document.getElementById('listing-details-content');
    if (!container) return;

    if (!listing) {
        container.innerHTML = '<p class="text-danger">Listing not found.</p>';
        return;
    }

    const imageGallery = listing.images && listing.images.length ? `
        <div id="lightgallery" class="row g-2">
            ${listing.images.map(img => `
                <a href="${getImageUrl(img)}" class="col-lg-6 col-md-12">
                    <img src="${getImageUrl(img)}" class="img-fluid rounded" alt="Listing image" style="height: 400px; width: 100%; object-fit: cover;"/>
                </a>
            `).join('')}
        </div>
    ` : `<img src="images/placeholder.svg" class="d-block w-100" style="height: 450px; object-fit: cover; border-radius: 1rem;" alt="Placeholder image">`;


    container.innerHTML = `
        <div class="mb-4">
            <div class="position-relative">
                ${imageGallery}
                ${getFavoriteButtonHtml(listing.id, listing.is_favorite)}
            </div>
        </div>
        <h1 class="display-5 fw-bold mb-3">${listing.title}</h1>
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-success me-2 text-capitalize">${listing.status}</span>
            <span class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city}</span>
            <button class="btn btn-sm btn-outline-secondary ms-auto" id="shareBtn"><i class="fas fa-share-alt"></i> Share</button>
        </div>
        <p class="lead">${listing.description}</p>
        <div class="my-4 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-4">
                    <p class="h5 fw-bold text-primary">${formatCurrency(listing.rent_amount)}<span class="text-muted fs-6 fw-normal">/mo</span></p>
                    <small class="text-muted">Rent</small>
                </div>
                <div class="col-md-4 border-start border-end">
                    <p class="h5 fw-bold">${formatCurrency(listing.deposit_amount)}</p>
                    <small class="text-muted">Deposit</small>
                </div>
                <div class="col-md-4">
                    <p class="h5 fw-bold text-capitalize">${listing.htype ? listing.htype.replace('_', ' ') : 'Property'}</p>
                    <small class="text-muted">Type</small>
                </div>
            </div>
        </div>
    `;

    container.querySelector('.favorite-btn')?.addEventListener('click', () => toggleFavorite(container.querySelector('.favorite-btn'), listing.id));
    container.querySelector('#shareBtn')?.addEventListener('click', () => {
        if (navigator.share) {
            navigator.share({ title: listing.title, url: window.location.href });
        } else {
            navigator.clipboard.writeText(window.location.href);
            showNotification('Link copied to clipboard!', 'info');
        }
    });
}

function renderAgentInfo(agent) {
    const container = document.getElementById('agent-info');
    if (!container) return;

    if (!agent) {
        container.innerHTML = '<p class="text-muted">Agent information not available.</p>';
        return;
    }

    container.innerHTML = `
        <a href="agent-profile.php?id=${agent.id}">
            <img src="${getImageUrl(agent.profile_pic, 'images/placeholder.svg')}" class="img-fluid rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;" alt="Agent ${agent.name}">
        </a>
        <h6 class="fw-bold"><a href="agent-profile.php?id=${agent.id}" class="text-decoration-none text-dark">${agent.name}</a></h6>
        <a href="tel:${agent.phone}" class="text-decoration-none text-muted d-block"><i class="fas fa-phone-alt me-2"></i>${agent.phone}</a>
        <a href="mailto:${agent.email}" class="text-decoration-none text-muted d-block"><i class="fas fa-envelope me-2"></i>${agent.email}</a>
    `;
}

function handleContactAgentForm(listingId) {
    const form = document.getElementById('contactAgentForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            showNotification('Please fill out all required fields.', 'error');
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Sending...';

        try {
            await apiClient.request('/messages', 'POST', {
                listing_id: listingId,
                name: document.getElementById('contactName').value,
                email: document.getElementById('contactEmail').value,
                message: document.getElementById('contactMessage').value,
            });
            showNotification('Your message has been sent!', 'success');
            form.reset();
            form.classList.remove('was-validated');
        } catch (error) {
            showNotification(`Error: ${error.message}`, 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = originalButtonText;
        }
    });
}

function renderAmenities(listing) {
    const container = document.getElementById('amenities-list');
    if (!container) return;

    const amenities = listing.amenities;
    if (!amenities || amenities.length === 0) {
        document.getElementById('amenities-section').style.display = 'none';
        return;
    }

    container.innerHTML = amenities.map(item => `
        <div class="col-md-6 col-lg-4">
            <div class="d-flex align-items-center text-muted">
                <i class="fas ${item.icon || 'fa-check'} text-primary me-3 fa-lg"></i>
                <span class="fw-medium">${item.name}</span>
            </div>
        </div>
    `).join('');
}

function initMap(listing) {
    const mapContainer = document.getElementById('map');
    if (!mapContainer || !listing.latitude || !listing.longitude) {
        document.getElementById('map-section').style.display = 'none';
        return;
    };

    const lat = listing.latitude;
    const lng = listing.longitude;

    const map = L.map('map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(`<b>${listing.title}</b>`).openPopup();
}

function renderVideos(listing) {
    const container = document.getElementById('video-list');
    if (!container) return;

    const videos = listing.videos || [];
    if (videos.length === 0) {
        document.getElementById('video-section').style.display = 'none';
        return;
    }

    container.innerHTML = videos.map(video => `
        <div class="col-md-6">
            <div class="ratio ratio-16x9">
                <video controls src="${video.url}" title="${video.title || 'Listing video'}" allowfullscreen></video>
            </div>
            <h6 class="mt-2">${video.title || ''}</h6>
        </div>
    `).join('');
}

async function loadSimilarListings(currentListing) {
    const container = document.getElementById('similar-listings-container');
    if (!container) return;

    try {
        const params = new URLSearchParams({
            city: currentListing.city,
            htype: currentListing.htype,
            exclude_id: currentListing.id,
            limit: 3
        });
        const response = await apiClient.request(`/listings/search?${params.toString()}`);
        const similar = response.data;

        if (similar.length > 0) {
            const html = similar.map(l => `
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <a href="listing-details.php?id=${l.id}" class="text-decoration-none text-dark">
                            <img src="${getImageUrl(l.images && l.images[0])}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-truncate">${l.title}</h6>
                                <p class="text-primary fw-bold mb-0">${formatCurrency(l.rent_amount)}</p>
                            </div>
                        </a>
                    </div>
                </div>
            `).join('');
            container.innerHTML = `<h3 class="fw-bold mb-4 mt-5">Similar Homes You Might Like</h3><div class="row g-4">${html}</div>`;
        }
    } catch (e) {
        console.error('Failed to load similar listings', e);
    }
}

async function initListingDetailsPage(listingId) {
    const content = document.getElementById('listing-details-content');

    let listingData;

    // Use initial data if available
    if (window.initialListingData) {
        listingData = window.initialListingData;
    } else {
        // Fallback to API call if initial data is not present
        content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        try {
            listingData = await apiClient.request(`/listings/${listingId}`);
        } catch (error) {
            content.innerHTML = `<p class="alert alert-danger">Error loading listing: ${error.message}</p>`;
            return;
        }
    }

    if (!listingData) {
        content.innerHTML = `<p class="alert alert-danger">Could not load listing data.</p>`;
        return;
    }

    renderListingDetails(listingData);
    renderAgentInfo(listingData.agent);
    renderAmenities(listingData);
    initMap(listingData);
    renderVideos(listingData);
    loadSimilarListings(listingData);
    handleContactAgentForm(listingData.id);

    const gallery = document.getElementById('lightgallery');
    if (gallery) {
        lightGallery(gallery, {
            selector: 'a',
            download: false
        });
    }

    const neighborhoodContainer = document.getElementById('neighborhood-details');
    if (neighborhoodContainer) {
        if (listingData.neighborhood) {
            neighborhoodContainer.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="fas fa-map-marked-alt text-primary fa-3x me-3"></i>
                    <div>
                        <h4 class="fw-bold">Living in ${listingData.neighborhood.name}</h4>
                        <p class="lead text-muted">${listingData.neighborhood.description || `Discover the charm of living in ${listingData.city}.`}</p>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark border me-2"><i class="fas fa-bus"></i> Public Transport Nearby</span>
                            <span class="badge bg-light text-dark border me-2"><i class="fas fa-shopping-basket"></i> Markets Nearby</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('neighborhood-section').style.display = 'none';
        }
    }
}


// --- ALL LISTINGS VIEW FUNCTIONS (listings.php) ---

let columns = [];
let listings = [];
let currentPage = 0;
const limit = 12;
let isLoading = false;
let totalListings = 0;

function setupMasonryGrid() {
    const listingsContainer = document.getElementById('all-listings-container');
    if (!listingsContainer) return;
    listingsContainer.style.display = 'flex';
    listingsContainer.style.alignItems = 'flex-start';
    initLazyLoading();
    const columnCount = getColumnCount();
    columns = [];
    const fragment = document.createDocumentFragment();
    for (let i = 0; i < columnCount; i++) {
        const column = document.createElement('div');
        column.classList.add('masonry-column');
        column.style.width = `${100 / columnCount}%`;
        column.style.padding = '0 10px';
        fragment.appendChild(column);
        columns.push(column);
    }
    listingsContainer.appendChild(fragment);
}

function showLoader() {
    let loader = document.getElementById('listings-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'listings-loader';
        loader.className = 'w-100 text-center py-4';
        loader.innerHTML = `<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>`;
        document.getElementById('all-listings-container')?.parentNode.insertBefore(loader, document.getElementById('all-listings-container').nextSibling);
    }
    loader.style.display = 'block';
}

function hideLoader() {
    const loader = document.getElementById('listings-loader');
    if (loader) loader.style.display = 'none';
}

function showSkeletonCards(count = 8) {
    const container = document.getElementById('all-listings-container');
    if (!container) return;
    container.innerHTML = '';
    const columnCount = getColumnCount();
    for (let i = 0; i < columnCount; i++) {
        const col = document.createElement('div');
        col.classList.add('masonry-column');
        col.style.width = `${100 / columnCount}%`;
        col.style.padding = '0 10px';
        for (let j = 0; j < Math.ceil(count / columnCount); j++) {
            const sk = document.createElement('div');
            sk.className = 'card mb-4 p-3 border-0 shadow-sm';
            sk.innerHTML = '<div style="height:200px;background:#eee;border-radius:6px;margin-bottom:12px"></div><div style="height:16px;background:#eee;width:60%;margin-bottom:8px"></div><div style="height:12px;background:#eee;width:40%"></div>';
            col.appendChild(sk);
        }
        container.appendChild(col);
    }
}

function getColumnCount() {
    if (window.innerWidth >= 1200) return 4;
    if (window.innerWidth >= 992) return 3;
    if (window.innerWidth >= 768) return 2;
    return 1;
}

function getShortestColumn() {
    return columns.reduce((shortest, current) => {
        return current.offsetHeight < shortest.offsetHeight ? current : shortest;
    }, columns[0]);
}

function relayoutColumns() {
    if (!columns || columns.length === 0) return;
    const allCards = Array.from(document.querySelectorAll('.listing-pin-card'));
    columns.forEach(c => c.innerHTML = '');
    allCards.forEach(card => {
        const shortest = getShortestColumn();
        shortest.appendChild(card);
    });
}

const debounce = (fn, wait = 150) => { let t = null; return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); }; };
const relayoutColumnsDebounced = debounce(relayoutColumns, 180);

function createPinterestListingCard(listing) {
    const card = document.createElement('div');
    card.classList.add('listing-pin-card');
    const imageCarouselHtml = `<img data-src="${getImageUrl(listing.images && listing.images[0])}" src="images/placeholder.svg" loading="lazy" decoding="async" class="d-block w-100 lazy" alt="${listing.title}">`;

    card.innerHTML = `
        <a href="listing-details.php?id=${listing.id}" class="text-decoration-none text-dark">
            <div class="position-relative">
                ${imageCarouselHtml}
                <div class="card-overlay">
                    <h5 class="card-title-pin text-truncate">${listing.title}</h5>
                </div>
                 ${getFavoriteButtonHtml(listing.id, listing.is_favorite)}
            </div>
            <div class="card-body-pin">
                <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city}</p>
                <p class="h6 text-primary fw-bold mb-0">${formatCurrency(listing.price)}</p>
            </div>
        </a>
    `;
    card.querySelector('.favorite-btn')?.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent link navigation
        toggleFavorite(e.currentTarget, listing.id);
    });
    return card;
}


function appendListing(listing) {
    const card = createPinterestListingCard(listing);
    const shortestColumn = getShortestColumn();
    shortestColumn.appendChild(card);
    initLazyLoading();
}

async function fetchAndRenderListings() {
    if (isLoading || (totalListings > 0 && listings.length >= totalListings)) return;

    isLoading = true;
    showLoader();

    try {
        const params = new URLSearchParams(window.location.search);
        params.set('offset', currentPage * limit);
        params.set('limit', limit);

        const response = await apiClient.request(`/listings/search?${params.toString()}`);
        const new_listings = response.data;
        totalListings = response.total;

        hideLoader();
        if (new_listings.length > 0) {
            if (currentPage === 0) {
                document.getElementById('all-listings-container').innerHTML = '';
                setupMasonryGrid();
            }
            new_listings.forEach(listing => {
                if (!listings.some(l => l.id === listing.id)) {
                    listings.push(listing);
                    appendListing(listing);
                }
            });
            currentPage++;
        } else if (listings.length === 0) {
            document.getElementById('all-listings-container').innerHTML = '<p class="text-center w-100 py-5">No listings found for your criteria.</p>';
        }

    } catch (error) {
        console.error('Failed to fetch listings:', error);
        showNotification(`Error loading listings: ${error.message}`, 'error');
        hideLoader();
        if (currentPage === 0) {
            document.getElementById('all-listings-container').innerHTML = `<div class="alert alert-danger w-100 text-center">Failed to load listings. Please try refreshing the page.</div>`;
        }
    } finally {
        isLoading = false;
    }
}

async function reloadListings() {
    listings = [];
    currentPage = 0;
    totalListings = 0;
    isLoading = false;
    window.scrollTo(0, 0);
    showSkeletonCards(8);
    await fetchAndRenderListings();
}

function handleInfiniteScroll() {
    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    if (clientHeight + scrollTop >= scrollHeight - 300) {
        fetchAndRenderListings();
    }
}

function handleAiSearch() {
    const aiSearchForm = document.getElementById('aiSearchForm');
    if (aiSearchForm) {
        aiSearchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const query = document.getElementById('aiQuery').value;
            if (!query) return;

            const searchButton = aiSearchForm.querySelector('button[type="submit"]');
            const originalButtonText = searchButton.innerHTML;
            searchButton.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
            searchButton.disabled = true;

            try {
                const aiParams = await apiClient.request('/ai-search', 'POST', { query });
                const params = new URLSearchParams(aiParams);
                history.pushState(null, '', `listings.php?${params.toString()}`);
                await reloadListings();
            } catch (error) {
                console.error('Error with AI search:', error);
                alert('There was an error with the AI search. Please try again.');
            } finally {
                searchButton.innerHTML = originalButtonText;
                searchButton.disabled = false;
            }
        });
    }
}

function handleFilterForm() {
    const filterForm = document.getElementById('listingsFilterForm');
    if (!filterForm) return;

    const updateFormFromURL = () => {
        const params = new URLSearchParams(window.location.search);
        document.getElementById('filterCity').value = params.get('city') || '';
        document.getElementById('filterType').value = params.get('htype') || '';
        document.getElementById('filterPrice').value = params.get('maxRent') || '';
        document.getElementById('sortListings').value = params.get('sort') || 'newest';
    };

    updateFormFromURL();

    filterForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const city = document.getElementById('filterCity')?.value || '';
        const type = document.getElementById('filterType')?.value || '';
        const price = document.getElementById('filterPrice')?.value || '';
        const sort = document.getElementById('sortListings')?.value || '';

        const params = new URLSearchParams();
        if (city) params.set('city', city);
        if (type) params.set('htype', type);
        if (price) params.set('maxRent', price);
        if (sort) params.set('sort', sort);

        history.pushState(null, '', `listings.php?${params.toString()}`);
        await reloadListings();
    });

    window.addEventListener('popstate', () => {
        updateFormFromURL();
        reloadListings();
    });
}

function initListingsPage() {
    setupMasonryGrid();
    reloadListings();
    handleAiSearch();
    handleFilterForm();
    window.addEventListener('scroll', handleInfiniteScroll);

    let previousColumnCount = getColumnCount();
    const onResize = debounce(() => {
        const newCount = getColumnCount();
        if (newCount !== previousColumnCount) {
            const listingsContainer = document.getElementById('all-listings-container');
            listingsContainer.innerHTML = '';
            setupMasonryGrid();
            listings.forEach(appendListing);
            previousColumnCount = newCount;
        } else {
            relayoutColumnsDebounced();
        }
    }, 200);
    window.addEventListener('resize', onResize);
}

// --- INITIALIZATION ---

document.addEventListener('DOMContentLoaded', () => {
    const listingDetailsContent = document.getElementById('listing-details-content');
    const allListingsContainer = document.getElementById('all-listings-container');

    if (listingDetailsContent) {
        let listingId = null;

        // Try to get ID from initial data
        if (window.initialListingData && window.initialListingData.id) {
            listingId = window.initialListingData.id;
        }

        // Try to get ID from URL query param
        if (!listingId) {
            listingId = new URLSearchParams(window.location.search).get('id');
        }

        // Try to get ID from URL path (e.g. /listing/123)
        if (!listingId) {
            const matches = window.location.pathname.match(/\/listing\/(\d+)/);
            if (matches && matches[1]) {
                listingId = matches[1];
            }
        }

        if (listingId) {
            initListingDetailsPage(listingId);
        } else {
            listingDetailsContent.innerHTML = '<div class="alert alert-danger">No listing ID provided.</div>';
        }
    } else if (allListingsContainer) {
        initListingsPage();
    }
});