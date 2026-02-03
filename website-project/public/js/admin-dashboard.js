import apiClient from './apiClient.js';
import { logout } from './auth.js';
import { showNotification } from './utils.js';

const AppState = {
    user: null, // Stores authenticated user data
};

document.addEventListener('DOMContentLoaded', () => {
    checkAuth(); // Added checkAuth call
    setupNavigation();
    loadStats();
    loadListings();

    document.getElementById('logoutBtn').addEventListener('click', (e) => {
        e.preventDefault();
        logout();
    });

    document.getElementById('addAmenityForm').addEventListener('submit', e => {
        e.preventDefault();
        const amenityNameInput = document.getElementById('amenityName');
        const name = amenityNameInput.value.trim();
        if (name) {
            addAmenity(name);
            amenityNameInput.value = '';
        }
    });
});

function checkAuth() {
    // const token = localStorage.getItem('token');
    // const userString = localStorage.getItem('user');

    // if (!token || !userString) {
    //     window.location.href = '../login.php?redirect=admin/index.php';
    //     return;
    // }

    // try {
    //     const user = JSON.parse(userString);
    //     if (user.role !== 'admin') {
    //         console.warn('User is not an admin. Redirecting.');
    //         window.location.href = '../login.php?redirect=admin/index.php';
    //         return;
    //     }
    //     AppState.user = user; // Store user data in AppState
    //     // Potentially display admin name here if there's an element for it
    // } catch (error) {
    //     console.error('Failed to parse user data, redirecting to login.', error);
    //     window.location.href = '../login.html?redirect=admin/index.html';
    // }
}


function setupNavigation() {
    const links = document.querySelectorAll('.sidebar .nav-link[data-section]');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const section = link.getAttribute('data-section');
            links.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('d-none'));
            document.getElementById(section + '-section').classList.remove('d-none');

            if (section === 'users') {
                loadUsers();
            }
            if (section === 'reservations') {
                loadReservations();
            }
            if (section === 'amenities') {
                loadAmenities();
            }
        });
    });
}

// Helper function for consistent loading states
async function loadContentIntoContainer(containerId, fetchFunction, renderFunction, emptyMessage = 'No data found.') {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container with ID ${containerId} not found.`);
        return;
    }

    // Display spinner while loading
    container.innerHTML = `<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;

    try {
        const data = await fetchFunction();
        if ((Array.isArray(data) && data.length === 0) || (typeof data === 'object' && Object.keys(data).length === 0)) {
            container.innerHTML = `<div class="text-center py-3"><p class="text-muted">${emptyMessage}</p></div>`;
        } else {
            container.innerHTML = renderFunction(data);
        }
    } catch (error) {
        console.error(`Error loading content for ${containerId}:`, error);
        container.innerHTML = `<div class="text-center py-3"><p class="text-danger">Failed to load content.</p></div>`;
    }
}


async function loadReservations() {
    await loadContentIntoContainer('adminReservationsTable', async () => {
        const response = await apiClient.request('/admin/reservations');
        return response.data; // The reservations are in the 'data' property
    }, (reservations) => {
        return reservations.map(reservation => `
            <tr>
                <td class="ps-4 fw-bold">${reservation.listing_id}</td>
                <td>${reservation.user_id}</td>
                <td><span class="badge bg-info">${reservation.status}</span></td>
                <td><span class="badge bg-secondary">${reservation.payment_status}</span></td>
            </tr>
        `).join('');
    }, 'No reservations found.');
}

async function loadAmenities() {
    await loadContentIntoContainer('amenitiesTable', async () => {
        return await apiClient.request('/admin/amenities');
    }, (amenities) => {
        const amenitiesHtml = amenities.map(amenity => `
            <tr>
                <td class="ps-4 fw-bold">${amenity.name}</td>
                <td>
                    <button class="btn btn-sm btn-danger delete-amenity-btn" data-id="${amenity.id}">Delete</button>
                </td>
            </tr>
        `).join('');

        // Attach event listeners after content is rendered
        setTimeout(() => {
            document.querySelectorAll('.delete-amenity-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteAmenity(btn.dataset.id));
            });
        }, 0); // Use setTimeout with 0 delay to ensure elements are in DOM
        return amenitiesHtml;
    }, 'No amenities found.');
}

async function addAmenity(name) {
    try {
        await apiClient.request('/admin/amenities', 'POST', { name });
        showNotification('Amenity added!', 'success');
        loadAmenities();
    } catch (error) {
        showNotification('Failed to add amenity', 'error');
    }
}

async function deleteAmenity(id) {
    if (!confirm('Are you sure you want to delete this amenity?')) return;
    try {
        await apiClient.request(`/admin/amenities/${id}`, 'DELETE');
        showNotification('Amenity deleted!', 'success');
        loadAmenities();
    } catch (error) {
        showNotification('Failed to delete amenity', 'error');
    }
}

async function loadStats() {
    const statIds = ['totalUsers', 'totalListings', 'pendingListings'];

    statIds.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        }
    });

    try {
        const stats = await apiClient.request('/admin/stats');
        document.getElementById('totalUsers').textContent = stats.totalUsers.toLocaleString() || '0';
        document.getElementById('totalListings').textContent = stats.totalListings.toLocaleString() || '0';
        document.getElementById('pendingListings').textContent = stats.pendingListings.toLocaleString() || '0';
    } catch (error) {
        console.error('Failed to load stats:', error);
        statIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = 'N/A';
            }
        });
    }
}

async function loadListings() {
    await loadContentIntoContainer('adminListingsTable', async () => {
        return await apiClient.request('/admin/listings');
    }, (listings) => {
        const listingsHtml = listings.map(listing => `
            <tr>
                <td class="ps-4 fw-bold">${listing.title}</td>
                <td>${listing.owner_name || 'Unknown'}</td>
                <td>
                    <span class="badge bg-${listing.verified ? 'success' : 'warning'}">
                        ${listing.verified ? 'Verified' : 'Pending'}
                    </span>
                </td>
                <td>
                    ${!listing.verified ? `
                        <button class="btn btn-sm btn-success verify-btn" data-id="${listing.id}">Verify</button>
                    ` : ''}
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${listing.id}">Delete</button>
                </td>
            </tr>
        `).join('');

        // Attach event listeners after content is rendered
        setTimeout(() => {
            document.querySelectorAll('.verify-btn').forEach(btn => {
                btn.addEventListener('click', () => verifyListing(btn.dataset.id));
            });
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteListing(btn.dataset.id));
            });
        }, 0); // Use setTimeout with 0 delay to ensure elements are in DOM
        return listingsHtml;
    }, 'No listings found.');
}

async function verifyListing(id) {
    // A proper confirm modal would be better, but for now, window.confirm is used.
    if(!confirm('Verify this listing?')) return;
    try {
        await apiClient.request(`/admin/listings/verify/${id}`, 'POST');
        showNotification('Listing verified!', 'success');
        loadListings();
    } catch (error) {
        showNotification('Action failed', 'error');
    }
}

async function deleteListing(id) {
    if(!confirm('Are you sure you want to delete this listing?')) return;
    try {
        await apiClient.request(`/admin/listings/${id}`, 'DELETE');
        showNotification('Listing deleted!', 'success');
        loadListings();
    } catch (error) {
        showNotification('Action failed', 'error');
    }
}

async function loadUsers() {
    await loadContentIntoContainer('adminUsersTable', async () => {
        return await apiClient.request('/admin/users');
    }, (users) => {
        const usersHtml = users.map(user => `
            <tr>
                <td class="ps-4 fw-bold">${user.name}</td>
                <td>${user.email}</td>
                <td>
                    <select class="form-select form-select-sm role-select" data-user-id="${user.id}">
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
        `).join('');

        // Attach event listeners after content is rendered
        setTimeout(() => {
            document.querySelectorAll('.delete-user-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteUser(btn.dataset.id));
            });

            document.querySelectorAll('.role-select').forEach(select => {
                select.addEventListener('change', (e) => {
                    const userId = e.target.dataset.userId;
                    const newRole = e.target.value;
                    updateUserRole(userId, newRole);
                });
            });
        }, 0); // Use setTimeout with 0 delay to ensure elements are in DOM
        return usersHtml;
    }, 'No users found.');
}

async function updateUserRole(userId, newRole) {
    if (!confirm(`Are you sure you want to change this user's role to ${newRole}?`)) {
        loadUsers(); // Re-load to reset the dropdown if the user cancels
        return;
    }
    try {
        await apiClient.request(`/admin/users/${userId}/role`, 'POST', { role: newRole });
        showNotification('User role updated successfully!', 'success');
        loadUsers(); // Refresh the user list
    } catch (error) {
        showNotification('Failed to update user role.', 'error');
        loadUsers(); // Re-load to reset the dropdown on failure
    }
}

async function deleteUser(id) {
    if(!confirm('Are you sure you want to delete this user?')) return;
    try {
        await apiClient.request(`/admin/users/${id}`, 'DELETE');
        showNotification('User deleted!', 'success');
        loadUsers();
    } catch (error) {
        showNotification('Action failed', 'error');
    }
}