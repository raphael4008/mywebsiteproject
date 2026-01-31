
import apiClient from './apiClient.js';
import { formatCurrency, getImageUrl, showNotification } from './utils.js';

document.addEventListener('DOMContentLoaded', () => {
    const listingsContainer = document.getElementById('all-listings-container');
    if (!listingsContainer) return;

    let columns = [];
    let listings = [];
    let currentPage = 0;
    const limit = 12;
    let isLoading = false;
    let totalListings = 0;

    // --- Masonry Layout Logic ---
    function setupMasonryGrid() {
        listingsContainer.style.display = 'flex';
        listingsContainer.style.alignItems = 'flex-start';
        const columnCount = getColumnCount();
        columns = [];
        for (let i = 0; i < columnCount; i++) {
            const column = document.createElement('div');
            column.classList.add('masonry-column');
            column.style.width = `${100 / columnCount}%`;
            column.style.padding = '0 10px';
            listingsContainer.appendChild(column);
            columns.push(column);
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

    function createPinterestListingCard(listing) {
        const card = document.createElement('div');
        card.classList.add('listing-pin-card');
        const carouselId = `listingCarousel-${listing.id}`;
        const imageCarouselHtml = listing.images && listing.images.length ? `
            <div id="${carouselId}" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="false">
                <div class="carousel-inner rounded-top">
                    ${listing.images.map((img, index) => `
                        <div class="carousel-item ${index === 0 ? 'active' : ''}">
                            <img src="${getImageUrl(img)}" class="d-block w-100" style="height: 200px; object-fit: cover;" alt="${listing.title} image ${index + 1}">
                        </div>
                    `).join('')}
                </div>
                ${listing.images.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                ` : ''}
            </div>
        ` : `<img src="css/placeholder.jpg" class="w-100 rounded-top" style="height: 200px; object-fit: cover;" alt="Placeholder image">`;

        card.innerHTML = `
            <a href="listing-details.php?id=${listing.id}" class="text-decoration-none text-dark">
                <div class="position-relative">
                    ${imageCarouselHtml}
                    <div class="card-overlay">
                        <h5 class="card-title-pin text-truncate">${listing.title}</h5>
                    </div>
                </div>
                <div class="card-body-pin">
                    <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city}</p>
                    <p class="h6 text-primary fw-bold mb-0">${formatCurrency(listing.rent_amount)}</p>
                </div>
            </a>
        `;
        return card;
    }

    function appendListing(listing) {
        const card = createPinterestListingCard(listing);
        const shortestColumn = getShortestColumn();
        shortestColumn.appendChild(card);
    }

    // --- Data Fetching and Infinite Scroll ---
    async function fetchAndRenderListings() {
        if (isLoading || (totalListings > 0 && listings.length >= totalListings)) return;

        isLoading = true;
        showNotification('Loading more homes...', 'info');

        try {
            const params = new URLSearchParams(window.location.search);
            params.set('offset', currentPage * limit);
            params.set('limit', limit);
            
            // Convert params to an object to pass to apiClient
            const searchData = Object.fromEntries(params.entries());

            const response = await apiClient.request('/listings/search', 'GET', searchData);
            
            const new_listings = response.data;
            totalListings = response.total;

            if (new_listings.length > 0) {
                new_listings.forEach(listing => {
                    listings.push(listing);
                    appendListing(listing);
                });
                currentPage++;
            } else if (listings.length === 0) {
                 listingsContainer.innerHTML = '<p class="text-center w-100">No listings found for your criteria.</p>';
            }

        } catch (error) {
            console.error('Failed to fetch listings:', error);
            showNotification(`Error loading listings: ${error.message}`, 'error');
        } finally {
            isLoading = false;
        }
    }

    function handleInfiniteScroll() {
        const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
        if (clientHeight + scrollTop >= scrollHeight - 300) {
            fetchAndRenderListings();
        }
    }

    // --- Initialization ---
    function init() {
        listingsContainer.innerHTML = ''; // Clear existing content
        setupMasonryGrid();
        fetchAndRenderListings();
        window.addEventListener('scroll', handleInfiniteScroll);
        
        // Handle resizing
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                listingsContainer.innerHTML = '';
                setupMasonryGrid();
                listings.forEach(appendListing);
            }, 300);
        });
    }

    init();
});
