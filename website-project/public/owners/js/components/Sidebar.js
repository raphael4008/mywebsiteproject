export default {
    template: `
        <div class="portal-sidebar w-64 flex flex-col">
            <div class="logo px-8 py-6 text-2xl font-bold">
                HouseHunter
            </div>
            <nav class="flex-1 px-4 py-4 space-y-2">
                <router-link to="/" class="flex items-center px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                    My Listings
                </router-link>
                <router-link to="/profile" class="flex items-center px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                    Profile
                </router-link>
            </nav>
        </div>
    `
};