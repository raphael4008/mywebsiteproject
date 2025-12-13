import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    const dashboardContainer = document.getElementById('dashboardContainer');

    if (!dashboardContainer) return;

    try {
        const properties = await apiClient.request('/landlord/properties');

        if (properties.data && properties.data.length > 0) {
            dashboardContainer.innerHTML = properties.data.map(property => `
                <div class="property-card">
                    <h3>${property.title}</h3>
                    <p>Location: ${property.city}, ${property.neighborhood}</p>
                    <p>Rent: KES ${property.rent_amount.toLocaleString()}</p>
                    <p>Status: ${property.available ? 'Available' : 'Occupied'}</p>
                    <button class="btn btn-outline-primary edit-property-btn" data-property-id="${property.id}">Edit</button>
                    <button class="btn btn-outline-danger delete-property-btn" data-property-id="${property.id}">Delete</button>
                </div>
            `).join('');

            setupDashboardActions();
        } else {
            dashboardContainer.innerHTML = '<p>No properties found. Add your first property to get started.</p>';
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
        dashboardContainer.innerHTML = '<p>Failed to load properties. Please try again later.</p>';
    }
});

function setupDashboardActions() {
    const editButtons = document.querySelectorAll('.edit-property-btn');
    const deleteButtons = document.querySelectorAll('.delete-property-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const propertyId = button.dataset.propertyId;
            window.location.href = `/edit-property.html?id=${propertyId}`;
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const propertyId = button.dataset.propertyId;

            if (confirm('Are you sure you want to delete this property?')) {
                try {
                    await apiClient.request(`/landlord/properties/${propertyId}`, 'DELETE');
                    button.closest('.property-card').remove();
                } catch (error) {
                    console.error('Error deleting property:', error);
                    alert('Failed to delete property. Please try again.');
                }
            }
        });
    });
}