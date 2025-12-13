import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    const authMessage = document.createElement('div');

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            authMessage.textContent = '';
            registerForm.parentNode.insertBefore(authMessage, registerForm.nextSibling);

            try {
                await apiClient.request('/register', 'POST', { name, email, password, role });
                window.location.href = '/login.html';
            } catch (error) {
                console.error('Registration error:', error);
                authMessage.textContent = `Registration failed: ${error.message}`;
                authMessage.style.color = 'red';
            }
        });
    }
});
