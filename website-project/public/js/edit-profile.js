import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    populateEditForm();

    const editProfileForm = document.getElementById('edit-profile-form');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', handleProfileUpdate);
    }
});

async function populateEditForm() {
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');

    if (!usernameInput || !emailInput) return;

    try {
        const user = await apiClient.request('/users/me');
        usernameInput.value = user.name;
        emailInput.value = user.email;
    } catch (error) {
        console.error('Failed to load user data for editing:', error);
        document.getElementById('edit-profile-response').innerHTML = `<p class="text-danger">Could not load your profile data.</p>`;
    }
}

async function handleProfileUpdate(e) {
    e.preventDefault();
    const form = e.target;
    const responseContainer = document.getElementById('edit-profile-response');
    const submitButton = form.querySelector('button[type="submit"]');
    responseContainer.textContent = '';

    const formData = new FormData(form);
    const data = {};
    data.name = formData.get('username');
    data.email = formData.get('email');
    if (formData.get('password')) {
        data.password = formData.get('password');
    }

    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';

    try {
        const result = await apiClient.request('/users/me', 'PUT', data);
        responseContainer.innerHTML = `<span class="text-success">${result.message || 'Profile updated successfully!'}</span>`;
    } catch (error) {
        responseContainer.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Save Changes';
    }
}