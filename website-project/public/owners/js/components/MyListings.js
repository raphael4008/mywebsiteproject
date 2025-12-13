import apiClient from '../../../admin/js/services/apiClient.js'; // Corrected path

export default {
    template: `
        <div>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-3xl font-semibold text-gray-700">My Listings</h3>
                <router-link :to="{ name: 'AddListing' }" class="btn-primary px-6 py-2">
                    + Add New Listing
                </router-link>
            </div>

            <div v-if="isLoading" class="text-center p-8">Loading your listings...</div>
            <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ error }}</span>
            </div>
            <div v-else class="bg-white p-8 rounded-md shadow-md">
                <div v-if="listings.length === 0" class="text-center text-gray-500">
                    <p>You haven't added any listings yet.</p>
                    <p>Click the 'Add New Listing' button to get started!</p>
                </div>
                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listing</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent (KES)</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="listing in listings" :key="listing.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ listing.title }}</div>
                                <div class="text-sm text-gray-500">{{ listing.city }}, {{ listing.neighborhood.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="listing.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ listing.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Number(listing.rent_amount).toLocaleString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <router-link :to="{ name: 'EditListing', params: { id: listing.id } }" class="text-indigo-600 hover:text-indigo-900">Edit</router-link>
                                <button @click="deleteListing(listing.id)" class="ml-4 text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
            this.error = '';
            try {
                const response = await apiClient.request('/owners/me/listings');
                this.listings = response.data;
            } catch (err) {
                this.error = err.message || 'Failed to fetch your listings.';
            } finally {
                this.isLoading = false;
            }
        },
        async deleteListing(id) {
            if (!confirm('Are you sure you want to delete this listing? This action cannot be undone.')) {
                return;
            }
            try {
                await apiClient.request(`/listings/${id}`, 'DELETE');
                await this.fetchListings(); // Refresh the list
            } catch (err) {
                this.error = err.message || 'Failed to delete the listing.';
            }
        }
    }
};