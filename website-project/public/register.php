<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div id="navbar-container"></div>

    <main class="form-signin w-100 m-auto">
        <form id="registerForm">
            <h1 class="h3 mb-3 fw-normal">Create an account</h1>

            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required>
                <label for="name">Full name</label>
            </div>
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Email address</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
                <label for="password_confirmation">Confirm Password</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">Sign up</button>
            <p class="mt-3">
                Already have an account? <a href="login.php">Sign in</a>
            </p>
            <p class="mt-5 mb-3 text-muted">&copy; 2023–2024</p>
        </form>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script type="module">
        import apiClient from './js/apiClient.js';
        import { showNotification } from './js/utils.js';

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button[type="submit"]');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            if (data.password !== data.password_confirmation) {
                showNotification('Passwords do not match.', 'error');
                return;
            }

            button.disabled = true;
            button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Signing up...';

            try {
                await apiClient.request('/register', 'POST', data);
                showNotification('Registration successful! Please log in.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1000);
            } catch (error) {
                const errorMessage = error.data && error.data.message ? error.data.message : 'Registration failed. Please try again.';
                showNotification(errorMessage, 'error');
                if (error.data && error.data.errors) {
                    Object.entries(error.data.errors).forEach(([key, value]) => {
                        const errorEl = document.createElement('div');
                        errorEl.className = 'text-danger small';
                        errorEl.textContent = value;
                        const inputEl = form.querySelector(`[name=${key}]`);
                        inputEl.parentNode.appendChild(errorEl);
                    });
                }
            } finally {
                button.disabled = false;
                button.innerHTML = 'Sign up';
            }
        });
    </script>
</body>
</html>
