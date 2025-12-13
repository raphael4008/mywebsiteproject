import Layout from '../components/Layout.js';
import Dashboard from '../components/Dashboard.js';
import Listings from '../components/Listings.js';
import Users from '../components/Users.js';
import Reservations from '../components/Reservations.js';
import Payments from '../components/Payments.js';
import Transport from '../components/Transport.js';

export default {
  routes: [
    {
      path: '/',
      component: Layout,
      children: [
        {
          path: '',
          component: Dashboard
        },
        {
          path: '/listings',
          component: Listings
        },
        {
          path: '/users',
          component: Users
        },
        {
          path: '/reservations',
          component: Reservations
        },
        {
          path: '/payments',
          component: Payments
        },
        {
          path: '/transport',
          component: Transport
        }
      ]
    }
  ]
};