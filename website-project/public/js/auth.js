import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const authMessage = document.createElement('div'); // Create a message container

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            authMessage.textContent = '';
            loginForm.parentNode.insertBefore(authMessage, loginForm.nextSibling);

            try {
                const data = await apiClient.request('/login', 'POST', { email, password });
                localStorage.setItem('token', data.token);

                // Decode token to get user role
                const tokenPayload = JSON.parse(atob(data.token.split('.')[1]));
                const userRole = tokenPayload.data.role;

                // Handle redirect
                const params = new URLSearchParams(window.location.search);
                const redirectUrl = params.get('redirect');

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                    return;
                }

                if (userRole === 'admin') {
                    window.location.href = '/admin/';
                } else if (userRole === 'owner') {
                    window.location.href = '/owners/';
                } else {
                    window.location.href = '/index.html';
                }
            } catch (error) {
                console.error('Login error:', error);
                authMessage.textContent = `Login failed: ${error.message}`;
                authMessage.style.color = 'red';
            }
        });
    }
});