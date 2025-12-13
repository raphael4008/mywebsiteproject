import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const neighborhoodList = document.getElementById('neighborhood-list');

    async function loadNeighborhoods() {
        try {
            const neighborhoods = await apiClient.request('/neighborhoods');

            if (!neighborhoods || neighborhoods.length === 0) {
                neighborhoodList.innerHTML = '<p>No neighborhoods found.</p>';
                return;
            }

            neighborhoodList.innerHTML = neighborhoods.map(neighborhood => `
                <a href="neighborhood.html?name=${neighborhood.name}" class="listing-card">
                    <img src="${neighborhood.image}" alt="${neighborhood.name}">
                    <div class="card-body">
                        <div class="title">${neighborhood.name}</div>
                        <div class="meta">${neighborhood.city}</div>
                    </div>
                </a>
            `).join('');
        } catch (error) {
            console.error('Error fetching neighborhoods:', error);
            neighborhoodList.innerHTML = '<p>Error loading neighborhoods.</p>';
        }
    }

    loadNeighborhoods();
});