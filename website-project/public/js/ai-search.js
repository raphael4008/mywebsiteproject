import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const aiSearchForm = document.getElementById('aiSearchForm');
    if (aiSearchForm) {
        aiSearchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const query = document.getElementById('aiQuery').value;
            if (!query) return;

            const searchButton = aiSearchForm.querySelector('button[type="submit"]');
            const originalButtonText = searchButton.innerHTML;
            searchButton.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
            searchButton.disabled = true;

            try {
                const aiParams = await apiClient.request('/users/ai-search', 'POST', { query });
                const params = new URLSearchParams(aiParams);
                window.location.search = params.toString();
            } catch (error) {
                console.error('Error with AI search:', error);
                alert('There was an error with the AI search. Please try again.');
            } finally {
                searchButton.innerHTML = originalButtonText;
                searchButton.disabled = false;
            }
        });
    }
});
