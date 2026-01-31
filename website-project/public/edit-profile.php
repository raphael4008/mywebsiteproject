<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="navbar-container"></div>

    <main class="container py-5">
        <div id="edit-profile-container">
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script type="module">
        import apiClient from './js/apiClient.js';
        import { showNotification } from './js/utils.js';

        const container = document.getElementById('edit-profile-container');

        if (!localStorage.getItem('token')) {
            window.location.href = 'login.php';
        }

        async function loadProfile() {
            try {
                const user = await apiClient.request('/users/me');
                renderForm(user);
            } catch (error) {
                showNotification('Could not load profile data.', 'error');
                container.innerHTML = '<p class="text-danger">Could not load profile data.</p>';
            }
        }

        function renderForm(user) {
            container.innerHTML = `
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <form id="editProfileForm">
                                    <h2 class="mb-4">Edit Profile</h2>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="${user.name}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="${user.email}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="${user.phone || ''}">
                                    </div>
                                    <hr>
                                    <h4 class="mb-3">Change Password</h4>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('editProfileForm').addEventListener('submit', handleFormSubmit);
        }
        
        async function handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button[type="submit"]');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            if (data.password && data.password !== data.password_confirmation) {
                showNotification('Passwords do not match.', 'error');
                return;
            }
            
            if (!data.password) {
                delete data.password;
                delete data.password_confirmation;
            }

            button.disabled = true;
            button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Saving...';

            try {
                await apiClient.request('/users/me', 'PUT', data);
                showNotification('Profile updated successfully!', 'success');
                setTimeout(() => {
                    window.location.href = 'profile.php';
                }, 1000);
            } catch (error) {
                const errorMessage = error.data && error.data.message ? error.data.message : 'An error occurred.';
                showNotification(errorMessage, 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = 'Save Changes';
            }
        }

        loadProfile();
    </script>
</body>
</html>
