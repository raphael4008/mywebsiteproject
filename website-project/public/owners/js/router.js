import Login from './components/Login.js';
import Dashboard from './components/Dashboard.js';
import MyListings from './components/MyListings.js';
import Profile from './components/Profile.js';
import AddListing from './components/AddListing.js';
import EditListing from './components/EditListing.js';

const routes = [
    { path: '/login', component: Login, name: 'Login' },
    { 
        path: '/', 
        component: Dashboard,
        meta: { requiresAuth: true },
        children: [
            { path: '', component: MyListings, name: 'MyListings' },
            { path: 'profile', component: Profile, name: 'Profile' },
            { path: 'listings/new', component: AddListing, name: 'AddListing' },
            { path: 'listings/:id/edit', component: EditListing, name: 'EditListing' },
        ]
    },
];

const router = VueRouter.createRouter({
    history: VueRouter.createWebHashHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const loggedIn = localStorage.getItem('token');

    if (to.matched.some(record => record.meta.requiresAuth) && !loggedIn) {
        // Redirect to login if not authenticated
        next({ name: 'Login' });
    } else if (to.name === 'Login' && loggedIn) {
        // If logged in, redirect from login page to dashboard
        next({ name: 'MyListings' });
    } else {
        next(); // Proceed as normal
    }
});

export default router;