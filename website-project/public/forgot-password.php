<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div id="navbar-container"></div>

    <main class="form-signin w-100 m-auto">
        <form id="forgotPasswordForm">
            <h1 class="h3 mb-3 fw-normal">Forgot Password</h1>
            <p>Enter your email address and we will send you a link to reset your password.</p>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Email address</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Send Reset Link</button>
            <p class="mt-3">
                Remember your password? <a href="login.php">Sign in</a>
            </p>
            <p class="mt-5 mb-3 text-muted">&copy; 2023–2024</p>
        </form>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script type="module">
        import apiClient from './js/apiClient.js';
        import { showNotification } from './js/utils.js';

        document.getElementById('forgotPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button[type="submit"]');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            button.disabled = true;
            button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Sending...';

            try {
                const response = await apiClient.request('/auth/forgot-password', 'POST', data);
                showNotification(response.message, 'success');
                form.reset();
            } catch (error) {
                const errorMessage = error.data && error.data.message ? error.data.message : 'An error occurred. Please try again.';
                showNotification(errorMessage, 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = 'Send Reset Link';
            }
        });
    </script>
</body>
</html>
