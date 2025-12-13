import api from '../services/api.js';

export default {
  data() {
    return {
      reservations: [],
    };
  },
  async created() {
    try {
      const data = await api.get('/admin/reservations');
      this.reservations = data.reservations;
    } catch (error) {
      console.error('Error fetching reservations:', error);
    }
  },
  template: `
    <div>
      <h2 class="text-2xl font-bold">Manage Reservations</h2>
      <div class="bg-white p-4 rounded-lg shadow mt-4">
        <table class="w-full">
          <thead>
            <tr>
              <th class="px-4 py-2">Listing</th>
              <th class="px-4 py-2">User</th>
              <th class="px-4 py-2">Dates</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="reservation in reservations" :key="reservation.id">
              <td class="border px-4 py-2">{{ reservation.listing.title }}</td>
              <td class="border px-4 py-2">{{ reservation.user.name }}</td>
              <td class="border px-4 py-2">{{ reservation.start_date }} to {{ reservation.end_date }}</td>
              <td class="border px-4 py-2"><span :class="statusClass(reservation.status)" class="py-1 px-3 rounded-full text-xs">{{ reservation.status }}</span></td>
              <td class="border px-4 py-2">
                <button v-if="reservation.status === 'pending'" @click="confirmReservation(reservation.id)" class="bg-green-500 text-white px-2 py-1 rounded">Confirm</button>
                <button @click="cancelReservation(reservation.id)" class="bg-red-500 text-white px-2 py-1 rounded">Cancel</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  `,
  methods: {
    async confirmReservation(id) {
      try {
        await api.post(`/admin/reservations/${id}/confirm`);
        this.fetchReservations();
      } catch (error) {
        console.error('Error confirming reservation:', error);
      }
    },
    async cancelReservation(id) {
      try {
        await api.post(`/admin/reservations/${id}/cancel`);
        this.fetchReservations();
      } catch (error) {
        console.error('Error canceling reservation:', error);
      }
    },
    async fetchReservations() {
        try {
            const data = await api.get('/admin/reservations');
            this.reservations = data.reservations;
        } catch (error) {
            console.error('Error fetching reservations:', error);
        }
    },
    statusClass(status) {
        switch (status) {
            case 'pending':
                return 'bg-yellow-200 text-yellow-800';
            case 'confirmed':
                return 'bg-green-200 text-green-800';
            case 'cancelled':
                return 'bg-red-200 text-red-800';
            default:
                return 'bg-gray-200 text-gray-800';
        }
    }
  },
};
