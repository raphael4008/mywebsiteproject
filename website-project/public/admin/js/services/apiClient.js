const apiClient = {
    async request(endpoint, options = {}) {
        const token = localStorage.getItem('token');
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers,
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(endpoint, {
            ...options,
            headers,
        });

        if (!response.ok) {
            const errorDetails = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, details: ${errorDetails}`);
        }

        return response.json();
    },

    buildQueryParams(params) {
        return Object.keys(params)
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
            .join('&');
    },

    async get(endpoint, params = {}) {
        const queryString = this.buildQueryParams(params);
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    },

    async post(endpoint, body = {}, options = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body),
            ...options,
        });
    },

    async upload(endpoint, formData, options = {}) {
        const token = localStorage.getItem('token');
        const headers = {
            ...options.headers,
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers,
        });

        if (!response.ok) {
            const errorDetails = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, details: ${errorDetails}`);
        }

        return response.json();
    },
};

export default apiClient;