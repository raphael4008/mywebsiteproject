import { api } from '../services/api.js';

const Transport = {
    render: async () => {
        const requests = await api.getTransportRequests();
        let view = `
            <section class="transport-requests">
                <h2>Transport Requests</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Pickup Address</th>
                            <th>Dropoff Address</th>
                            <th>Moving Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${requests.map(request => `
                            <tr>
                                <td>${request.id}</td>
                                <td>${request.name}</td>
                                <td>${request.phone}</td>
                                <td>${request.email}</td>
                                <td>${request.pickup_address}</td>
                                <td>${request.dropoff_address}</td>
                                <td>${request.moving_date}</td>
                                <td>${request.status}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </section>
        `;
        return view;
    },
    after_render: async () => {}
};

export default Transport;