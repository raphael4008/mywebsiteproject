import apiClient from './apiClient.js';
import { clearErrors, showError } from './formUtils.js';

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    const authMessage = document.createElement('div');

    if (registerForm) {
        const fields = ['name', 'email', 'password', 'role'];

        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors(fields, authMessage);

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            
            let isValid = true;

            if (!name) {
                showError('name', 'Name is required.');
                isValid = false;
            }

            if (!email) {
                showError('email', 'Email is required.');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email', 'Please enter a valid email address.');
                isValid = false;
            }

            if (!password) {
                showError('password', 'Password is required.');
                isValid = false;
            } else if (password.length < 8) {
                showError('password', 'Password must be at least 8 characters long.');
                isValid = false;
            }

            if (!role) {
                showError('role', 'Please select a role.');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            registerForm.parentNode.insertBefore(authMessage, registerForm.nextSibling);

            try {
                await apiClient.request('/register', 'POST', { name, email, password, role });
                window.location.href = '/login.html?registered=true';
            } catch (error) {
                console.error('Registration error:', error);
                if (error.errors) {
                    Object.keys(error.errors).forEach(field => {
                        showError(field, error.errors[field].join(', '));
                    });
                } else {
                    authMessage.textContent = `Registration failed: ${error.message}`;
                    authMessage.style.color = 'red';
                }
            }
        });
    }
});
