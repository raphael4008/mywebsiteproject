import apiClient from '../services/apiClient.js';

export default {
    template: `
        <div>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-3xl font-semibold text-gray-700">Edit User</h3>
                <router-link :to="{ name: 'Users' }" class="text-indigo-600 hover:text-indigo-900">
                    &larr; Back to Users
                </router-link>
            </div>

            <div v-if="isLoading" class="text-center p-8">Loading user details...</div>
            <div v-else class="mt-8">
                <div class="bg-white p-8 rounded-md shadow-md">
                    <form @submit.prevent="handleSubmit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name (Read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <p class="mt-1 text-lg text-gray-900">{{ user.name }}</p>
                            </div>

                            <!-- Email (Read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-lg text-gray-900">{{ user.email }}</p>
                            </div>

                            <!-- Role -->
                            <div class="md:col-span-2">
                                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                <select v-model="user.role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="user">User</option>
                                    <option value="owner">Owner</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="error" class="mt-4 text-red-500 text-sm text-center">{{ error }}</div>

                        <div class="mt-6 text-right">
                            <button type="submit" :disabled="isSubmitting" class="btn-primary inline-flex justify-center py-2 px-8 border border-transparent shadow-sm text-sm font-medium rounded-md" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }">
                                {{ isSubmitting ? 'Saving...' : 'Update User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `,
    data() {
        return { user: {}, isLoading: true, isSubmitting: false, error: '' };
    },
    async mounted() {
        this.isLoading = true;
        const userId = this.$route.params.id;
        try {
            this.user = await apiClient.request(`/admin/users/${userId}`);
        } catch (err) {
            this.error = 'Failed to load user data.';
        } finally {
            this.isLoading = false;
        }
    },
    methods: {
        async handleSubmit() {
            this.isSubmitting = true;
            this.error = '';
            try {
                await apiClient.request(`/admin/users/${this.user.id}`, 'PUT', { role: this.user.role });
                this.$router.push({ name: 'Users' });
            } catch (err) {
                this.error = err.message || 'An unexpected error occurred.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }
};