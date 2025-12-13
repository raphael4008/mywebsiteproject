import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    const transportForm = document.getElementById('transportForm');
    const transportMsg = document.getElementById('transportMsg');

    if (transportForm) {
        transportForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            transportMsg.innerHTML = ''; // Clear previous messages

            const formData = new FormData(transportForm);
            const data = Object.fromEntries(formData.entries());
            const submitButton = transportForm.querySelector('button[type="submit"]');

            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';

            try {
                const result = await apiClient.request('/transport', 'POST', data);
                
                transportMsg.innerHTML = `<span class="text-success">${result.message || 'Your request has been submitted. We will contact you shortly.'}</span>`;
                transportForm.reset();

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
            const drivers = await apiClient.request('/drivers');
            
            driverList.innerHTML = ''; // Clear loading message

            if (drivers.length === 0) {
                driverList.innerHTML = '<p>No drivers are available at the moment.</p>';
                return;
            }

            drivers.forEach(driver => {
                const driverCard = document.createElement('div');
                driverCard.className = 'driver-card';
                driverCard.innerHTML = `
                    <img src="${driver.image || 'img/drivers/default.png'}" alt="${driver.name}">
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