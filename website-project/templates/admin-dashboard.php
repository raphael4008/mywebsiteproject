<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #1a252f;
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 1rem 1.5rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: #2c3e50;
        }

        .stat-card {
            border-left: 5px solid var(--primary);
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px;">
        <a href="{{ basePath }}/"
            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-3">
            <span class="fs-4 fw-bold text-uppercase">Admin Panel</span>
        </a>
        <hr class="border-secondary">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="#" class="nav-link active" data-section="dashboard"><i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard</a>
            </li>
            <li>
                <a href="#" class="nav-link" data-section="listings"><i class="fas fa-list me-2"></i> Listings</a>
            </li>
            <li>
                <a href="#" class="nav-link" data-section="users"><i class="fas fa-users me-2"></i> Users</a>
            </li>
            <li>
                <a href="#" class="nav-link" data-section="reservations"><i class="fas fa-calendar-check me-2"></i>
                    Reservations</a>
            </li>
            <li>
                <a href="#" class="nav-link" data-section="amenities"><i class="fas fa-plus-circle me-2"></i>
                    Amenities</a>
            </li>
        </ul>
        <hr class="border-secondary">
        <div class="px-3">
            <a href="#" id="logoutBtn" class="text-white text-decoration-none"><i class="fas fa-sign-out-alt me-2"></i>
                Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <!-- Dashboard Section -->
        <div id="dashboard-section" class="content-section">
            <h2 class="fw-bold mb-4">System Overview</h2>
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm p-3">
                        <h6 class="text-muted text-uppercase small fw-bold">Total Users</h6>
                        <h2 class="fw-bold mb-0" id="totalUsers">0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm p-3" style="border-left-color: #198754;">
                        <h6 class="text-muted text-uppercase small fw-bold">Total Listings</h6>
                        <h2 class="fw-bold mb-0" id="totalListings">0</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm p-3" style="border-left-color: #ffc107;">
                        <h6 class="text-muted text-uppercase small fw-bold">Pending Verifications</h6>
                        <h2 class="fw-bold mb-0" id="pendingListings">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listings Section -->
        <div id="listings-section" class="content-section d-none">
            <h2 class="fw-bold mb-4">Manage Listings</h2>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="adminListingsTable">
                            <tr>
                                <td colspan="4" class="text-center py-4">Loading listings...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div id="users-section" class="content-section d-none">
            <h2 class="fw-bold mb-4">User Management</h2>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="adminUsersTable">
                            <tr>
                                <td colspan="4" class="text-center py-4">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reservations Section -->
        <div id="reservations-section" class="content-section d-none">
            <h2 class="fw-bold mb-4">Reservation Management</h2>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Listing ID</th>
                                <th>User ID</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody id="adminReservationsTable">
                            <tr>
                                <td colspan="4" class="text-center py-4">Loading reservations...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Amenities Section -->
        <div id="amenities-section" class="content-section d-none">
            <h2 class="fw-bold mb-4">Amenity Management</h2>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form id="addAmenityForm" class="mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col">
                                <input type="text" id="amenityName" class="form-control" placeholder="New amenity name"
                                    required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Add Amenity</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="amenitiesTable">
                            <tr>
                                <td colspan="2" class="text-center py-4">Loading amenities...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>window.basePath = '{{ basePath }}';</script>
<script src="../js/admin-dashboard.js" type="module"></script>
</body>
</html>