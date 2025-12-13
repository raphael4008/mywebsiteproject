import { createStore } from 'vuex';

export default createStore({
  state: {
    listings: [],
    users: [],
    reservations: [],
    payments: [],
  },
  mutations: {
    setListings(state, listings) {
      state.listings = listings;
    },
    setUsers(state, users) {
      state.users = users;
    },
    setReservations(state, reservations) {
      state.reservations = reservations;
    },
    setPayments(state, payments) {
      state.payments = payments;
    },
  },
  actions: {
    async fetchListings({ commit }) {
      const response = await fetch('/api/listings');
      const listings = await response.json();
      commit('setListings', listings.data);
    },
    async fetchUsers({ commit }) {
      const response = await fetch('/api/users');
      const users = await response.json();
      commit('setUsers', users.data);
    },
    async fetchReservations({ commit }) {
      const response = await fetch('/api/reservations');
      const reservations = await response.json();
      commit('setReservations', reservations.data);
    },
    async fetchPayments({ commit }) {
      const response = await fetch('/api/payments');
      const payments = await response.json();
      commit('setPayments', payments.data);
    },
  },
  getters: {
    getListings: (state) => state.listings,
    getUsers: (state) => state.users,
    getReservations: (state) => state.reservations,
    getPayments: (state) => state.payments,
  },
});
