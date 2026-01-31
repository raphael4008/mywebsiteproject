<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div id="navbar-container"></div>

    <main class="form-signin w-100 m-auto">
        <form id="resetPasswordForm">
            <h1 class="h3 mb-3 fw-normal">Reset Password</h1>
            
            <input type="hidden" id="token" name="token">

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Email address</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="New Password" required>
                <label for="password">New Password</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm New Password" required>
                <label for="password_confirmation">Confirm New Password</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Reset Password</button>
            <p class="mt-5 mb-3 text-muted">&copy; 2023–2024</p>
        </form>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script type="module">
        import apiClient from './js/apiClient.js';
        import { showNotification } from './js/utils.js';

        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            document.getElementById('token').value = token;
        } else {
            showNotification('No reset token provided.', 'error');
        }
        
        const email = urlParams.get('email');
        if(email){
            document.getElementById('email').value = email;
        }


        document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button[type="submit"]');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            if (data.password !== data.password_confirmation) {
                showNotification('Passwords do not match.', 'error');
                return;
            }
            
            if (!data.token) {
                showNotification('Reset token is missing.', 'error');
                return;
            }

            button.disabled = true;
            button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Reseting...';

            try {
                const response = await apiClient.request('/auth/reset-password', 'POST', data);
                showNotification(response.message, 'success');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1000);
            } catch (error) {
                const errorMessage = error.data && error.data.message ? error.data.message : 'An error occurred. Please try again.';
                showNotification(errorMessage, 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = 'Reset Password';
            }
        });
    </script>
</body>
</html>
