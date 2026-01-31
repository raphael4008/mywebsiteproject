import apiClient from './apiClient.js';
import { formatCurrency, getImageUrl } from './utils.js';

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const agentId = params.get('id');

    if (agentId) {
        loadAgentProfile(agentId);
        loadAgentListings(agentId);
    } else {
        document.getElementById('agent-details-container').innerHTML = '<p class="text-danger text-center">Agent ID not specified.</p>';
    }
});

async function loadAgentProfile(id) {
    const container = document.getElementById('agent-details-container');
    try {
        const agent = await apiClient.request(`/agents/${id}`);
        
        container.innerHTML = `
            <div class="card border-0 shadow-sm overflow-hidden mb-4" data-aos="fade-up">
                <div class="row g-0">
                    <div class="col-md-4 bg-light d-flex align-items-center justify-content-center p-4">
                        <img src="${agent.image || 'images/avatar1.jpg'}" onerror="this.src='https://via.placeholder.com/300'" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover;" alt="${agent.name}">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="fw-bold mb-2">${agent.name}</h2>
                            <p class="text-primary fw-bold mb-3">${agent.specialization || 'Real Estate Agent'}</p>
                            <div class="mb-3 text-warning">
                                ${getStarRating(agent.rating)} <span class="text-muted text-small">(${agent.review_count || 0} reviews)</span>
                            </div>
                            <p class="text-muted mb-4">${agent.bio || 'Experienced agent dedicated to helping you find your perfect home.'}</p>
                            
                            <div class="d-flex gap-3">
                                <a href="mailto:${agent.email}" class="btn btn-primary"><i class="fas fa-envelope me-2"></i> Email Agent</a>
                                <a href="tel:${agent.phone}" class="btn btn-outline-secondary"><i class="fas fa-phone me-2"></i> Call Agent</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading agent:', error);
        container.innerHTML = '<p class="text-danger text-center">Failed to load agent profile.</p>';
    }
}

async function loadAgentListings(id) {
    const container = document.getElementById('agent-listings');
    try {
        const listings = await apiClient.request(`/listings?agent_id=${id}`);
        
        if (!listings || listings.length === 0) {
            container.innerHTML = '<div class="col-12"><p class="text-muted">No active listings found for this agent.</p></div>';
            return;
        }

        container.innerHTML = listings.map(listing => `
            <div class="col-md-4" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm listing-card">
                    <div class="position-relative">
                        <img src="${getImageUrl(listing.images && listing.images[0])}" class="card-img-top" alt="${listing.title}" style="height: 200px; object-fit: cover;">
                        <span class="position-absolute top-0 end-0 m-3 badge bg-primary">${listing.status}</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-truncate">${listing.title}</h5>
                        <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 text-primary mb-0 fw-bold">${formatCurrency(listing.rent_amount)}</span>
                            <a href="listings.php?id=${listing.id}" class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading listings:', error);
        container.innerHTML = '<div class="col-12"><p class="text-danger">Failed to load listings.</p></div>';
    }
}

function getStarRating(rating) {
    const stars = Math.round(rating) || 5;
    return Array(5).fill(0).map((_, i) => i < stars ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>').join('');
}