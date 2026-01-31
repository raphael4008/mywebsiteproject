import apiClient from './apiClient.js';
import { formatCurrency, getImageUrl } from './utils.js';
import { getFavoriteButtonHtml, toggleFavorite } from './wishlist.js';

function createListingCard(listing) {
    const card = document.createElement('div');
    card.classList.add('col-md-4', 'mb-4');

    const imageCarouselHtml = `<img src="${getImageUrl(listing.images && listing.images[0])}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${listing.title}">`;

    card.innerHTML = `
        <div class="card h-100 shadow-sm border-0">
            <a href="listing-details.php?id=${listing.id}" class="text-decoration-none text-dark">
                <div class="position-relative">
                    ${imageCarouselHtml}
                    ${getFavoriteButtonHtml(listing.id, listing.is_favorite)}
                </div>
                <div class="card-body">
                    <h5 class="card-title text-truncate">${listing.title}</h5>
                    <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city}</p>
                    <p class="h6 text-primary fw-bold mb-0">${formatCurrency(listing.rent_amount)}</p>
                </div>
            </a>
        </div>
    `;
    card.querySelector('.favorite-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleFavorite(e.currentTarget, listing.id);
    });
    return card;
}


async function renderAgentListings(agentId) {
    const container = document.getElementById('agent-listings-container');
    if (!container) return;

    try {
        const response = await apiClient.request(`/listings/search?agent_id=${agentId}`);
        const listings = response.data;

        if (listings.length > 0) {
            listings.forEach(listing => {
                container.appendChild(createListingCard(listing));
            });
        } else {
            container.innerHTML = '<p class="text-muted">This agent has no active listings.</p>';
        }
    } catch (error) {
        container.innerHTML = `<p class="alert alert-danger">Error loading listings: ${error.message}</p>`;
    }
}

function renderAgentProfile(agent) {
    const container = document.getElementById('agent-profile-content');
    if (!container) return;

    container.innerHTML = `
        <div class="col-md-4 text-center">
            <img src="${getImageUrl(agent.profile_pic, 'images/placeholder.svg')}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" alt="Agent ${agent.name}">
        </div>
        <div class="col-md-8">
            <h1 class="display-5 fw-bold">${agent.name}</h1>
            <p class="lead text-muted">${agent.bio || 'A dedicated real estate professional.'}</p>
            <div class="d-flex flex-wrap">
                <a href="tel:${agent.phone}" class="btn btn-outline-primary me-2 mb-2"><i class="fas fa-phone-alt me-2"></i>Call Now</a>
                <a href="mailto:${agent.email}" class="btn btn-outline-secondary mb-2"><i class="fas fa-envelope me-2"></i>Send Email</a>
            </div>
        </div>
    `;
}

async function initAgentProfilePage(agentId) {
    const content = document.getElementById('agent-profile-content');
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    try {
        const agent = await apiClient.request(`/agents/${agentId}`);
        renderAgentProfile(agent);
        await renderAgentListings(agentId);
    } catch (error) {
        content.innerHTML = `<p class="alert alert-danger">Error loading agent profile: ${error.message}</p>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const agentId = params.get('id');

    if (agentId) {
        initAgentProfilePage(agentId);
    } else {
        document.body.innerHTML = '<div class="alert alert-danger">No agent ID provided.</div>';
    }
});
