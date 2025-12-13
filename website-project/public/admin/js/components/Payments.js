import api from '../services/api.js';

export default {
  data() {
    return {
      payments: [],
    };
  },
  async created() {
    this.fetchPayments();
  },
  template: `
    <div>
      <h2 class="text-2xl font-bold">Manage Payments</h2>
      <div class="bg-white p-4 rounded-lg shadow mt-4">
        <table class="w-full">
          <thead>
            <tr>
              <th class="px-4 py-2">Reservation ID</th>
              <th class="px-4 py-2">Amount</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in payments" :key="payment.id">
              <td class="border px-4 py-2">{{ payment.reservation_id }}</td>
              <td class="border px-4 py-2">{{ formatCurrency(payment.amount) }}</td>
              <td class="border px-4 py-2"><span :class="statusClass(payment.status)" class="py-1 px-3 rounded-full text-xs">{{ payment.status }}</span></td>
              <td class="border px-4 py-2">
                <button v-if="payment.status === 'pending'" @click="approvePayment(payment.id)" class="bg-green-500 text-white px-2 py-1 rounded">Approve</button>
                <button v-if="payment.status === 'pending'" @click="rejectPayment(payment.id)" class="bg-red-500 text-white px-2 py-1 rounded">Reject</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  `,
  methods: {
    async fetchPayments() {
        try {
            const data = await api.get('/admin/payments');
            this.payments = data.payments;
        } catch (error) {
            console.error('Error fetching payments:', error);
        }
    },
    async approvePayment(id) {
      try {
        await api.post(`/admin/payments/${id}/approve`);
        this.fetchPayments();
      } catch (error) {
        console.error('Error approving payment:', error);
      }
    },
    async rejectPayment(id) {
      try {
        await api.post(`/admin/payments/${id}/reject`);
        this.fetchPayments();
      } catch (error) {
        console.error('Error rejecting payment:', error);
      }
    },
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
    },
    statusClass(status) {
        switch (status) {
            case 'pending':
                return 'bg-yellow-200 text-yellow-800';
            case 'approved':
                return 'bg-green-200 text-green-800';
            case 'rejected':
                return 'bg-red-200 text-red-800';
            default:
                return 'bg-gray-200 text-gray-800';
        }
    }
  },
};
