import apiClient from '../services/apiClient.js';

export default {
    template: `
        <div>
            <h3 class="text-3xl font-semibold text-gray-700">User Management</h3>

            <div class="mt-8">
                <div v-if="isLoading" class="text-center p-8">Loading users...</div>
                <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ error }}</span>
                </div>
                <div v-else class="bg-white p-8 rounded-md shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="roleClass(user.role)">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <router-link :to="{ name: 'EditUser', params: { id: user.id } }" class="text-indigo-600 hover:text-indigo-900">Edit</router-link>
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
            users: [],
            isLoading: true,
            error: ''
        };
    },
    async mounted() {
        this.isLoading = true;
        try {
            this.users = await apiClient.request('/admin/users');
        } catch (err) {
            this.error = 'Failed to load user data.';
        } finally {
            this.isLoading = false;
        }
    },
    methods: {
        roleClass(role) {
            if (role === 'admin') return 'bg-red-100 text-red-800';
            if (role === 'owner') return 'bg-blue-100 text-blue-800';
            return 'bg-gray-100 text-gray-800';
        }
    }
};