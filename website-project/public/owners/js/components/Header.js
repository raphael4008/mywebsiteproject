const Header = {
    template: `
        <header class="flex items-center justify-between px-6 py-4 bg-white border-b-4 border-indigo-600">
            <div class="flex items-center">
                <button @click="$emit('toggle-sidebar')" class="text-gray-500 focus:outline-none lg:hidden">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
            </div>

            <div class="flex items-center">
                <div class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center text-gray-700 focus:outline-none">
                        <img class="h-8 w-8 rounded-full object-cover" :src="owner.avatar" :alt="owner.name">
                        <span class="mx-2">{{ owner.name }}</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 15.713L18.707 9.00699L17.293 7.59299L12 12.886L6.70703 7.59299L5.29303 9.00699L12 15.713Z" fill="currentColor"></path>
                        </svg>
                    </button>

                    <div v-show="dropdownOpen" @click="dropdownOpen = false" class="fixed inset-0 z-10 w-full h-full"></div>

                    <div v-show="dropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-md overflow-hidden shadow-xl z-20">
                        <router-link to="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-600 hover:text-white">Profile</router-link>
                        <a @click.prevent="logout" href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-600 hover:text-white">Logout</a>
                    </div>
                </div>
            </div>
        </header>
    `,
    data() {
        return {
            dropdownOpen: false,
            owner: {
                name: 'Owner Name',
                avatar: 'https://images.unsplash.com/photo-1590031905470-a138928b9676?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=334&q=80',
            },
        };
    },
    mounted() {
        this.fetchOwner();
    },
    methods: {
        async fetchOwner() {
            try {
                const response = await fetch('/api/owner');
                const data = await response.json();
                this.owner = data;
            } catch (error) {
                console.error('Error fetching owner data:', error);
            }
        },
        async logout() {
            try {
                await fetch('/api/logout', { method: 'POST' });
                window.location.href = '/login.html';
            } catch (error) {
                console.error('Error logging out:', error);
            }
        },
    },
};

export default Header;