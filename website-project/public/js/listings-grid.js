document.addEventListener('DOMContentLoaded', () => {
    const listingsContainer = document.getElementById('listings-grid');

    const renderLoading = () => {
        let skeletonHTML = '';
        for (let i = 0; i < 8; i++) {
            skeletonHTML += `
                <div class="card skeleton-card">
                    <div class="card-img skeleton"></div>
                    <div class="card-content">
                        <div class="card-title skeleton"></div>
                        <div class="card-location skeleton"></div>
                        <div class="card-price skeleton"></div>
                    </div>
                </div>
            `;
        }
        listingsContainer.innerHTML = skeletonHTML;
    };

    const renderError = (message) => {
        listingsContainer.innerHTML = `<div class="col-span-full text-center py-8">
            <p class="text-gray-500">${message}</p>
        </div>`;
    };

    const renderListings = (listings) => {
        listingsContainer.innerHTML = ''; // Clear loading/error state

        listings.forEach(listing => {
            const card = document.createElement('div');
            card.className = 'card';

            // Default to placeholder if no images are available
            const imageUrl = listing.images && listing.images.length > 0 ? listing.images[0].path : 'images/placeholder.svg';
            
            const price = new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: 'KES',
            }).format(listing.price);

            card.innerHTML = `
                <a href="listing-details.php?id=${listing.id}" class="card-link">
                    <div class="card-img-container">
                        <img src="${imageUrl}" alt="${listing.title}" class="card-img" loading="lazy" onerror="this.onerror=null;this.src='images/placeholder.svg';">
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">${listing.title}</h3>
                        <p class="card-location">${listing.city}</p>
                        <p class="card-price">${price} / month</p>
                        <span class="btn-primary mt-2">View Details</span>
                    </div>
                </a>
            `;
            listingsContainer.appendChild(card);
        });
    };

    const fetchListings = async () => {
        if (!listingsContainer) {
            console.error('Listings container #listings-grid not found.');
            return;
        }

        renderLoading();

        // Check for AI query in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const aiQuery = urlParams.get('ai_query');

        let endpoint = 'api/listings/search';
        let options = { method: 'GET' };

        // If there's an AI query, use the AI search endpoint
        if (aiQuery) {
            endpoint = 'api/ai-search'; // Note: This endpoint requires auth in the backend router
            options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: aiQuery })
            };
        }

        try {
            const response = await fetch(endpoint, options);
            const result = await response.json();

            if (!response.ok) {
                 throw new Error(result.error || `HTTP error! status: ${response.status}`);
            }
            
            if (result.data && result.data.length > 0) {
                renderListings(result.data);
            } else {
                renderError('No listings found matching your criteria.');
            }

        } catch (error) {
            console.error('Error fetching listings:', error);
            renderError(error.message);
        }
    };

    fetchListings();
});