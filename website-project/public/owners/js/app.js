import router from './router.js';

const App = {
    template: `
        <div id="owner-portal">
            <router-view></router-view>
        </div>
    `
};

Vue.createApp(App).use(router).mount('#app');






