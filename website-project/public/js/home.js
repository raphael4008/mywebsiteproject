document.addEventListener('DOMContentLoaded', () => {
    // AI Search Bar Logic
    const aiSearchInput = document.getElementById('ai-search-input');
    const aiSearchBtn = document.getElementById('ai-search-btn');
    aiSearchBtn.addEventListener('click', () => {
        const query = aiSearchInput.value;
        if (query) {
            window.location.href = `listings?ai_query=${encodeURIComponent(query)}`;
        }
    });

    // Featured Properties Logic
    const featuredGrid = document.getElementById('featured-grid');
    async function fetchFeatured() {
        try {
            const response = await fetch('api/listings/featured');
            if (!response.ok) throw new Error('Could not fetch featured properties.');
            const result = await response.json();
            
            featuredGrid.innerHTML = ''; // Clear any loading state
            result.data.forEach(listing => {
                const card = document.createElement('div');
                card.className = 'listing-card';
                
                const imageUrl = listing.images && listing.images.length > 0 ? listing.images[0].path : 'images/placeholder.svg';
                const price = new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(listing.price);

                card.innerHTML = `
                    <a href="listing/${listing.id}" class="text-decoration-none text-white">
                        <img src="${imageUrl}" alt="${listing.title}" onerror="this.onerror=null;this.src='images/placeholder.svg';">
                        <div class="listing-card-content">
                            <h5>${listing.title}</h5>
                            <p class="text-muted">${listing.city}</p>
                            <p class="fs-5 fw-bold">${price}</p>
                        </div>
                    </a>
                `;
                featuredGrid.appendChild(card);
            });
        } catch (error) {
            featuredGrid.innerHTML = '<p class="text-danger">Could not load featured properties.</p>';
            console.error(error);
        }
    }

    fetchFeatured();
});