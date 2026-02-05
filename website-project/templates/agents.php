<header class="bg-primary text-white py-5 text-center"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ basePath }}/images/c.jpg') center/cover;">
    <div class="container">
        <h1 class="display-4 fw-bold">Meet Our Expert Agents</h1>
        <p class="lead">Professional, knowledgeable, and ready to help you find your next home.</p>
    </div>
</header>

<div class="container py-5">
    <div id="agents-container" class="row">
        <div class="text-center w-100">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import apiClient from '{{ basePath }}/js/apiClient.js';
    import { showNotification } from '{{ basePath }}/js/utils.js';

    const agentsContainer = document.getElementById('agents-container');

    async function loadAgents() {
        try {
            const agents = await apiClient.request('/agents');
            renderAgents(agents);
        } catch (error) {
            showNotification('Could not load agents.', 'error');
            agentsContainer.innerHTML = '<p class="text-danger">Could not load agents.</p>';
        }
    }

    function renderAgents(agents) {
        if (!agents || agents.length === 0) {
            agentsContainer.innerHTML = '<p class="text-center">No agents found.</p>';
            return;
        }

        agentsContainer.innerHTML = agents.map(agent => `
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <a href="agent-profile?id=${agent.id}">
                                <img src="${agent.profile_pic || 'images/placeholder.svg'}" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;" alt="Agent ${agent.name}">
                            </a>
                            <h5 class="card-title fw-bold">
                                <a href="agent-profile?id=${agent.id}" class="text-decoration-none text-dark">${agent.name}</a>
                            </h5>
                            <p class="card-text text-muted">${agent.email}</p>
                            <a href="agent-profile?id=${agent.id}" class="btn btn-primary">View Profile</a>
                        </div>
                    </div>
                </div>
            `).join('');
    }

    loadAgents();
</script>