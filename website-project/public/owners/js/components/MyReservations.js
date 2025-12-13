const MyReservations = {
    template: `
        <h3 class="text-gray-700 text-3xl font-medium">My Reservations</h3>
        <div class="mt-4">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800 mb-4">Upcoming Reservations</h4>
                <ul class="divide-y divide-gray-200">
                    <li v-for="reservation in reservations" :key="reservation.id" class="py-4 flex justify-between items-center">
                        <div>
                            <p class="text-lg font-medium text-gray-900">{{ reservation.listing.title }} - {{ reservation.guest.name }}</p>
                            <p class="text-sm text-gray-500">Dates: {{ reservation.start_date }} to {{ reservation.end_date }}</p>
                        </div>
                        <div class="flex space-x-4">
                            <button @click="viewDetails(reservation.id)" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">View Details</button>
                            <button @click="cancelReservation(reservation.id)" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Cancel</button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    `,
    data() {
        return {
            reservations: [],
        };
    },
    mounted() {
        this.fetchReservations();
    },
    methods: {
        async fetchReservations() {
            try {
                const response = await fetch('/api/owner/reservations');
                const data = await response.json();
                this.reservations = data;
            } catch (error) {
                console.error('Error fetching reservations:', error);
            }
        },
        async cancelReservation(id) {
            try {
                await fetch(`/api/owner/reservations/${id}/cancel`, { method: 'POST' });
                this.fetchReservations();
            } catch (error) {
                console.error('Error canceling reservation:', error);
            }
        },
        viewDetails(id) {
            alert(`View details for reservation ${id} coming soon!`);
        },
    },
};

export default MyReservations;