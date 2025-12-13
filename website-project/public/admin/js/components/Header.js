export default {
  template: `
    <header class="flex justify-between items-center bg-white py-4 px-6 border-b-4 border-indigo-600">
      <div class="flex items-center">
        <button @click="$emit('sidebarToggle')" class="text-gray-500 focus:outline-none">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </button>
        <h1 class="text-gray-800 text-xl font-bold ml-3">Admin Dashboard</h1>
      </div>
      <div class="flex items-center">
        <!-- User profile or other header elements can go here -->
        <span class="mr-2 text-gray-600">Admin User</span>
        <button class="flex items-center focus:outline-none">
          <img class="h-8 w-8 rounded-full object-cover" src="https://via.placeholder.com/150" alt="Admin Avatar">
        </button>
      </div>
    </header>
  `
};