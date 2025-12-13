import apiClient from './apiClient.js';

document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const contactMsg = document.getElementById('contactMsg');
    contactMsg.innerHTML = ''; // Clear previous messages

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
        const result = await apiClient.request('/messages', 'POST', data);
        contactMsg.innerHTML = `<span style='color:green'>${result.message}</span>`;
        form.reset();
    } catch (error) {
        console.error('Error submitting contact form:', error);
        contactMsg.innerHTML = `<span style='color:red'>Error: ${error.message || 'An unexpected error occurred.'}</span>`;
    }
});
