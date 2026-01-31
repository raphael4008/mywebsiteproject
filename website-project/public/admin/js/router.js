import Login from './components/Login.js';
import Dashboard from './components/Dashboard.js';
import AdminDashboard from './components/AdminDashboard.js';
import Users from './components/Users.js';
import Listings from './components/Listings.js';
import EditUser from './components/EditUser.js';

const routes = [
    { path: '/login', component: Login, name: 'Login' },
    { 
        path: '/', 
        component: Dashboard,
        meta: { requiresAuth: true },
        children: [
            { path: '', component: AdminDashboard, name: 'AdminDashboard' },
            { path: 'users', component: Users, name: 'Users' },
            { path: 'users/:id/edit', component: EditUser, name: 'EditUser' },
            { path: 'listings', component: Listings, name: 'Listings' },
        ]
    },
];

const router = VueRouter.createRouter({
    history: VueRouter.createWebHashHistory(),
    routes,
});

// router.beforeEach((to, from, next) => {
//     const loggedIn = localStorage.getItem('token');
//     const tokenPayload = loggedIn ? JSON.parse(atob(loggedIn.split('.')[1])) : null;
//     const userRole = tokenPayload ? tokenPayload.data.role : null;

//     if (to.matched.some(record => record.meta.requiresAuth) && userRole !== 'admin') {
//         next({ name: 'Login' });
//     } else if (to.name === 'Login' && userRole === 'admin') {
//         next({ name: 'AdminDashboard' });
//     } else {
//         next();
//     }
// });

export default router;