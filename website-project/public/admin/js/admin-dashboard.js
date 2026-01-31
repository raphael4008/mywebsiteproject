import apiClient from '../../js/apiClient.js'; // Adjust path as necessary
import { logout } from '../../js/auth.js';     // Adjust path as necessary

document.addEventListener('DOMContentLoaded', () => {
    // Initial load of dashboard data
    loadDashboardStats();

    // Event listeners for sidebar navigation
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar .nav-link').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            showSection(this.dataset.section);
        });
    });

    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', (e) => {
        e.preventDefault();
        logout(); // Use the logout function from auth.js
    });
});

async function loadDashboardStats() {
    try {
        const stats = await apiClient.request('/admin/stats');
        document.getElementById('totalUsers').textContent = stats.totalUsers;
        document.getElementById('totalListings').textContent = stats.totalListings;
        document.getElementById('pendingListings').textContent = stats.pendingListings;
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
        // Optionally display a user-friendly error message
    }
}

function showSection(sectionId) {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.add('d-none');
    });
    document.getElementById(`${sectionId}-section`).classList.remove('d-none');

    // Load data specific to the section when it becomes visible
    switch (sectionId) {
        case 'listings':
            loadListings();
            break;
        case 'users':
            loadUsers();
            break;
        case 'reservations':
            loadReservations();
            break;
        case 'amenities':
            loadAmenities();
            break;
        // 'dashboard' section is loaded on initial page load
    }
}

// --- Placeholder functions for other sections (to be implemented) ---

async function loadListings() {
    const listingsTableBody = document.getElementById('adminListingsTable');
    listingsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Loading listings...</td></tr>';
    try {
        const result = await apiClient.request('/admin/listings');
        const listings = result.data; // Assuming API returns { data: [], total: X }
        listingsTableBody.innerHTML = ''; // Clear loading message

        if (listings.length === 0) {
            listingsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No listings found.</td></tr>';
            return;
        }

        listings.forEach(listing => {
            const row = `
                <tr>
                    <td class="ps-4">${listing.title}</td>
                    <td>${listing.owner_name}</td>
                    <td><span class="badge bg-${listing.status === 'APPROVED' ? 'success' : 'warning'}">${listing.status}</span></td>
                    <td>
                        ${listing.status === 'PENDING' ? `<button class="btn btn-sm btn-success verify-listing-btn" data-id="${listing.id}">Verify</button>` : ''}
                        <button class="btn btn-sm btn-danger delete-listing-btn" data-id="${listing.id}">Delete</button>
                    </td>
                </tr>
            `;
            listingsTableBody.insertAdjacentHTML('beforeend', row);
        });

        // Add event listeners for new buttons
        listingsTableBody.querySelectorAll('.verify-listing-btn').forEach(button => {
            button.addEventListener('click', (e) => verifyListing(e.target.dataset.id));
        });
        listingsTableBody.querySelectorAll('.delete-listing-btn').forEach(button => {
            button.addEventListener('click', (e) => deleteListing(e.target.dataset.id));
        });

    } catch (error) {
        console.error('Failed to load listings:', error);
        listingsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading listings.</td></tr>';
    }
}

async function verifyListing(listingId) {
    if (!confirm('Are you sure you want to verify this listing?')) return;
    try {
        await apiClient.request(`/admin/listings/verify/${listingId}`, 'POST');
        alert('Listing verified successfully!');
        loadListings(); // Reload listings to update status
    } catch (error) {
        console.error('Failed to verify listing:', error);
        alert(error.message || 'Failed to verify listing.');
    }
}

async function deleteListing(listingId) {
    if (!confirm('Are you sure you want to delete this listing? This action cannot be undone.')) return;
    try {
        await apiClient.request(`/admin/listings/${listingId}`, 'DELETE');
        alert('Listing deleted successfully!');
        loadListings(); // Reload listings
    } catch (error) {
        console.error('Failed to delete listing:', error);
        alert(error.message || 'Failed to delete listing.');
    }
}

async function loadUsers() {
    const usersTableBody = document.getElementById('adminUsersTable');
    usersTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Loading users...</td></tr>';
    try {
        const users = await apiClient.request('/admin/users');
        usersTableBody.innerHTML = ''; // Clear loading message

        if (users.length === 0) {
            usersTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No users found.</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = `
                <tr>
                    <td class="ps-4">${user.name}</td>
                    <td>${user.email}</td>
                    <td>
                        <select class="form-select form-select-sm user-role-select" data-id="${user.id}">
                            <option value="user" ${user.role === 'user' ? 'selected' : ''}>User</option>
                            <option value="owner" ${user.role === 'owner' ? 'selected' : ''}>Owner</option>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="agent" ${user.role === 'agent' ? 'selected' : ''}>Agent</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-user-btn" data-id="${user.id}">Delete</button>
                    </td>
                </tr>
            `;
            usersTableBody.insertAdjacentHTML('beforeend', row);
        });

        usersTableBody.querySelectorAll('.user-role-select').forEach(select => {
            select.addEventListener('change', (e) => updateUserRole(e.target.dataset.id, e.target.value));
        });
        usersTableBody.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', (e) => deleteUser(e.target.dataset.id));
        });

    } catch (error) {
        console.error('Failed to load users:', error);
        usersTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading users.</td></tr>';
    }
}

async function updateUserRole(userId, newRole) {
    if (!confirm(`Are you sure you want to change the role of this user to ${newRole}?`)) return;
    try {
        await apiClient.request(`/admin/users/${userId}/role`, 'POST', { role: newRole });
        alert('User role updated successfully!');
        // No need to reload users if the change is reflected in the select
    } catch (error) {
        console.error('Failed to update user role:', error);
        alert(error.message || 'Failed to update user role.');
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    try {
        await apiClient.request(`/admin/users/${userId}`, 'DELETE');
        alert('User deleted successfully!');
        loadUsers(); // Reload users
    } catch (error) {
        console.error('Failed to delete user:', error);
        alert(error.message || 'Failed to delete user.');
    }
}

async function loadReservations() {
    const reservationsTableBody = document.getElementById('adminReservationsTable');
    reservationsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Loading reservations...</td></tr>';
    try {
        const result = await apiClient.request('/admin/reservations');
        const reservations = result.data; // Assuming API returns { data: [], total: X }
        reservationsTableBody.innerHTML = ''; // Clear loading message

        if (reservations.length === 0) {
            reservationsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No reservations found.</td></tr>';
            return;
        }

        reservations.forEach(reservation => {
            const row = `
                <tr>
                    <td class="ps-4">${reservation.listing_id}</td>
                    <td>${reservation.user_id}</td>
                    <td><span class="badge bg-${reservation.status === 'CONFIRMED' ? 'success' : 'warning'}">${reservation.status}</span></td>
                    <td>${reservation.payment_status || 'N/A'}</td>
                    <td>
                        ${reservation.status === 'PENDING' ? `<button class="btn btn-sm btn-success confirm-reservation-btn" data-id="${reservation.id}">Confirm</button>` : ''}
                        ${reservation.status !== 'CANCELLED' ? `<button class="btn btn-sm btn-warning cancel-reservation-btn" data-id="${reservation.id}">Cancel</button>` : ''}
                    </td>
                </tr>
            `;
            reservationsTableBody.insertAdjacentHTML('beforeend', row);
        });

        reservationsTableBody.querySelectorAll('.confirm-reservation-btn').forEach(button => {
            button.addEventListener('click', (e) => confirmReservation(e.target.dataset.id));
        });
        reservationsTableBody.querySelectorAll('.cancel-reservation-btn').forEach(button => {
            button.addEventListener('click', (e) => cancelReservation(e.target.dataset.id));
        });

    } catch (error) {
        console.error('Failed to load reservations:', error);
        reservationsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading reservations.</td></tr>';
    }
}

async function confirmReservation(reservationId) {
    if (!confirm('Are you sure you want to confirm this reservation?')) return;
    try {
        await apiClient.request(`/admin/reservations/confirm/${reservationId}`, 'POST');
        alert('Reservation confirmed successfully!');
        loadReservations();
    } catch (error) {
        console.error('Failed to confirm reservation:', error);
        alert(error.message || 'Failed to confirm reservation.');
    }
}

async function cancelReservation(reservationId) {
    if (!confirm('Are you sure you want to cancel this reservation?')) return;
    try {
        await apiClient.request(`/admin/reservations/cancel/${reservationId}`, 'POST');
        alert('Reservation cancelled successfully!');
        loadReservations();
    } catch (error) {
        console.error('Failed to cancel reservation:', error);
        alert(error.message || 'Failed to cancel reservation.');
    }
}

async function loadAmenities() {
    const amenitiesTableBody = document.getElementById('amenitiesTable');
    amenitiesTableBody.innerHTML = '<tr><td colspan="2" class="text-center py-4">Loading amenities...</td></tr>';
    try {
        // Assuming there will be an API for amenities later. For now, using a placeholder if needed.
        // If no direct API for /admin/amenities (GET), we might need to fetch from /amenities and add admin actions.
        // For now, let's assume `apiClient.request('/amenities')` gets all.
        const amenities = await apiClient.request('/amenities'); // Public endpoint for now
        amenitiesTableBody.innerHTML = '';

        if (amenities.length === 0) {
            amenitiesTableBody.innerHTML = '<tr><td colspan="2" class="text-center py-4">No amenities found.</td></tr>';
            return;
        }

        amenities.forEach(amenity => {
            const row = `
                <tr>
                    <td class="ps-4">${amenity.name}</td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-amenity-btn" data-id="${amenity.id}">Delete</button>
                    </td>
                </tr>
            `;
            amenitiesTableBody.insertAdjacentHTML('beforeend', row);
        });

        amenitiesTableBody.querySelectorAll('.delete-amenity-btn').forEach(button => {
            button.addEventListener('click', (e) => deleteAmenity(e.target.dataset.id));
        });

    } catch (error) {
        console.error('Failed to load amenities:', error);
        amenitiesTableBody.innerHTML = '<tr><td colspan="2" class="text-center py-4 text-danger">Error loading amenities.</td></tr>';
    }

    // Add form submission for adding amenities
    document.getElementById('addAmenityForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const amenityNameInput = document.getElementById('amenityName');
        const amenityName = amenityNameInput.value.trim();

        if (!amenityName) {
            alert('Amenity name cannot be empty.');
            return;
        }

        try {
            await apiClient.request('/admin/amenities', 'POST', { name: amenityName });
            alert('Amenity added successfully!');
            amenityNameInput.value = '';
            loadAmenities(); // Reload amenities
        } catch (error) {
            console.error('Failed to add amenity:', error);
            alert(error.message || 'Failed to add amenity.');
        }
    });
}

async function deleteAmenity(amenityId) {
    if (!confirm('Are you sure you want to delete this amenity? This action cannot be undone.')) return;
    try {
        await apiClient.request(`/admin/amenities/${amenityId}`, 'DELETE');
        alert('Amenity deleted successfully!');
        loadAmenities(); // Reload amenities
    } catch (error) {
        console.error('Failed to delete amenity:', error);
        alert(error.message || 'Failed to delete amenity.');
    }
}
