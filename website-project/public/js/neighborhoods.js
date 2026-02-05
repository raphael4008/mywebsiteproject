import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const neighborhoodList = document.getElementById('neighborhood-list');
    if (!neighborhoodList) return;

    async function loadNeighborhoods() {
        try {
            const response = await apiClient.request('/neighborhoods');
            const neighborhoods = response.data;

            if (!neighborhoods || neighborhoods.length === 0) {
                neighborhoodList.innerHTML = '<p>No neighborhoods found.</p>';
                return;
            }

            // Updated to use Bootstrap grid and new card styling
            neighborhoodList.innerHTML = neighborhoods.map(neighborhood => `
                <div class="col-md-4 col-sm-6" data-aos="fade-up">
                    <a href="neighborhood.html?name=${encodeURIComponent(neighborhood.name)}" class="text-decoration-none">
                        <div class="neighborhood-card shadow-sm">
                            <img src="${neighborhood.image || 'css/placeholder.jpg'}" alt="${neighborhood.name}">
                            <div class="neighborhood-overlay">
                                <h3 class="mb-1 fw-bold text-white">${neighborhood.name}</h3>
                                <p class="mb-0 text-white-50"><i class="fas fa-map-marker-alt me-1"></i> ${neighborhood.city}</p>
                            </div>
                        </div>
                    </a>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error fetching neighborhoods:', error);
            neighborhoodList.innerHTML = '<p>Error loading neighborhoods.</p>';
        }
    }

    loadNeighborhoods();
});