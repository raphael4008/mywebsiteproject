import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const serviceRequestForm = document.getElementById('serviceRequestForm');
    const modalResponse = document.getElementById('modal-response');

    if (serviceRequestForm) {
        serviceRequestForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = serviceRequestForm.querySelector('button[type="submit"]');
            const formData = new FormData(serviceRequestForm);
            const data = Object.fromEntries(formData.entries());

            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            modalResponse.textContent = '';

            try {
                const result = await apiClient.request('/users/marketplace/request', 'POST', data);
                modalResponse.innerHTML = `<span class="text-success">${result.message || 'Your request has been sent! A provider will contact you shortly.'}</span>`;
                serviceRequestForm.reset();
            } catch (error) {
                modalResponse.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Request';
            }
        });
    }
});