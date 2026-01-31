import { isInCompare } from './compare-manager.js';
import { isFavorite } from './wishlist.js';

export const formatCurrency = (amount) => {
    if (amount === undefined || amount === null) return 'KES 0';
    return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount);
};

export const getImageUrl = (img) => {
    if (!img) return 'images/placeholder.svg';
    if (typeof img === 'string') return img;
    return img.path || img.image_path || 'images/placeholder.svg';
};

export const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-KE', { year: 'numeric', month: 'long', day: 'numeric' });
};

const getActionButtons = (listing, context) => {
    switch (context) {
        case 'owner':
            return `
                <a href="../listing-details.php?id=${listing.id}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-eye"></i></a>
                <button class="btn btn-sm btn-outline-secondary edit-btn" data-id="${listing.id}"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-info availability-btn" data-id="${listing.id}"><i class="fas fa-calendar-alt"></i></button>
                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${listing.id}"><i class="fas fa-trash"></i></button>
            `;
        case 'compare':
            return `
                <a href="listing-details.php?id=${listing.id}" class="btn btn-sm btn-primary">View Details</a>
                <button class="btn btn-sm btn-outline-danger remove-from-compare-btn" data-id="${listing.id}"><i class="fas fa-times"></i> Remove</button>
            `;
        default: // search context
            const isFav = isFavorite(listing.id);
            const inComp = isInCompare(listing.id);
            return `
                <a href="listing-details.php?id=${listing.id}" class="btn btn-sm btn-primary">View Details</a>
                <button class="btn btn-sm ${isFav ? 'btn-danger' : 'btn-outline-secondary'} favorite-btn" data-id="${listing.id}" data-is-favorite="${isFav}">
                    <i class="fas fa-heart"></i>
                </button>
                <button class="btn btn-sm ${inComp ? 'btn-primary' : 'btn-outline-secondary'} compare-btn" data-id="${listing.id}">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            `;
    }
};

export const createListingCard = (listing, context = 'search') => {
    const imageUrl = listing.images && listing.images.length ? getImageUrl(listing.images[0]) : 'css/placeholder.jpg';

    return `
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 border-0 shadow-sm listing-card">
                <div class="position-relative">
                    <img src="${imageUrl}" class="card-img-top" alt="${listing.title}" style="height: 250px; object-fit: cover;">
                    <div class="position-absolute top-0 end-0 m-2">
                        ${listing.verified ? '<span class="badge bg-success me-1"><i class="fas fa-check-circle"></i> Verified</span>' : ''}
                        <span class="badge bg-info text-dark">${listing.status}</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold text-truncate">${listing.title}</h5>
                    <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city}</p>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="h5 text-primary mb-0 fw-bold">${formatCurrency(listing.rent_amount)}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            ${getActionButtons(listing, context)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
};

export const showNotification = (message, type = 'success', duration = 3000) => {
    const container = document.getElementById('notification-container') || createNotificationContainer();
    
    let iconClass = '';
    let title = '';
    let alertClass = '';

    switch (type) {
        case 'success':
            iconClass = 'fas fa-check-circle';
            title = 'Success!';
            alertClass = 'alert-success';
            break;
        case 'error':
            iconClass = 'fas fa-times-circle';
            title = 'Error!';
            alertClass = 'alert-danger';
            break;
        case 'info':
            iconClass = 'fas fa-info-circle';
            title = 'Info!';
            alertClass = 'alert-info';
            break;
        case 'warning':
            iconClass = 'fas fa-exclamation-triangle';
            title = 'Warning!';
            alertClass = 'alert-warning';
            break;
        default:
            iconClass = 'fas fa-info-circle';
            title = 'Notification!';
            alertClass = 'alert-info';
    }

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show d-flex align-items-center mb-2`;
    notification.role = 'alert';
    notification.innerHTML = `
        <i class="${iconClass} me-2"></i>
        <div>
            <h6 class="alert-heading mb-0">${title}</h6>
            <small>${message}</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    container.appendChild(notification);
    
    // Automatically dismiss after a duration
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(notification);
        bsAlert.close();
    }, duration);
};

function createNotificationContainer() {
    const container = document.createElement('div');
    container.id = 'notification-container';
    // Style the container to position notifications in the corner (e.g., top-right)
    container.style.position = 'fixed';
    container.style.top = '10px';
    container.style.right = '10px';
    container.style.zIndex = '1050'; // Ensure it's above other content
    container.style.maxWidth = '350px'; // Limit width
    document.body.appendChild(container);
    return container;
}