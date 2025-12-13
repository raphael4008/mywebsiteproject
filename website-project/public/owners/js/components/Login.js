import apiClient from '../../../admin/js/services/apiClient.js';

export default {
    template: `
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Owner Portal Sign In
                    </h2>
                </div>
                <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="email-address" class="sr-only">Email address</label>
                            <input id="email-address" v-model="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Email address">
                        </div>
                        <div>
                            <label for="password" class="sr-only">Password</label>
                            <input id="password" v-model="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Password">
                        </div>
                    </div>
                    <div v-if="error" class="text-red-500 text-sm text-center">{{ error }}</div>
                    <div>
                        <button type="submit" class="btn-primary group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm">
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `,
    data() {
        return {
            email: '',
            password: '',
            error: ''
        };
    },
    methods: {
        async handleLogin() {
            this.error = '';
            try {
                const data = await apiClient.request('/login', 'POST', { email: this.email, password: this.password });
                const tokenPayload = JSON.parse(atob(data.token.split('.')[1]));
                
                if (tokenPayload.data.role !== 'owner') {
                    this.error = 'Access denied. Only owners can log in here.';
                    return;
                }

                localStorage.setItem('token', data.token);
                this.$router.push({ name: 'MyListings' });
            } catch (err) {
                this.error = err.message || 'Login failed. Please check your credentials.';
            }
        }
    }
};