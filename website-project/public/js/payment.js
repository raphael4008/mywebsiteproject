import apiClient from './apiClient.js';

async function populateListingSummary() {
    const summaryContainer = document.getElementById('listingSummary');
    if (!summaryContainer) return;

    summaryContainer.innerHTML = '<p>Loading listing details...</p>';

    const params = new URLSearchParams(window.location.search);
    const listingId = params.get('id');

    if (!listingId) {
        summaryContainer.innerHTML = '<p class="text-danger">No listing selected.</p>';
        return;
    }

    try {
        const listing = await apiClient.request(`/listings/${listingId}`);
        const imageUrl = listing.images && listing.images.length > 0 ? listing.images[0] : 'css/placeholder.jpg';

        summaryContainer.innerHTML = `
            <div class="card border-0 bg-transparent">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="${imageUrl}" class="img-fluid rounded-start" alt="${listing.title}">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body py-0">
                            <h5 class="card-title mb-1">${listing.title}</h5>
                            <p class="card-text"><small class="text-muted">${listing.city}, ${listing.neighborhood.name}</small></p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        summaryContainer.innerHTML = `<p class="text-danger">Failed to load listing details: ${error.message}</p>`;
        console.error(error);
    }
}

function initializePaymentHandlers() {
    const mpesaForm = document.getElementById('mpesa-form');
    if (mpesaForm) {
        mpesaForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const phoneInput = document.getElementById('mpesa-phone');
            const phone = phoneInput.value;
            const submitButton = mpesaForm.querySelector('button[type="submit"]');
            const responseContainer = document.getElementById('mpesa-response');
            responseContainer.textContent = '';

            if (!/^254\d{9}$/.test(phone)) {
                responseContainer.innerHTML = `<span class="text-danger">Invalid phone number. Use format 254...</span>`;
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            const params = new URLSearchParams(window.location.search);
            const listingId = params.get('id');

            try {
                const result = await apiClient.request('/payments/mpesa', 'POST', {
                    phone: phone,
                    listingId: listingId,
                    amount: 500 // The reservation fee
                });
                responseContainer.innerHTML = `<span class="text-success">${result.message || 'STK push sent. Please enter your M-Pesa PIN on your phone to complete the payment.'}</span>`;
            } catch (error) {
                responseContainer.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Pay KES 500';
            }
        });
    }

    const cardForm = document.getElementById('card-form');
    if (cardForm) {
        // IMPORTANT: Replace with your actual Stripe publishable key
        const stripePublishableKey = 'pk_test_YOUR_KEY_HERE';
        const stripe = Stripe(stripePublishableKey);
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const cardErrors = document.getElementById('card-errors');

        cardForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = cardForm.querySelector('button[type="submit"]');
            const cardHolderName = document.getElementById('card-holder').value;
            cardErrors.textContent = '';

            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: cardHolderName,
                },
            });

            if (error) {
                cardErrors.textContent = error.message;
                submitButton.disabled = false;
                submitButton.textContent = 'Pay KES 500';
                return;
            }

            const params = new URLSearchParams(window.location.search);
            const listingId = params.get('id');

            try {
                const result = await apiClient.request('/payments/stripe', 'POST', {
                    paymentMethodId: paymentMethod.id,
                    listingId: listingId,
                    amount: 50000 // Amount in cents for KES 500
                });
                cardErrors.innerHTML = `<span class="text-success">${result.message || 'Payment successful!'}</span>`;
            } catch (err) {
                cardErrors.innerHTML = `<span class="text-danger">Error: ${err.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Pay KES 500';
            }
        });
    }

    // PayPal integration
    const paypalContainer = document.getElementById('paypal-button-container');
    const paypalResponseContainer = document.getElementById('paypal-details').querySelector('p');
    if (paypalContainer) {
        try {
            paypal.Buttons({
                createOrder: async () => {
                    try {
                        const params = new URLSearchParams(window.location.search);
                        const listingId = params.get('id');
                        const orderData = await apiClient.request('/payments/paypal/create-order', 'POST', {
                            listingId: listingId,
                            // The backend will determine the amount and currency
                        });
                        return orderData.id;
                    } catch (error) {
                        paypalResponseContainer.innerHTML = `<span class="text-danger">Error creating order: ${error.message}</span>`;
                        return null;
                    }
                },
                onApprove: async (data) => {
                    try {
                        const result = await apiClient.request('/payments/paypal/capture-order', 'POST', {
                            orderID: data.orderID,
                        });
                        document.getElementById('paypal-details').innerHTML = `<div class="text-center text-success"><h4>Payment Successful!</h4><p>${result.message || 'Your reservation is complete.'}</p></div>`;
                    } catch (error) {
                        paypalResponseContainer.innerHTML = `<span class="text-danger">Payment capture failed: ${error.message}</span>`;
                    }
                },
                onError: (err) => {
                    console.error('PayPal SDK Error:', err);
                    paypalResponseContainer.innerHTML = `<span class="text-danger">An error occurred with the PayPal payment. Please try another method.</span>`;
                }
            }).render('#paypal-button-container');
        } catch (error) {
            console.error('Failed to render PayPal buttons:', error);
            paypalContainer.innerHTML = '<p class="text-danger">Could not load PayPal payment option.</p>';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    populateListingSummary();
    initializePaymentHandlers();
});
