const API_BASE_URL = '/api'; // Adjust if your API is hosted elsewhere

const apiClient = {
    async request(endpoint, method = 'GET', data = null) {
        const url = `${API_BASE_URL}${endpoint}`;
        const token = localStorage.getItem('token');

        const headers = new Headers({
            'Content-Type': 'application/json',
        });

        if (token) {
            headers.append('Authorization', `Bearer ${token}`);
        }

        const config = {
            method,
            headers,
        };

        if (data) {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, config);
            /* if (response.status === 401) {
                // Unauthorized, redirect to login
                const currentPath = window.location.pathname + window.location.search;
                window.location.href = `/login.html?redirect=${encodeURIComponent(currentPath)}`;
                return Promise.reject(new Error('Unauthorized')); // Stop further processing
            } */

            const responseData = await response.json();

            if (!response.ok) {
                // If the server provides a specific error message, use it. Otherwise, use a generic one.
                const errorMessage = responseData.message || `An error occurred: ${response.statusText}`;
                throw new Error(errorMessage);
            }

            return responseData;
        } catch (error) {
            console.error('API Client Error:', error.message);
            // Re-throw the error so the calling function can handle it (e.g., show a message to the user)
            throw new Error(error.message || 'A network error occurred. Please check your connection.');
        }
    }
};

export default apiClient;