import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('mortgageForm');
    if (!form) return;

    // Try to pre-fill price from listing data
    const urlParams = new URLSearchParams(window.location.search);
    const listingId = urlParams.get('id');
    if (listingId) {
        try {
            const listing = await apiClient.request(`/listings/${listingId}`);
            // Assuming rent_amount * 100 as a rough sale price estimate if sale price isn't available
            // In a real app, you'd have a sale_price field.
            const estimatedPrice = listing.sale_price || (listing.rent_amount * 12 * 15); 
            document.getElementById('mc-price').value = estimatedPrice;
            document.getElementById('mc-down').value = estimatedPrice * 0.2; // 20% down
        } catch (e) {
            console.log('Could not pre-fill mortgage calculator');
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const price = parseFloat(document.getElementById('mc-price').value) || 0;
        const down = parseFloat(document.getElementById('mc-down').value) || 0;
        const rate = parseFloat(document.getElementById('mc-rate').value) || 0;
        const term = parseFloat(document.getElementById('mc-term').value) || 0;

        const principal = price - down;
        const monthlyRate = rate / 100 / 12;
        const numberOfPayments = term * 12;

        const monthlyPayment = (principal * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -numberOfPayments));

        document.getElementById('mc-result').innerHTML = `Monthly Payment: <br>KES ${monthlyPayment.toLocaleString(undefined, {maximumFractionDigits: 0})}`;
    });
});