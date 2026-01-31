import apiClient from './apiClient.js';
import { showNotification } from './utils.js';

let userFavorites = [];

// Function to load favorites from user object in local storage
const loadUserFavorites = () => {
    try {
        const user = JSON.parse(localStorage.getItem('user'));
        userFavorites = user?.favorites?.map(fav => fav.id || fav) || [];
    } catch (e) {
        userFavorites = [];
    }
};

export const isFavorite = (listingId) => {
    // Ensure we are comparing same types
    return userFavorites.includes(Number(listingId));
};

export async function toggleFavorite(btn, listingId) {
    const token = localStorage.getItem('token');
    if (!token) {
        showNotification('Please log in to add favorites.', 'warning');
        // Redirect to login after a short delay
        setTimeout(() => window.location.href = 'login.html?redirect=' + window.location.pathname, 1500);
        return;
    }

    const isCurrentlyFavorite = isFavorite(listingId);
    
    // Optimistic UI update
    btn.classList.toggle('btn-danger', !isCurrentlyFavorite);
    btn.classList.toggle('btn-outline-secondary', isCurrentlyFavorite);

    if (isCurrentlyFavorite) {
        // Remove from local list
        userFavorites = userFavorites.filter(id => id !== Number(listingId));
    } else {
        // Add to local list
        userFavorites.push(Number(listingId));
    }

    try {
        const method = isCurrentlyFavorite ? 'DELETE' : 'PUT';
        await apiClient.request(`/users/me/favorites/${listingId}`, method);
        // We can optionally update the user object in local storage here if needed
    } catch (error) {
        console.error('Error toggling favorite:', error);
        
        // Revert UI on error
        btn.classList.toggle('btn-danger', isCurrentlyFavorite);
        btn.classList.toggle('btn-outline-secondary', !isCurrentlyFavorite);

        // Revert local list
        if (isCurrentlyFavorite) {
            userFavorites.push(Number(listingId));
        } else {
            userFavorites = userFavorites.filter(id => id !== Number(listingId));
        }
        showNotification('Failed to update favorite. Please try again.', 'error');
    }
}

export function getFavoriteButtonHtml(listingId, isFavorite) {
    const iconClass = isFavorite ? 'fas text-danger' : 'far text-white';
    return `<button class="btn btn-link position-absolute top-0 start-0 m-3 p-0 favorite-btn" data-id="${listingId}" style="z-index: 10;"><i class="${iconClass} fa-heart fa-2x" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i></button>`;
}

// Initial load of favorites when the script is loaded
loadUserFavorites();