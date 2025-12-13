import apiClient from './apiClient.js';

async function populateCities() {
    const citySelect = document.getElementById('city');
    if (!citySelect) return;

    try {
        const cities = await apiClient.request('/cities');
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    } catch (error) {
        console.error('Failed to load cities:', error);
    }
}

document.addEventListener('DOMContentLoaded', populateCities);
