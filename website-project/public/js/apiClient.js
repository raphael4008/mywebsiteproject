const API_BASE_URL = 'api';
const DEFAULT_TIMEOUT = 8000; // 8 seconds

// --- Helper Functions ---
const isObject = (value) => value && typeof value === 'object' && !Array.isArray(value);

const getToken = () => localStorage.getItem('token');
const setToken = (token) => localStorage.setItem('token', token);
const getRefreshToken = () => localStorage.getItem('refreshToken');
const setRefreshToken = (token) => localStorage.setItem('refreshToken', token);
const clearTokens = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('refreshToken');
};

/**
 * Creates a standardized error object from various sources.
 * @param {string} message - The primary error message.
 * @param {Response|null} response - The raw fetch response, if available.
 * @param {object|string} responseBody - The parsed or raw response body.
 * @returns {Error} A standardized error object.
 */
const logAndCreateError = (message, response = null, responseBody = '') => {
    const error = new Error(message);
    if (response) {
        error.status = response.status;
        error.statusText = response.statusText;
    }
    // Attach parsed JSON or raw text to the error object
    error.data = isObject(responseBody) ? responseBody : { message: responseBody };
    
    // For client-side presentation, ensure a user-friendly message exists
    error.message = error.data.message || error.message;

    console.error(`API Client Error: ${error.message}`, {
        status: error.status,
        data: error.data
    });
    
    return error;
};


// --- Core API Client ---
const apiClient = {
    /**
     * Make an HTTP request to the backend API.
     * @param {string} endpoint - API path (prefixed by API_BASE_URL)
     * @param {string} method - HTTP verb
     * @param {object|FormData|null} data - Request body or query params for GET
     * @param {object} options - { headers, noAuth, timeout, isRetry }
     */
    async request(endpoint, method = 'GET', data = null, options = {}) {
        let url = `${API_BASE_URL}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;

        const headers = new Headers();
        if (!(data instanceof FormData)) {
            headers.append('Content-Type', 'application/json');
        }

        if (!options.noAuth) {
            const token = getToken();
            if (token) {
                headers.append('Authorization', `Bearer ${token}`);
            }
        }
        
        // Apply custom headers
        if (isObject(options.headers)) {
            Object.entries(options.headers).forEach(([k, v]) => {
                if (k && v !== undefined && v !== null) headers.append(k, v);
            });
        }
        
        const config = { method, headers };
        
        // --- Method Override for FormData with PUT/PATCH/DELETE ---
        if (data instanceof FormData && ['PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase())) {
            data.append('_method', method);
            config.method = 'POST';
        }

        if (data) {
            if (method.toUpperCase() === 'GET') {
                const queryString = (data instanceof URLSearchParams) ? data.toString() : new URLSearchParams(data).toString();
                url += (url.includes('?') ? '&' : '?') + queryString;
            } else {
                config.body = (data instanceof FormData) ? data : JSON.stringify(data);
            }
        }
        
        // Timeout and external signal handling
        const timeoutController = new AbortController();
        const timeoutId = setTimeout(() => timeoutController.abort(), options.timeout || DEFAULT_TIMEOUT);
        
        const signals = [timeoutController.signal];
        if (options.signal) {
            signals.push(options.signal);
        }

        // Use AbortSignal.any if available, otherwise prioritize external signal
        if (typeof AbortSignal.any === 'function') {
             config.signal = AbortSignal.any(signals);
        } else {
            config.signal = options.signal || timeoutController.signal;
        }

        try {
            const response = await fetch(url, config);
            clearTimeout(timeoutId);

            // Handle token refresh for 401 Unauthorized
            if (response.status === 401 && !options.isRetry) {
                const newToken = await this.refreshToken();
                if (newToken) {
                    // We need to create a new request without the already aborted signal
                    const newOptions = { ...options };
                    delete newOptions.signal;
                    return this.request(endpoint, method, data, { ...newOptions, isRetry: true });
                }
                // If refresh fails, the user should be logged out
                throw logAndCreateError('Session expired. Please log in again.', response);
            }

            if (response.status === 204) {
                return null; // No content, successful response
            }

            const responseBody = await this.parseResponse(response);

            if (!response.ok) {
                throw logAndCreateError('API request failed', response, responseBody);
            }

            return responseBody;

        } catch (error) {
            // Handle fetch-level errors (e.g., network, timeout)
            if (error.name === 'AbortError') {
                throw logAndCreateError('Request timed out. Please check your network connection.');
            }
            // Re-throw already processed errors
            if (error.status) {
                throw error;
            }
            // Catch any other generic errors
            throw logAndCreateError(error.message || 'An unexpected error occurred.');
        }
    },
    
    /**
     * Refreshes the authentication token.
     * @returns {Promise<string|null>} The new token or null if refresh fails.
     */
    async refreshToken() {
        const refreshToken = getRefreshToken();
        if (!refreshToken) return null;

        try {
            // Use the core request method to handle the refresh call itself
            const response = await this.request('/auth/refresh', 'POST', { refreshToken }, { noAuth: true, isRetry: true });
            if (response && response.token) {
                setToken(response.token);
                if (response.refreshToken) {
                    setRefreshToken(response.refreshToken);
                }
                return response.token;
            }
            clearTokens();
            return null;
        } catch (error) {
            console.error('Token refresh failed:', error);
            clearTokens();
            window.location.href = '/login.php?reason=session-expired';
            return null;
        }
    },

    /**
     * Parses the response body based on Content-Type.
     * @param {Response} response - The fetch response object.
     * @returns {Promise<object|string>}
     */
    async parseResponse(response) {
        const contentType = response.headers.get('content-type');
        const text = await response.text();
        
        if (contentType && contentType.includes('application/json')) {
            try {
                return text ? JSON.parse(text) : {};
            } catch (e) {
                // If JSON parsing fails, return the raw text for the error handler
                return text;
            }
        }
        return text; 
    }
};

export default apiClient;