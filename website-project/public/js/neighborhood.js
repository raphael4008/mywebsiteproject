import apiClient from './apiClient.js';

const params = new URLSearchParams(window.location.search);
const neighborhoodName = params.get('name');

async function loadNeighborhood() {
    if (!neighborhoodName) {
        document.getElementById('neighborhood-content').innerHTML = "<p>Neighborhood not found.</p>";
        return;
    }

    try {
        const neighborhood = await apiClient.request(`/neighborhoods/${neighborhoodName}`);

        if (!neighborhood) {
            document.getElementById('neighborhood-content').innerHTML = "<p>Neighborhood not found.</p>";
            return;
        }

        document.getElementById('neighborhood-content').innerHTML = `
            <h2>${neighborhood.name}</h2>
            <img src="${neighborhood.image}" style="width:100%;max-width:500px;border-radius:1rem;">
            <p><b>City:</b> ${neighborhood.city}</p>
            <p><b>Description:</b> ${neighborhood.description}</p>
        `;

    } catch (error) {
        console.error('Error fetching neighborhood:', error);
        document.getElementById('neighborhood-content').innerHTML = "<p>Error loading neighborhood details.</p>";
    }
}

document.addEventListener('DOMContentLoaded', loadNeighborhood);
