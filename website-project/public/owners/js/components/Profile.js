import apiClient from '../../../admin/js/services/apiClient.js'; // Corrected path

export default {
    template: `
        <div>
            <h3 class="text-3xl font-semibold text-gray-700">My Profile</h3>

            <div class="mt-8">
                <div class="bg-white p-8 rounded-md shadow-md">
                    <div v-if="isLoading" class="text-center">Loading profile...</div>
                    <form v-else @submit.prevent="handleSubmit">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" v-model="user.name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" v-model="user.email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input type="password" v-model="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Leave blank to keep current password">
                            </div>
                        </div>

                        <div v-if="error" class="mt-4 text-red-500 text-sm text-center">{{ error }}</div>
                        <div v-if="success" class="mt-4 text-green-500 text-sm text-center">{{ success }}</div>

                        <div class="mt-6 text-right">
                            <button type="submit" :disabled="isSubmitting" class="btn-primary inline-flex justify-center py-2 px-8 border border-transparent shadow-sm text-sm font-medium rounded-md" :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }">
                                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `,
    data() {
        return { user: {}, password: '', isLoading: true, isSubmitting: false, error: '', success: '' };
    },
    async mounted() {
        this.isLoading = true;
        try {
            this.user = await apiClient.request('/users/me');
        } catch (err) {
            this.error = 'Failed to load profile data.';
        } finally {
            this.isLoading = false;
        }
    },
    methods: {
        async handleSubmit() {
            this.isSubmitting = true;
            this.error = '';
            this.success = '';
            const payload = { name: this.user.name, email: this.user.email };
            if (this.password) {
                payload.password = this.password;
            }
            try {
                await apiClient.request('/users/me', 'PUT', payload);
                this.success = 'Profile updated successfully!';
                this.password = ''; // Clear password field
            } catch (err) {
                this.error = err.message || 'An unexpected error occurred.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }
};