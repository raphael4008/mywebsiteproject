import Sidebar from './Sidebar.js';

export default {
    components: { Sidebar },
    template: `
        <div class="flex h-screen font-roboto">
            <Sidebar />
            <div class="flex-1 flex flex-col overflow-hidden">
                <header class="flex justify-between items-center p-6 bg-surface border-b border-border-color">
                    <h1 class="text-2xl font-semibold text-gray-700">Admin Dashboard</h1>
                    <button @click="logout" class="btn-danger px-4 py-2 text-sm">
                        Logout
                    </button>
                </header>
                <main class="flex-1 overflow-x-hidden overflow-y-auto">
                    <div class="container mx-auto px-6 py-8">
                        <router-view></router-view>
                    </div>
                </main>
            </div>
        </div>
    `,
    methods: {
        logout() {
            localStorage.removeItem('token');
            this.$router.push({ name: 'Login' });
        }
    }
};