import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    const transportForm = document.getElementById('transportForm');
    const transportMsg = document.getElementById('transportMsg');

    if (transportForm) {
        const submitButton = transportForm.querySelector('button[type="submit"]');

        // Realtime estimate when key fields change
        async function fetchEstimate() {
            const formData = new FormData(transportForm);
            const data = Object.fromEntries(formData.entries());
            try {
                    const res = await apiClient.request('/users/transport/estimate', 'POST', data);
                const estimateBox = document.getElementById('transport-estimate');
                if (estimateBox) {
                    estimateBox.innerHTML = `<div class="alert alert-info p-2">Estimated: <strong>${res.estimated_currency} ${res.estimated_cost}</strong> • ${res.estimated_duration_minutes} min • ${res.distance_km} km</div>`;
                }
            } catch (err) {
                console.error('Estimate fetch failed', err);
            }
        }

        transportForm.querySelectorAll('input,select').forEach(el => el.addEventListener('change', fetchEstimate));

        transportForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            transportMsg.innerHTML = ''; // Clear previous messages

            const formData = new FormData(transportForm);
            const data = Object.fromEntries(formData.entries());

            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';

            try {
                const result = await apiClient.request('/users/transport', 'POST', data);
                
                transportMsg.innerHTML = `<span class="text-success">${result.message || 'Your request has been submitted. We will contact you shortly.'}</span>`;
                transportForm.reset();
                const estimateBox = document.getElementById('transport-estimate');
                if (estimateBox) estimateBox.innerHTML = '';

            } catch (error) {
                console.error('Error:', error);
                transportMsg.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Request a Quote';
            }
        });
    }

    const driverList = document.getElementById('driver-list');
    if (driverList) {
        driverList.innerHTML = '<p>Loading drivers...</p>';
        try {
            const drivers = await apiClient.request('/users/drivers');
            
            driverList.innerHTML = ''; // Clear loading message

            if (drivers.length === 0) {
                driverList.innerHTML = '<p>No drivers are available at the moment.</p>';
                return;
            }

            drivers.forEach(driver => {
                const driverCard = document.createElement('div');
                driverCard.className = 'driver-card';
                driverCard.innerHTML = `
                    <img src="${driver.image || 'images/a.jpg'}" alt="${driver.name}">
                    <h3>${driver.name}</h3>
                    <p>${driver.vehicle}</p>
                    <p>Rating: ${driver.rating} ★</p>
                    <button class="cta-btn">Book Now</button>
                `;
                driverList.appendChild(driverCard);
            });
        } catch (error) {
            console.error('Error fetching drivers:', error);
            driverList.innerHTML = '<p class="text-danger">Could not load available drivers at this time.</p>';
        }
    }
});