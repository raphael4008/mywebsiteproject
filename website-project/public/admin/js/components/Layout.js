import Sidebar from './Sidebar.js';
import Header from './Header.js';

export default {
  components: {
    Sidebar,
    Header,
  },
  template: `
    <div class="flex h-screen bg-gray-100">
      <Sidebar />
      <div class="flex-1 flex flex-col overflow-hidden">
        <Header />
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-4">
          <router-view></router-view>
        </main>
      </div>
    </div>
  `
};
