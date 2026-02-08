<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content=""> <!-- CSRF token will be injected by the server -->
    <title>Owner Dashboard – HouseHunter</title>
    <link rel="stylesheet" href="{{ basePath }}/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/vanilla-js-calendar@1.6.5/build/vanilla-js-calendar.min.css">
    <link rel="stylesheet" href="{{ basePath }}/css/owner-dashboard.css">
</head>

<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px;">
            <a href="{{ basePath }}/"
                class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-3">
                <span class="fs-4 fw-bold">Owner Portal</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="#overview" class="nav-link active" data-section="overview"><i
                            class="fas fa-chart-pie me-2"></i> Overview</a>
                </li>
                <li>
                    <a href="#properties" class="nav-link" data-section="properties"><i
                            class="fas fa-building me-2"></i> My Properties</a>
                </li>
                <li>
                    <a href="#reservations" class="nav-link" data-section="reservations"><i
                            class="fas fa-calendar-check me-2"></i> Reservations</a>
                </li>
                <li>
                    <a href="#financials" class="nav-link" data-section="financials"><i
                            class="fas fa-dollar-sign me-2"></i> Financials</a>
                </li>
                <li>
                    <a href="#messages" class="nav-link" data-section="messages"><i class="fas fa-comments me-2"></i>
                        Messages</a>
                </li>
                <li>
                    <a href="{{ basePath }}/rental-agreements" class="nav-link"><i
                            class="fas fa-file-contract me-2"></i>
                        Agreements</a>
                </li>
            </ul>
            <hr>
            <div class="dropdown px-3">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 32px; height: 32px;">O</div>
                    <strong id="ownerNameDisplay"></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="{{ basePath }}/profile">Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="#" id="logoutBtn">Sign out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;" role="main">
            <!-- Overview Section -->
            <div id="overview-section" class="content-section" aria-live="polite">
                <h2 class="fw-bold mb-4">Dashboard Overview</h2>
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card stat-card bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Properties</p>
                                    <h3 class="fw-bold mb-0" id="totalProperties">0</h3>
                                </div>
                                <div class="bg-light text-primary rounded p-3"><i class="fas fa-home fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Active Listings</p>
                                    <h3 class="fw-bold mb-0" id="activeListings">0</h3>
                                </div>
                                <div class="bg-light text-success rounded p-3"><i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Views</p>
                                    <h3 class="fw-bold mb-0" id="totalViews">0</h3>
                                </div>
                                <div class="bg-light text-info rounded p-3"><i class="fas fa-eye fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Revenue (Est)</p>
                                    <h3 class="fw-bold mb-0" id="totalRevenue">KES 0</h3>
                                </div>
                                <div class="bg-light text-warning rounded p-3"><i class="fas fa-coins fa-2x"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-3">Recent Activity</h4>
                <div class="card table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Property</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentActivityTable">
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Loading activity...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Section -->
            <div id="properties-section" class="content-section d-none" aria-live="polite">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">My Properties</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal"><i
                            class="fas fa-plus me-2"></i> Add New Property</button>
                </div>
                <div id="propertiesList" class="row g-4"></div>
            </div>

            <!-- Reservations Section -->
            <div id="reservations-section" class="content-section d-none" aria-live="polite">
                <h2 class="fw-bold mb-4">Reservations</h2>
                <div class="card table-card">
                    <div class="card-body">
                        <div id="reservationsList">Loading reservations...</div>
                    </div>
                </div>
            </div>

            <!-- Financials Section -->
            <div id="financials-section" class="content-section d-none" aria-live="polite">
                <h2 class="fw-bold mb-4">Financials</h2>
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card stat-card bg-white p-3">
                            <h6 class="text-muted text-uppercase small fw-bold">Total Revenue</h6>
                            <h2 class="fw-bold mb-0" id="totalRevenueFinancials">KES 0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-white p-3">
                            <h6 class="text-muted text-uppercase small fw-bold">This Month's Earnings</h6>
                            <h2 class="fw-bold mb-0" id="monthlyEarnings">KES 0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-white p-3">
                            <h6 class="text-muted text-uppercase small fw-bold">Pending Payouts</h6>
                            <h2 class="fw-bold mb-0" id="pendingPayouts">KES 0</h2>
                        </div>
                    </div>
                </div>
                <div class="card table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Date</th>
                                        <th>Type</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsTable">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Loading transactions...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Section -->
            <div id="messages-section" class="content-section d-none" aria-live="polite">
                <h2 class="fw-bold mb-4">Messages</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header fw-bold">Conversations</div>
                            <div id="conversationsList" class="list-group list-group-flush">
                                <!-- Conversation items will be injected here -->
                                <div class="text-center p-3 text-muted">Loading conversations...</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header fw-bold" id="conversationHeader">Select a conversation</div>
                            <div class="card-body" id="conversationMessages" style="height: 500px; overflow-y: scroll;">
                                <!-- Messages will be injected here -->
                                <div class="text-center p-3 text-muted">Messages will appear here.</div>
                            </div>
                            <div class="card-footer">
                                <form id="sendMessageForm">
                                    <div class="input-group">
                                        <input type="text" id="messageInput" class="form-control"
                                            placeholder="Type a message..." disabled>
                                        <button class="btn btn-primary" type="submit" disabled>Send</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPropertyForm">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Property Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <select name="city" class="form-select" required>
                                    <option value="Nairobi">Nairobi</option>
                                    <option value="Mombasa">Mombasa</option>
                                    <option value="Kisumu">Kisumu</option>
                                    <option value="Nakuru">Nakuru</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select name="htype" class="form-select" required>
                                    <option value="apartment">Apartment</option>
                                    <option value="house">House</option>
                                    <option value="studio">Studio</option>
                                    <option value="single_room">Single Room</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rent Amount (KES)</label>
                                <input type="number" name="rent_amount" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Deposit Amount (KES)</label>
                                <input type="number" name="deposit_amount" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Images</label>
                                <input type="file" name="images" class="form-control" multiple accept="image/*">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Property Modal -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPropertyForm">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Property Title</label>
                                <input type="text" name="title" id="edit-title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <select name="city" id="edit-city" class="form-select" required>
                                    <option value="Nairobi">Nairobi</option>
                                    <option value="Mombasa">Mombasa</option>
                                    <option value="Kisumu">Kisumu</option>
                                    <option value="Nakuru">Nakuru</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select name="htype" id="edit-htype" class="form-select" required>
                                    <option value="apartment">Apartment</option>
                                    <option value="house">House</option>
                                    <option value="studio">Studio</option>
                                    <option value="single_room">Single Room</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rent Amount (KES)</label>
                                <input type="number" name="rent_amount" id="edit-rent_amount" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Deposit Amount (KES)</label>
                                <input type="number" name="deposit_amount" id="edit-deposit_amount" class="form-control"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="edit-description" class="form-control" rows="3"
                                    required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Images</label>
                                <input type="file" name="images" class="form-control" multiple accept="image/*">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn.secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Availability Modal -->
    <div class="modal fade" id="availabilityModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Manage Availability</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div id="calendar"></div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="fw-bold">Block Dates</h6>
                            <form id="addUnavailabilityForm">
                                <input type="hidden" id="unavailabilityListingId">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" id="unavailabilityStartDate" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" id="unavailabilityEndDate" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Block</button>
                            </form>
                            <hr>
                            <h6 class="fw-bold">Currently Blocked</h6>
                            <div id="unavailabilityList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanilla-js-calendar@1.6.5/build/vanilla-js-calendar.min.js"></script>
    <script>window.basePath = '{{ basePath }}';</script>
    <script src="{{ basePath }}/js/owner-dashboard.js" type="module"></script>
</body>
</html>