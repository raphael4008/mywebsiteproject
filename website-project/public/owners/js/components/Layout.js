import Sidebar from './Sidebar.js';
import Header from './Header.js';

const Layout = {
    template: `
        <div class="flex h-screen bg-gray-100">
            <div :class="sidebarOpen ? 'block' : 'hidden'" @click="toggleSidebar" class="fixed z-20 inset-0 bg-black opacity-50 transition-opacity lg:hidden"></div>
            <Sidebar :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="fixed z-30 inset-y-0 left-0 w-64 transition duration-300 transform bg-gray-900 overflow-y-auto lg:translate-x-0 lg:static lg:inset-0" />
            <div class="flex-1 flex flex-col overflow-hidden">
                <Header @toggle-sidebar="toggleSidebar" />
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
                    <div class="container mx-auto px-6 py-8">
                        <router-view></router-view>
                    </div>
                </main>
            </div>
        </div>
    `,
    components: {
        Sidebar,
        Header,
    },
    data() {
        return {
            sidebarOpen: false,
        };
    },
    methods: {
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
    },
};

export default Layout;
