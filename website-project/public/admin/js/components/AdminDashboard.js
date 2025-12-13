import apiClient from '../services/apiClient.js'; // Corrected path

export default {
    template: `
        <div>
            <h3 class="text-3xl font-semibold text-gray-700">Dashboard</h3>

            <div v-if="isLoading" class="mt-8 text-center p-8">Loading dashboard data...</div>
            <div v-else-if="error" class="mt-8 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ error }}</span>
            </div>
            
            <div v-else class="mt-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-md shadow-md flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 uppercase">Total Users</div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.total_users }}</div>
                        </div>
                        <div class="text-blue-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-md shadow-md flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 uppercase">Total Listings</div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.total_listings }}</div>
                        </div>
                        <div class="text-green-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-md shadow-md flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 uppercase">Total Reservations</div>
                            <div class="text-3xl font-bold text-gray-800">{{ stats.total_reservations }}</div>
                        </div>
                        <div class="text-yellow-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="mt-8 bg-white p-6 rounded-md shadow-md">
                    <h4 class="text-xl font-semibold text-gray-700">Recent Activity</h4>
                    <p class="text-gray-500 mt-2">A feed of recent user sign-ups and new listings will be displayed here.</p>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            stats: {},
            isLoading: true,
            error: ''
        };
    },
    async mounted() {
        this.isLoading = true;
        try {
            this.stats = await apiClient.request('/admin/stats');
        } catch (err) {
            this.error = 'Failed to load dashboard statistics.';
        } finally {
            this.isLoading = false;
        }
    }
};