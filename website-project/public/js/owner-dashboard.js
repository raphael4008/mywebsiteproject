import apiClient from './apiClient.js';
import { logout } from './auth.js';
import { createListingCard, formatDate } from './utils.js';
import { showNotification } from './utils.js';

const ApiEndpoints = {
    ANALYTICS: '/owner/stats',
    ACTIVITIES: '/owner/activities',
    PROPERTIES: '/owner/my-listings',
    RESERVATIONS: '/owner/reservations',
    LISTINGS: '/listings',
    CITIES: '/cities', 
    HOUSE_TYPES: '/house-types',
    FINANCIALS: '/owner/financials',
    TRANSACTIONS: '/owner/transactions',
    UNAVAILABILITY: '/owner/unavailability',
    MESSAGES: '/owner/messages',
};

const AppState = {
    user: null, // Stores authenticated user data
    cities: [],
    houseTypes: [],
};

// Helper function to populate a select element
function populateSelectElement(selectElement, options, valueKey, textKey, selectedValue = null) {
    if (!selectElement) return;

    selectElement.innerHTML = '<option value="">Select an option</option>'; // Clear existing options
    options.forEach(item => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = item[textKey];
        if (selectedValue && item[valueKey] === selectedValue) {
            option.selected = true;
        }
        selectElement.appendChild(option);
    });
}

// Helper function to populate dropdowns
async function populateDropdown(selectElementId, endpoint, valueKey, textKey, selectedValue = null) {
    const selectElement = document.getElementById(selectElementId);
    if (!selectElement) return;

    selectElement.innerHTML = '<option value="">Loading...</option>';
    try {
        const data = await apiClient.request(endpoint);
        selectElement.innerHTML = '<option value="">Select an option</option>';
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[textKey];
            if (selectedValue && item[valueKey] === selectedValue) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
        if (data.length > 0 && !selectedValue) {
            selectElement.selectedIndex = 0; // Select the first valid option if no selectedValue
        }
        return data; // Return fetched data for potential caching or further use
    } catch (error) {
        console.error(`Failed to load data for ${selectElementId}:`, error);
        selectElement.innerHTML = '<option value="">Failed to load</option>';
        return [];
    }
}


document.addEventListener('DOMContentLoaded', async () => { // Made async
    checkAuth();
    setupNavigation();
    
    // Fetch and store cities and house types globally
    AppState.cities = await populateDropdown('addPropertyForm [name="city"]', ApiEndpoints.CITIES, 'name', 'name');
    AppState.houseTypes = await populateDropdown('addPropertyForm [name="htype"]', ApiEndpoints.HOUSE_TYPES, 'type', 'type');

    setupAddPropertyForm();
    setupEditPropertyForm();

    // Initial load based on URL hash or default to overview
    const initialSection = window.location.hash ? window.location.hash.substring(1) : 'overview';
    showSection(initialSection, false); // Do not push state on initial load

    document.getElementById('logoutBtn').addEventListener('click', (e) => {
        e.preventDefault();
        logout();
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', (event) => {
        const section = event.state ? event.state.section : 'overview';
        showSection(section, false); // Do not push state, as it's already in history
    });
});

function checkAuth() {
    const token = localStorage.getItem('token');
    const userString = localStorage.getItem('user');

    if (!token || !userString) {
        window.location.href = '../login.php?redirect=owners/index.php';
        return;
    }

    try {
        const user = JSON.parse(userString);
        if (user.role !== 'owner') {
            console.warn('User is not an owner. Redirecting.');
            window.location.href = '../login.php?redirect=owners/index.php';
            return; // Added return to prevent further execution
        }
        AppState.user = user; // Store user data in AppState
        // Display owner name
        document.getElementById('ownerNameDisplay').textContent = AppState.user.name || 'Owner';
    } catch (error) {
        console.error('Failed to parse user data, redirecting to login.', error);
        window.location.href = '../login.html?redirect=owners/index.html';
    }
}

function showSection(sectionName, pushState = true) {
    // Validate sectionName against allowed sections
    const allowedSections = ['overview', 'properties', 'reservations', 'financials', 'messages'];
    if (!allowedSections.includes(sectionName)) {
        sectionName = 'overview'; // Default to overview if invalid
    }

    // Update active class on navigation links
    const links = document.querySelectorAll('.sidebar .nav-link[data-section]');
    links.forEach(link => {
        if (link.getAttribute('data-section') === sectionName) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Hide all sections and show the target one
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('d-none'));
    const targetSection = document.getElementById(sectionName + '-section');
    if (targetSection) {
        targetSection.classList.remove('d-none');
    }

    // Update browser history
    if (pushState) {
        history.pushState({ section: sectionName }, '', `#${sectionName}`);
    } else {
        history.replaceState({ section: sectionName }, '', `#${sectionName}`);
    }

    // Load content for the active section
    switch (sectionName) {
        case 'overview':
            loadOverview();
            break;
        case 'properties':
            loadProperties();
            break;
        case 'reservations':
            loadReservations();
            break;
        case 'financials':
            loadFinancials();
            break;
        case 'messages':
            loadMessages();
            break;
    }
}

async function loadFinancials() {
    await loadContentIntoContainer('financials-section', async () => {
        const financials = await apiClient.request('/owner/financials');
        document.getElementById('totalRevenueFinancials').textContent = 'KES ' + Number(financials.totalRevenue || 0).toLocaleString();
        document.getElementById('monthlyEarnings').textContent = 'KES ' + Number(financials.monthlyEarnings || 0).toLocaleString();
        document.getElementById('pendingPayouts').textContent = 'KES ' + Number(financials.pendingPayouts || 0).toLocaleString();
        loadTransactions();
        return financials;
    }, (financials) => {
        return '';
    }, 'No financial data available.');
}

async function loadTransactions() {
    await loadContentIntoContainer('transactionsTable', async () => {
        return await apiClient.request('/owner/transactions');
    }, (transactions) => {
        return transactions.map(transaction => {
            let statusBadge;
            switch (transaction.status.toLowerCase()) {
                case 'completed':
                case 'confirmed':
                    statusBadge = `<span class="badge bg-success">Completed</span>`;
                    break;
                case 'pending':
                    statusBadge = `<span class="badge bg-warning text-dark">Pending</span>`;
                    break;
                case 'failed':
                case 'cancelled':
                    statusBadge = `<span class="badge bg-danger">Failed</span>`;
                    break;
                default:
                    statusBadge = `<span class="badge bg-secondary">${transaction.status}</span>`;
            }

            let typeIcon;
            let formattedType;
            switch (transaction.type.toLowerCase()) {
                case 'mpesa':
                    typeIcon = `<i class="fas fa-mobile-alt me-2 text-success"></i>`;
                    formattedType = 'M-Pesa';
                    break;
                case 'card':
                case 'stripe':
                    typeIcon = `<i class="fas fa-credit-card me-2 text-primary"></i>`;
                    formattedType = 'Card';
                    break;
                default:
                    typeIcon = `<i class="fas fa-dollar-sign me-2 text-muted"></i>`;
                    formattedType = transaction.type;
            }

            return `
                <tr>
                    <td class="ps-4">${new Date(transaction.date).toLocaleDateString()}</td>
                    <td>${typeIcon}${formattedType}</td>
                    <td>${transaction.property || 'N/A'}</td>
                    <td>KES ${Number(transaction.amount).toLocaleString()}</td>
                    <td data-bs-toggle="tooltip" title="Transaction ID: ${transaction.transaction_id || 'N/A'}">${statusBadge}</td>
                </tr>
            `;
        }).join('');
    }, 'No transactions found.');

    // Initialize tooltips after rendering
    setTimeout(() => {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    }, 500);
}


function setupNavigation() {
    const links = document.querySelectorAll('.sidebar .nav-link[data-section]');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionName = link.getAttribute('data-section');
            showSection(sectionName);
        });
    });
}

async function loadContentIntoContainer(containerId, fetchFunction, renderFunction, emptyMessage = 'No data found.') {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container with ID ${containerId} not found.`);
        return;
    }

    container.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    try {
        const data = await fetchFunction();
        if (data.length === 0 || Object.keys(data).length === 0) {
            container.innerHTML = `<div class="col-12 text-center py-3"><p class="text-muted">${emptyMessage}</p></div>`;
        } else {
            container.innerHTML = renderFunction(data);
        }
    } catch (error) {
        if (error.status === 401) {
            console.error('Authentication error. Redirecting to login.');
            logout(); // logout() from auth.js handles clearing storage and redirection
        } else {
            console.error(`Error loading content for ${containerId}:`, error);
            container.innerHTML = `<div class="col-12 text-center py-3"><p class="text-danger">Failed to load content.</p></div>`;
        }
    }
}

async function loadOverview() {
    const statIds = ['totalProperties', 'activeListings', 'totalViews', 'totalRevenue'];

    statIds.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        }
    });

    const eventSource = new EventSource('api/owner/stats/stream');

    eventSource.onmessage = function(event) {
        const stats = JSON.parse(event.data);
        document.getElementById('totalProperties').textContent = stats.totalListings || '0';
        document.getElementById('activeListings').textContent = stats.activeListings || '0';
        document.getElementById('totalViews').textContent = stats.totalViews || '0';
        document.getElementById('totalRevenue').textContent = 'KES ' + Number(stats.totalEarnings || 0).toLocaleString();
    };

    eventSource.onerror = function(err) {
        console.error('EventSource failed:', err);
        eventSource.close();
    };

    loadActivityLog(); // Activity log is part of overview
}

async function loadActivityLog() {
    await loadContentIntoContainer('recentActivityTable', async () => {
        return await apiClient.request(ApiEndpoints.ACTIVITIES);
    }, (activities) => {
        return activities.map(activity => `
            <tr>
                <td class="ps-4">${activity.property}</td>
                <td>${activity.date}</td>
                <td>${activity.action}</td>
                <td><span class="badge bg-secondary">${activity.status}</span></td>
            </tr>
        `).join('');
    }, 'No recent activity.');
}

async function loadProperties() {
    await loadContentIntoContainer('propertiesList', async () => {
        return await apiClient.request(ApiEndpoints.PROPERTIES);
    }, (response) => {
        const { listings, stats } = response.data;
        
        // Update overview stats
        document.getElementById('totalProperties').textContent = stats.total_listings || '0';
        document.getElementById('totalViews').textContent = stats.total_views || '0';

        const html = listings.map(listing => createListingCard(listing, 'owner')).join('');

        // Attach event listeners for edit/delete after DOM insertion
        setTimeout(() => {
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = e.currentTarget.dataset.id;
                    openEditModal(id);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const id = e.currentTarget.dataset.id;
                    if (!confirm('Are you sure you want to delete this property? This action cannot be undone.')) return;
                    try {
                        await apiClient.request(`/listings/${id}`, 'DELETE');
                        showNotification('Property deleted successfully.', 'success');
                        loadProperties();
                    } catch (err) {
                        console.error('Failed to delete property:', err);
                        showNotification('Failed to delete property.', 'error');
                    }
                });
            });

            document.querySelectorAll('.availability-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = e.currentTarget.dataset.id;
                    openAvailabilityModal(id);
                });
            });
        }, 0);

        return html;
    }, 'No properties added yet.');
}

let availabilityCalendar = null;

async function openAvailabilityModal(listingId) {
    const modalElement = document.getElementById('availabilityModal');
    const modal = new bootstrap.Modal(modalElement);
    document.getElementById('unavailabilityListingId').value = listingId;

    if (availabilityCalendar) {
        availabilityCalendar.destroy();
    }

    availabilityCalendar = new VanillaCalendar('#calendar', {
        settings: {
            range: {
                disablePast: true,
            },
            selection: {
                day: 'multiple-ranged',
            },
        },
    });
    availabilityCalendar.init();

    await loadUnavailability(listingId);
    modal.show();
}

async function loadUnavailability(listingId) {
    const listContainer = document.getElementById('unavailabilityList');
    listContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>';
    
    try {
        const unavailableDates = await apiClient.request(`/owner/listings/${listingId}/unavailability`);
        
        const calendarDates = [];
        const listHtml = unavailableDates.map(item => {
            calendarDates.push({
                start: item.start_date,
                end: item.end_date,
                meta: {
                    highlight: true,
                },
            });
            return `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <span>${formatDate(item.start_date)} - ${formatDate(item.end_date)}</span>
                    <button class="btn btn-sm btn-danger delete-unavailability-btn" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </div>
            `;
        }).join('');
        
        listContainer.innerHTML = listHtml || '<p class="text-muted text-center">No dates blocked yet.</p>';
        availabilityCalendar.settings.range.disabled = calendarDates;
        availabilityCalendar.update();

        document.querySelectorAll('.delete-unavailability-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.dataset.id;
                deleteUnavailability(id, listingId);
            });
        });

    } catch (error) {
        console.error('Failed to load unavailability:', error);
        listContainer.innerHTML = '<p class="text-danger text-center">Failed to load data.</p>';
    }
}

async function addUnavailability(listingId, startDate, endDate) {
    try {
        await apiClient.request(`/owner/listings/${listingId}/unavailability`, 'POST', { start_date: startDate, end_date: endDate });
        showNotification('Dates blocked successfully.', 'success');
        await loadUnavailability(listingId);
    } catch (error) {
        console.error('Failed to block dates:', error);
        showNotification('Failed to block dates.', 'error');
    }
}

async function deleteUnavailability(unavailabilityId, listingId) {
    if (!confirm('Are you sure you want to unblock these dates?')) return;
    try {
        await apiClient.request(`/owner/unavailability/${unavailabilityId}`, 'DELETE');
        showNotification('Dates unblocked successfully.', 'success');
        await loadUnavailability(listingId);
    } catch (error) {
        console.error('Failed to unblock dates:', error);
        showNotification('Failed to unblock dates.', 'error');
    }
}

document.getElementById('addUnavailabilityForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const listingId = document.getElementById('unavailabilityListingId').value;
    const startDate = document.getElementById('unavailabilityStartDate').value;
    const endDate = document.getElementById('unavailabilityEndDate').value;
    
    if (listingId && startDate && endDate) {
        await addUnavailability(listingId, startDate, endDate);
    }
});


async function openEditModal(id) {
    try {
        const listing = await apiClient.request(`/listings/${id}`);
        const modalElement = document.getElementById('editPropertyModal');
        const modal = new bootstrap.Modal(modalElement);

        // Pre-fill the form
        document.getElementById('edit-id').value = listing.id;
        document.getElementById('edit-title').value = listing.title;
        document.getElementById('edit-city').value = listing.city;
        document.getElementById('edit-htype').value = listing.htype;
        document.getElementById('edit-rent_amount').value = listing.rent_amount;
        document.getElementById('edit-deposit_amount').value = listing.deposit_amount;
        document.getElementById('edit-description').value = listing.description;

        // Populate and select values for city and house type dropdowns
        populateSelectElement(document.getElementById('edit-city'), AppState.cities, 'name', 'name', listing.city);
        populateSelectElement(document.getElementById('edit-htype'), AppState.houseTypes, 'type', 'type', listing.htype);

        modal.show();
    } catch (error) {
        console.error('Error fetching listing for edit:', error);
        showNotification('Failed to fetch listing details.', 'error');
    }
}

async function loadReservations() {
    await loadContentIntoContainer('reservationsList', async () => {
        return await apiClient.request('/owner/reservations');
    }, (reservations) => {
        if (reservations.length === 0) {
            return '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No reservations found.</div>';
        }
        const tableHtml = `
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Tenant</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${reservations.map(res => {
                        let statusBadge;
                        switch (res.status.toUpperCase()) {
                            case 'CONFIRMED':
                                statusBadge = `<span class="badge bg-success">Confirmed</span>`;
                                break;
                            case 'PENDING':
                                statusBadge = `<span class="badge bg-warning text-dark">Pending</span>`;
                                break;
                            case 'FAILED':
                            case 'CANCELLED':
                                statusBadge = `<span class="badge bg-danger">Failed</span>`;
                                break;
                            default:
                                statusBadge = `<span class="badge bg-secondary">${res.status}</span>`;
                        }
                        return `
                            <tr>
                                <td>${res.listing_title || 'N/A'}</td>
                                <td>${res.tenant_name || 'N/A'}</td>
                                <td>${new Date(res.reservation_date).toLocaleDateString()}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary view-reservation-btn" data-id="${res.id}" title="View Details"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
        // Re-attach event listeners after rendering
        setTimeout(() => {
            document.querySelectorAll('.view-reservation-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const reservationId = e.target.closest('button').dataset.id;
                    showNotification(`Viewing details for reservation ID: ${reservationId} (Not implemented yet)`, 'info');
                });
            });
        }, 0); // Use setTimeout to ensure elements are in DOM
        return tableHtml;
    }, 'No reservations found.');
}

function clearValidationErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.remove();
    });
}

function validateForm(form) {
    let isValid = true;
    clearValidationErrors(form); // Clear previous errors

    const requiredInputs = form.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            feedback.textContent = 'This field is required.';
            input.parentNode.appendChild(feedback);
        }
    });

    // Specific validation for numbers
    form.querySelectorAll('input[type="number"]').forEach(input => {
        if (input.value && isNaN(input.value)) {
            isValid = false;
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            feedback.textContent = 'Please enter a valid number.';
            input.parentNode.appendChild(feedback);
        }
    });

    return isValid;
}


function setupAddPropertyForm() {
    const form = document.getElementById('addPropertyForm');
    const modalElement = document.getElementById('addPropertyModal');
    const modal = new bootstrap.Modal(modalElement);
    const citySelect = form.querySelector('[name="city"]');
    const htypeSelect = form.querySelector('[name="htype"]');

    // Clear validation on modal open
    modalElement.addEventListener('shown.bs.modal', () => {
        form.reset();
        clearValidationErrors(form);
        // Populate dropdowns from AppState
        populateSelectElement(citySelect, AppState.cities, 'name', 'name');
        populateSelectElement(htypeSelect, AppState.houseTypes, 'type', 'type');
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validateForm(form)) {
            showNotification('Please correct the errors in the form.', 'error');
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            const formData = new FormData(form);
            
            // Implement CSRF protection.
            // 1. Generate a CSRF token on the server and add it to a meta tag in your HTML.
            //    <meta name="csrf-token" content="your-token-here">
            // 2. Read the token here and include it in the headers.
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const headers = {};
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            await apiClient.request('/listings', 'POST', formData, headers, true); // true for multipart/form-data
 
            form.reset();
            modal.hide();
            showNotification('Property added successfully!', 'success');
            
            // Refresh just the properties list
            await loadProperties(); 
            
        } catch (error) {
            console.error('Error adding property:', error);
            let errorMessage = 'An unexpected error occurred.';
            if (error.response && error.response.json) { // Assuming apiClient might return a response object with a json method
                const errorData = await error.response.json();
                if (errorData.message) {
                    errorMessage = errorData.message;
                } else if (errorData.errors) { // Handle validation errors from API
                    errorMessage = Object.values(errorData.errors).flat().join('<br>');
                }
            } else if (error.message) {
                errorMessage = error.message;
            }
                        showNotification('Error adding property: ' + errorMessage, 'error');
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Save Property';
                    }
                });
            }
            
            function setupEditPropertyForm() {
                const form = document.getElementById('editPropertyForm');
                const modalElement = document.getElementById('editPropertyModal');
                const modal = new bootstrap.Modal(modalElement);
            
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
            
                    if (!validateForm(form)) {
                        showNotification('Please correct the errors in the form.', 'error');
                        return;
                    }
            
                    const btn = form.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.textContent = 'Saving...';
            
                    try {
                        const formData = new FormData(form);
                        const id = formData.get('id');
                        
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const headers = {};
                        if (csrfToken) {
                            headers['X-CSRF-TOKEN'] = csrfToken;
                        }

                        // NOTE: FormData with PUT in PHP is tricky.
                        // The backend must be adapted to handle multipart/form-data for PUT requests,
                        // or we must serialize the form data differently.
                        // For now, we assume the backend handles it.
                        await apiClient.request(`/listings/${id}`, 'PUT', formData, headers, true); // true for multipart
            
                        form.reset();
                        modal.hide();
                        showNotification('Property updated successfully!', 'success');
                        
                        await loadProperties(); 
                        
                    } catch (error) {
                        console.error('Error updating property:', error);
                        let errorMessage = 'An unexpected error occurred.';
                        // Basic error handling, can be improved
                        if (error.message) {
                            errorMessage = error.message;
                        }
                        showNotification('Error updating property: ' + errorMessage, 'error');
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Save Changes';
                    }
                });
            }

async function loadMessages() {
    await loadContentIntoContainer('conversationsList', async () => {
        return await apiClient.request(ApiEndpoints.MESSAGES);
    }, (messages) => {
        if (messages.length === 0) {
            return '<div class="text-center p-3 text-muted">No messages found.</div>';
        }

        // Group messages by sender
        const conversations = messages.reduce((acc, msg) => {
            if (!acc[msg.sender_id]) {
                acc[msg.sender_id] = {
                    id: msg.sender_id,
                    name: msg.sender_name, // Use sender_name from API
                    messages: [],
                    lastMessage: null,
                    lastMessageDate: null
                };
            }
            acc[msg.sender_id].messages.push(msg);
            if (!acc[msg.sender_id].lastMessageDate || new Date(msg.created_at) > new Date(acc[msg.sender_id].lastMessageDate)) {
                acc[msg.sender_id].lastMessage = msg.message;
                acc[msg.sender_id].lastMessageDate = msg.created_at;
            }
            return acc;
        }, {});

        // Sort conversations by the date of the last message
        const sortedConversations = Object.values(conversations).sort((a, b) => new Date(b.lastMessageDate) - new Date(a.lastMessageDate));

        // Attach event listeners after rendering
        setTimeout(() => {
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const senderId = e.currentTarget.dataset.senderId;
                    const conversation = sortedConversations.find(c => c.id == senderId);
                    showConversation(conversation);
                });
            });
        }, 0);

        return sortedConversations.map(conv => `
            <a href="#" class="list-group-item list-group-item-action conversation-item" data-sender-id="${conv.id}">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${conv.name}</h6>
                    <small>${new Date(conv.lastMessageDate).toLocaleDateString()}</small>
                </div>
                <p class="mb-1 text-truncate">${conv.lastMessage}</p>
            </a>
        `).join('');
    }, 'No messages found.');
}

function showConversation(conversation) {
    document.getElementById('conversationHeader').textContent = `Conversation with ${conversation.name}`;

    const messagesHtml = conversation.messages.map(msg => {
        const isOwner = msg.sender_id !== conversation.id; // Check if the sender is not the other user
        return `
            <div class="d-flex ${isOwner ? 'justify-content-end' : ''} mb-3">
                <div class="card" style="max-width: 75%;">
                    <div class="card-body p-2">
                        <p class="mb-0 small">${msg.message}</p>
                        <small class="text-muted d-block text-end">${new Date(msg.created_at).toLocaleString()}</small>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    document.getElementById('conversationMessages').innerHTML = messagesHtml;
    
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.querySelector('#sendMessageForm button');
    
    messageInput.disabled = false;
    sendMessageBtn.disabled = false;
    
    // You would set up the form submission logic here
    document.getElementById('sendMessageForm').onsubmit = (e) => {
        e.preventDefault();
        const messageText = messageInput.value;
        if (!messageText.trim()) return;
        
        // This part is not fully implemented:
        // You would need an API endpoint to send a message
        console.log(`Sending message to ${conversation.name}: ${messageText}`);
        
        messageInput.value = ''; // Clear input
        
        // For demonstration, you could add the message to the UI directly
        // but it's better to refetch or get confirmation from the server.
    };
}
            