import apiClient from '../services/apiClient.js'; // Corrected path

export default {
    template: `
        <div>
            <h3 class="text-3xl font-semibold text-gray-700">Listing Management</h3>

            <div class="mt-8">
                <div v-if="isLoading" class="text-center p-8">Loading listings...</div>
                <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ error }}</span>
                </div>
                <div v-else class="bg-white p-8 rounded-md shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listing</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="listing in listings" :key="listing.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ listing.title }}</div>
                                    <div class="text-sm text-gray-500">{{ listing.city }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ listing.owner.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="listing.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                        {{ listing.is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button v-if="!listing.is_published" @click="approveListing(listing.id)" class="text-green-600 hover:text-green-900">Approve</button>
                                    <button @click="deleteListing(listing.id)" class="ml-4 text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            listings: [],
            isLoading: true,
            error: ''
        };
    },
    async mounted() {
        await this.fetchListings();
    },
    methods: {
        async fetchListings() {
            this.isLoading = true;
            try {
                // Backend exposes listings at GET /listings (returns { data: [...], total })
                const resp = await apiClient.get('/listings');
                this.listings = resp && resp.data ? resp.data : resp;
            } catch (err) {
                this.error = 'Failed to load listings.';
            } finally {
                this.isLoading = false;
            }
        },
        async approveListing(id) {
            try {
                // Server exposes verification at POST /listings/:id/verify
                await apiClient.post(`/listings/${id}/verify`);
                await this.fetchListings(); // Refresh list
            } catch (err) {
                this.error = 'Failed to approve listing.';
            }
        },
        async deleteListing(id) {
            if (!confirm('Are you sure you want to permanently delete this listing?')) return;
            try {
                await apiClient.request(`/listings/${id}`, { method: 'DELETE' });
                await this.fetchListings(); // Refresh list
            } catch (err) {
                this.error = 'Failed to delete listing.';
            }
        }
    }
};