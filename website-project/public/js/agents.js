import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const agentList = document.getElementById('agent-list');

    async function loadAgents() {
        if (!agentList) return;

        try {
            const agents = await apiClient.request('/agents');

            if (!agents || agents.length === 0) {
                agentList.innerHTML = '<p>No agents found.</p>';
                return;
            }

            agentList.innerHTML = agents.map(agent => `
                <div class="col-md-3 col-sm-6" data-aos="fade-up">
                    <div class="card agent-card h-100 p-4 text-center shadow-sm">
                        <img src="${agent.image || 'https://via.placeholder.com/150'}" onerror="this.src='https://via.placeholder.com/150'" class="agent-img" alt="Agent">
                        <h5 class="fw-bold">${agent.name}</h5>
                        <p class="text-muted small">${agent.specialty}</p>
                        <div class="text-warning mb-3">${getStarRating(agent.rating)}</div>
                        <a href="agent-profile.html?id=${agent.id}" class="btn btn-outline-primary btn-sm rounded-pill">Contact</a>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error fetching agents:', error);
            agentList.innerHTML = '<p>Error loading agents.</p>';
        }
    }

    function getStarRating(rating) {
        let stars = '';
        for (let i = 0; i < 5; i++) {
            if (rating >= i + 1) {
                stars += '<i class="fas fa-star"></i>';
            } else if (rating >= i + 0.5) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }

    loadAgents();
});
