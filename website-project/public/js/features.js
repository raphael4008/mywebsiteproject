import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('features-list');
    if (!container) return;

    async function loadFeatures() {
        container.innerHTML = '<div class="text-center py-4">Loading features...</div>';
        try {
            const features = await apiClient.request('/amenities');
            if (!features || features.length === 0) {
                container.innerHTML = '<p class="text-center">No features available.</p>';
                return;
            }

            container.innerHTML = features.map(f => `
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card feature-card p-4 h-100 shadow-sm">
                        <div class="d-flex align-items-center mb-3">
                            <div class="feature-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:56px;height:56px">${f.icon?`<i class="${f.icon}"></i>`:'<i class="fas fa-star"></i>'}</div>
                            <h5 class="mb-0 fw-bold">${f.name}</h5>
                        </div>
                        <p class="text-muted small">${f.description || ''}</p>
                        ${f.tags?`<div class="mt-auto"><small class="text-muted">${f.tags.split(',').map(t=>`<span class="badge bg-light text-muted me-1">${t.trim()}</span>`).join('')}</small></div>`:''}
                    </div>
                </div>
            `).join('');
        } catch (e) {
            console.error('Failed to load features', e);
            container.innerHTML = '<p class="text-danger">Failed to load features.</p>';
        }
    }

    loadFeatures();

    const mortgageCalculator = document.getElementById('mortgage-calculator');
    if (mortgageCalculator) {
        mortgageCalculator.addEventListener('submit', function(e) {
            e.preventDefault();

            const loanAmount = parseFloat(document.getElementById('loan-amount').value);
            const interestRate = parseFloat(document.getElementById('interest-rate').value) / 100 / 12;
            const loanTerm = parseFloat(document.getElementById('loan-term').value) * 12;
            const resultContainer = document.getElementById('mortgage-result');

            if (isNaN(loanAmount) || isNaN(interestRate) || isNaN(loanTerm) || loanAmount <= 0 || interestRate <= 0 || loanTerm <= 0) {
                resultContainer.innerHTML = `<div class="card bg-danger text-white p-4"><p>Please enter valid positive numbers for all fields.</p></div>`;
                return;
            }

            const monthlyPayment = (loanAmount * interestRate * Math.pow(1 + interestRate, loanTerm)) / (Math.pow(1 + interestRate, loanTerm) - 1);

            if (isFinite(monthlyPayment)) {
                resultContainer.innerHTML = `
                    <div class="card bg-primary text-white p-4 text-center">
                        <h4 class="fw-bold">Estimated Monthly Payment</h4>
                        <p class="display-5 fw-bold mb-0">Ksh ${monthlyPayment.toLocaleString('en-US', { maximumFractionDigits: 0 })}</p>
                    </div>
                `;
            } else {
                resultContainer.innerHTML = `<div class="card bg-danger text-white p-4"><p>Could not calculate payment. Please check your values.</p></div>`;
            }
        });
    }
});
