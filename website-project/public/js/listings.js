import apiClient from './apiClient.js';
import { formatCurrency, getImageUrl, showNotification } from './utils.js';
import { toggleFavorite, getFavoriteButtonHtml } from './wishlist.js';

document.addEventListener('DOMContentLoaded', () => {
    const listingsGrid = document.getElementById('listings-grid');
    if (!listingsGrid) return;

    let currentPage = 1;
    const limit = 12;
    let isLoading = false;
    let totalListings = 0;

    function createListingCard(listing) {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6';

        const card = document.createElement('div');
        card.className = 'card h-100 shadow-sm listing-card-new';

        const imageUrl = getImageUrl(listing.images && listing.images[0], `${window.basePath}/images/placeholder.jpg`);
        const price = formatCurrency(listing.price);

        card.innerHTML = `
            <a href="${window.basePath}/listing/${listing.id}" class="text-decoration-none text-dark">
                <div class="position-relative">
                    <img src="${imageUrl}" class="card-img-top" alt="${listing.title}" style="height: 220px; object-fit: cover;">
                    <div class="badge bg-primary position-absolute top-0 start-0 m-2">${listing.status}</div>
                    ${getFavoriteButtonHtml(listing.id, listing.is_favorite)}
                </div>
                <div class="card-body">
                    <h5 class="card-title text-truncate">${listing.title}</h5>
                    <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i>${listing.city}, ${listing.neighborhood}</p>
                    <p class="card-text fs-5 fw-bold">${price}</p>
                    <div class="d-flex justify-content-between text-muted border-top pt-2 mt-2">
                        <small><i class="fas fa-bed me-1"></i> ${listing.bedrooms} Beds</small>
                        <small><i class="fas fa-bath me-1"></i> ${listing.bathrooms} Baths</small>
                        <small><i class="fas fa-ruler-combined me-1"></i> ${listing.surface_area} m²</small>
                    </div>
                </div>
            </a>
        `;

        card.querySelector('.favorite-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            toggleFavorite(e.currentTarget, listing.id);
        });

        col.appendChild(card);
        return col;
    }

    async function fetchListings(clear = false) {
        if (isLoading) return;
        isLoading = true;

        const loader = document.getElementById('loader');
        const resultsCount = document.getElementById('results-count');
        const noResults = document.getElementById('no-results');

        loader.style.display = 'block';
        noResults.style.display = 'none';

        if (clear) {
            currentPage = 1;
            listingsGrid.innerHTML = '';
        }

        const params = new URLSearchParams(window.location.search);
        params.set('page', currentPage);
        params.set('limit', limit);

        const filterForm = document.getElementById('listingsFilterForm');
        if (filterForm) {
            const city = document.getElementById('filterCity').value;
            const type = document.getElementById('filterType').value;
            const price = document.getElementById('filterPrice').value;
            if (city) params.set('city', city);
            if (type) params.set('htype', type);
            if (price) params.set('maxRent', price);
        }

        const sort = document.getElementById('sortListings').value;
        if (sort) params.set('sort', sort);

        try {
            const response = await apiClient.request(`/listings/search?${params.toString()}`);
            const { data, total } = response;

            totalListings = total;
            resultsCount.textContent = `${total} properties found`;

            if (data.length === 0 && currentPage === 1) {
                noResults.style.display = 'block';
            } else {
                data.forEach(listing => {
                    const card = createListingCard(listing);
                    listingsGrid.appendChild(card);
                });
                currentPage++;
            }
        } catch (error) {
            console.error('Failed to fetch listings:', error);
            showNotification('Error loading listings. Please try again.', 'error');
            listingsGrid.innerHTML = `<div class="col-12"><div class="alert alert-danger">Failed to load listings. Please try refreshing.</div></div>`;
        } finally {
            loader.style.display = 'none';
            isLoading = false;
        }
    }

    function handleInfiniteScroll() {
        const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
        const hasMore = listingsGrid.children.length < totalListings;
        if (clientHeight + scrollTop >= scrollHeight - 300 && hasMore) {
            fetchListings();
        }
    }

    function initListingsPage() {
        const filterForm = document.getElementById('listingsFilterForm');
        const sortSelect = document.getElementById('sortListings');
        const clearFiltersBtn = document.getElementById('clear-filters-btn');

        fetchListings(true);

        window.addEventListener('scroll', handleInfiniteScroll);

        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const params = new URLSearchParams();
                const city = document.getElementById('filterCity').value;
                const type = document.getElementById('filterType').value;
                const price = document.getElementById('filterPrice').value;
                if (city) params.set('city', city);
                if (type) params.set('htype', type);
                if (price) params.set('maxRent', price);

                history.pushState(null, '', `?${params.toString()}`);
                fetchListings(true);
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                const params = new URLSearchParams(window.location.search);
                params.set('sort', sortSelect.value);
                history.pushState(null, '', `?${params.toString()}`);
                fetchListings(true);
            });
        }

        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                if (filterForm) filterForm.reset();
                if (sortSelect) sortSelect.value = 'newest';
                history.pushState(null, '', window.location.pathname);

                document.getElementById('filterCity').value = '';
                document.getElementById('filterType').value = '';
                document.getElementById('filterPrice').value = '';

                fetchListings(true);
            });
        }

        window.addEventListener('popstate', () => {
            const params = new URLSearchParams(window.location.search);
            if (filterForm) {
                document.getElementById('filterCity').value = params.get('city') || '';
                document.getElementById('filterType').value = params.get('htype') || '';
                document.getElementById('filterPrice').value = params.get('maxRent') || '';
            }
            if (sortSelect) {
                sortSelect.value = params.get('sort') || 'newest';
            }
            fetchListings(true);
        });
    }

    initListingsPage();
});
