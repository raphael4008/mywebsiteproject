/**
 * Auth utility module
 */

// --- Helper function to decode JWT ---
export function decodeJwt(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));

        return JSON.parse(jsonPayload);
    } catch (e) {
        console.error("Failed to decode JWT", e);
        return null;
    }
}

// --- Logout Function ---
export function logout() {
    localStorage.removeItem('token');
    sessionStorage.removeItem('token');
    localStorage.removeItem('user'); // Also remove user data
    window.location.href = 'login.php';
}

// --- Check if user is logged in ---
export function isLoggedIn() {
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    return !!token;
}

// --- Get user data ---
export function getUser() {
    const userString = localStorage.getItem('user');
    if (userString) {
        try {
            return JSON.parse(userString);
        } catch (error) {
            console.error('Failed to parse user data from localStorage', error);
            return null;
        }
    }
    return null;
}