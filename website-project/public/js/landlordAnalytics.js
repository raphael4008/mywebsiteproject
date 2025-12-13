import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    const analyticsContainer = document.getElementById('analyticsContainer');

    if (!analyticsContainer) return;

    try {
        const analyticsData = await apiClient.request('/landlord/analytics');

        if (analyticsData.data) {
            analyticsContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Views</h5>
                                <p class="card-text">${analyticsData.data.totalViews}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Inquiries</h5>
                                <p class="card-text">${analyticsData.data.totalInquiries}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Average Rent</h5>
                                <p class="card-text">KES ${analyticsData.data.averageRent.toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            analyticsContainer.innerHTML = '<p>No analytics data available.</p>';
        }
    } catch (error) {
        console.error('Error loading analytics:', error);
        analyticsContainer.innerHTML = '<p>Failed to load analytics. Please try again later.</p>';
    }
});