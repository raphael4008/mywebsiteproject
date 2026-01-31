<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="navbar-container"></div>

    <main class="container py-5">
        <div id="profile-container">
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

        const profileContainer = document.getElementById('profile-container');

        if (!localStorage.getItem('token')) {
            window.location.href = 'login.php';
        }

        async function loadProfile() {
            try {
                const user = await apiClient.request('/users/me');
                renderProfile(user);
            } catch (error) {
                showNotification('Could not load profile data.', 'error');
                profileContainer.innerHTML = '<p class="text-danger">Could not load profile data.</p>';
            }
        }

        function renderProfile(user) {
            profileContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="${user.profile_pic || 'images/placeholder.svg'}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" alt="Profile picture">
                        <h3>${user.name}</h3>
                        <p class="text-muted">${user.email}</p>
                        <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
                        <button id="logoutButton" class="btn btn-outline-danger mt-2">Logout</button>
                    </div>
                    <div class="col-md-8">
                        <h2>My Details</h2>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Name:</strong> ${user.name}</li>
                            <li class="list-group-item"><strong>Email:</strong> ${user.email}</li>
                            <li class="list-group-item"><strong>Phone:</strong> ${user.phone || 'Not provided'}</li>
                            <li class="list-group-item"><strong>Joined:</strong> ${new Date(user.created_at).toLocaleDateString()}</li>
                        </ul>
                    </div>
                </div>
            `;

            document.getElementById('logoutButton').addEventListener('click', () => {
                localStorage.removeItem('token');
                localStorage.removeItem('refreshToken');
                window.location.href = 'login.php';
            });
        }

        loadProfile();
    </script>
</body>
</html>
