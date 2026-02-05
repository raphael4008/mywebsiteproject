<!-- Filter and Search Section -->
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <form id="listingsFilterForm">
                <div class="row g-3">
                    <!-- Location Filter -->
                    <div class="col-md-4">
                        <label for="filterCity" class="form-label">Location</label>
                        <input type="text" id="filterCity" class="form-control" placeholder="e.g., Nairobi, Mombasa">
                    </div>
                    
                    <!-- Property Type Filter -->
                    <div class="col-md-3">
                        <label for="filterType" class="form-label">Property Type</label>
                        <select id="filterType" class="form-select">
                            <option value="">All Types</option>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="villa">Villa</option>
                        </select>
                    </div>
                    
                    <!-- Max Price Filter -->
                    <div class="col-md-3">
                        <label for="filterPrice" class="form-label">Max Price (KES)</label>
                        <input type="number" id="filterPrice" class="form-control" placeholder="e.g., 80000">
                    </div>
                    
                    <!-- Search Button -->
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sorting and Results Count -->
<div class="container d-flex justify-content-between align-items-center my-3">
    <span id="results-count" class="text-muted"></span>
    <div class="d-flex align-items-center">
        <label for="sortListings" class="form-label me-2 mb-0">Sort by:</label>
        <select id="sortListings" class="form-select form-select-sm" style="width: auto;">
            <option value="newest">Newest</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
        </select>
    </div>
</div>

<!-- Listings Grid -->
<div class="container">
    <div id="listings-grid" class="row g-4">
        <!-- Listings will be loaded here -->
    </div>
    <div id="loader" class="text-center my-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div id="no-results" class="text-center my-5" style="display: none;">
        <h3>No Listings Found</h3>
        <p>We couldn't find any properties matching your search. Try adjusting your filters.</p>
        <button id="clear-filters-btn" class="btn btn-outline-secondary">Clear Filters</button>
    </div>
</div>

<script src="{{ basePath }}/js/listings.js" type="module"></script>
