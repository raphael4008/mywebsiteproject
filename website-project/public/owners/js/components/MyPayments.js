const MyPayments = {
    template: `
        <h3 class="text-gray-700 text-3xl font-medium">My Payments</h3>
        <div class="mt-4">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800 mb-4">Payment History</h4>
                <ul class="divide-y divide-gray-200">
                    <li v-for="payment in payments" :key="payment.id" class="py-4 flex justify-between items-center">
                        <div>
                            <p class="text-lg font-medium text-gray-900">Payment from {{ payment.guest.name }}</p>
                            <p class="text-sm text-gray-500">Amount: {{ formatCurrency(payment.amount) }} - Date: {{ payment.date }}</p>
                        </div>
                        <span :class="statusClass(payment.status)" class="px-3 py-1 text-sm font-semibold rounded-full">{{ payment.status }}</span>
                    </li>
                </ul>
            </div>
        </div>
    `,
    data() {
        return {
            payments: [],
        };
    },
    mounted() {
        this.fetchPayments();
    },
    methods: {
        async fetchPayments() {
            try {
                const response = await fetch('/api/owner/payments');
                const data = await response.json();
                this.payments = data;
            } catch (error) {
                console.error('Error fetching payments:', error);
            }
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            }).format(amount);
        },
        statusClass(status) {
            switch (status) {
                case 'Completed':
                    return 'text-green-800 bg-green-200';
                case 'Pending':
                    return 'text-yellow-800 bg-yellow-200';
                case 'Failed':
                    return 'text-red-800 bg-red-200';
                default:
                    return 'text-gray-800 bg-gray-200';
            }
        },
    },
};

export default MyPayments;