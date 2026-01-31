<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Payment – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <div id="navbar-container"></div>

    <header class="bg-primary text-white py-5 text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/g.jpeg') center/cover;">
        <div class="container">
            <h1 class="display-4 fw-bold">Secure Payment</h1>
            <p class="lead">Complete your transaction safely and securely.</p>
        </div>
    </header>

    <main class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="mb-4">Choose Payment Method</h2>
                        <div class="payment-methods">
                            <button id="mpesaBtn" class="btn btn-lg btn-success w-100 mb-3">
                                <i class="fas fa-mobile-alt me-2"></i> Pay with M-Pesa
                            </button>
                            <button id="stripeBtn" class="btn btn-lg btn-info w-100">
                                <i class="fab fa-stripe-s me-2"></i> Pay with Card (Stripe)
                            </button>
                        </div>
                        <div id="payment-form-container" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script type="module">
        // For now, this is a placeholder for the payment logic.
        // A full implementation would require Stripe.js and M-Pesa STK push integration.
        import { showNotification } from './js/utils.js';

        const mpesaBtn = document.getElementById('mpesaBtn');
        const stripeBtn = document.getElementById('stripeBtn');
        const formContainer = document.getElementById('payment-form-container');

        mpesaBtn.addEventListener('click', () => {
            showNotification('M-Pesa payment option coming soon!', 'info');
            // Here you would typically show a form to enter a phone number
            // and then make a request to the /payment/mpesa/stk_push endpoint.
        });

        stripeBtn.addEventListener('click', () => {
            showNotification('Card payment option coming soon!', 'info');
            // Here you would typically mount the Stripe Elements form
            // and handle the payment flow using Stripe.js.
        });
    </script>
</body>
</html>
