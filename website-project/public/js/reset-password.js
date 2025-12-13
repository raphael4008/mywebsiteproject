import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reset-password-form');
    const responseContainer = document.getElementById('reset-password-response');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            const password = form.password.value;
            const passwordConfirm = form['password-confirm'].value;
            responseContainer.textContent = '';

            if (password !== passwordConfirm) {
                responseContainer.innerHTML = `<span class="text-danger">Passwords do not match.</span>`;
                return;
            }

            const params = new URLSearchParams(window.location.search);
            const token = params.get('token');

            if (!token) {
                responseContainer.innerHTML = `<span class="text-danger">Invalid or missing reset token.</span>`;
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Resetting...';

            try {
                const result = await apiClient.request('/auth/reset-password', 'POST', { token, password });
                responseContainer.innerHTML = `<span class="text-success">${result.message || 'Password has been reset successfully! Redirecting to login...'}</span>`;
                setTimeout(() => window.location.href = '/login.html', 3000);
            } catch (error) {
                responseContainer.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Reset Password';
            }
        });
    }
});