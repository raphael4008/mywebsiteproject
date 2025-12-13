import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgot-password-form');
    const responseContainer = document.getElementById('forgot-password-response');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            const email = form.email.value;
            responseContainer.textContent = '';

            submitButton.disabled = true;
            submitButton.textContent = 'Sending...';

            try {
                const result = await apiClient.request('/auth/forgot-password', 'POST', { email });
                responseContainer.innerHTML = `<span class="text-success">${result.message || 'If an account with that email exists, a password reset link has been sent.'}</span>`;
                form.reset();
            } catch (error) {
                responseContainer.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Send Reset Link';
            }
        });
    }
});