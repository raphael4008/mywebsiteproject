import apiClient from './apiClient.js';
import { formatCurrency, getImageUrl, showNotification } from './utils.js';

document.addEventListener('DOMContentLoaded', () => {
    const listingDetailsContainer = document.getElementById('listing-details-container');
    if (!listingDetailsContainer) return;

    const urlParams = new URLSearchParams(window.location.search);
    const listingId = urlParams.get('id');

    if (!listingId) {
        listingDetailsContainer.innerHTML = '<p class="text-center">No listing ID provided.</p>';
        return;
    }

    const renderListingDetails = (listing) => {
        if (!listing) {
            listingDetailsContainer.innerHTML = '<p class="text-center">Listing data is not available.</p>';
            return;
        }

        const images = Array.isArray(listing.images) ? listing.images : [];
        const listingHtml = `
            <div class="col-lg-8">
                <div class="mb-4">
                    <h2>${listing.title || 'N/A'}</h2>
                    <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> ${listing.city || 'N/A'}, ${listing.neighborhood || 'N/A'}</p>
                </div>

                <div class="carousel slide" id="listingCarousel" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        ${images.map((img, index) => `
                            <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                <img src="${getImageUrl(img)}" class="d-block w-100" alt="Listing image ${index + 1}">
                            </div>
                        `).join('')}
                        ${images.length === 0 ? '<div class="carousel-item active"><img src="https://via.placeholder.com/800x600.png?text=No+Image+Available" class="d-block w-100" alt="No image available"></div>' : ''}
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#listingCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#listingCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <ul class="nav nav-tabs card-header-tabs" id="listingTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab">Features & Amenities</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-section" type="button" role="tab">Location Map</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="videos-tab" data-bs-toggle="tab" data-bs-target="#videos" type="button" role="tab">Videos</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="listingTabsContent">
                            <div class="tab-pane fade show active" id="features" role="tabpanel">
                                <p>${listing.description || 'No description available.'}</p>
                                <hr>
                                <div id="amenities-list" class="row g-3"></div>
                            </div>
                            <div class="tab-pane fade" id="map-section" role="tabpanel">
                                <div id="map" style="height: 400px;"></div>
                            </div>
                            <div class="tab-pane fade" id="videos" role="tabpanel">
                                <div id="video-list" class="row g-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 20px;">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Reserve This House</h5>
                            <p class="card-text">Pay a small fee to hold this property.</p>
                                <button id="reserveNowBtn" class="btn btn-primary w-100">Reserve Now</button>
                        </div>
                    </div>
                        <div class="card shadow-sm mt-3">
                            <div class="card-body">
                                <h5 class="card-title">Contact Owner</h5>
                                <p class="card-text">Get the owner's contact details or send a quick message.</p>
                                <button id="contactOwnerBtn" class="btn btn-outline-secondary w-100">Contact Owner</button>
                            </div>
                        </div>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Request a Driver</h5>
                            <form id="driverRequestForm">
                                <div class="mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Request Driver</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;
        listingDetailsContainer.innerHTML = listingHtml;

        // Attach handlers
        const reserveBtn = document.getElementById('reserveNowBtn');
        if (reserveBtn) {
            reserveBtn.addEventListener('click', () => {
                const token = localStorage.getItem('token');
                if (!token) {
                    // If not logged in, redirect to login page and then back here
                    window.location.href = `../login.html?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                    return;
                }
                
                // Redirect to the payment page, passing the listing ID
                window.location.href = `../payment.html?listing_id=${listing.id}`;
            });
        }

        const contactBtn = document.getElementById('contactOwnerBtn');
        if (contactBtn) {
            contactBtn.addEventListener('click', () => {
                const owner = listing.owner || {};
                if (owner.phone || owner.email) {
                    const parts = [];
                    if (owner.phone) parts.push(`Phone: ${owner.phone}`);
                    if (owner.email) parts.push(`Email: ${owner.email}`);
                    alert(`Owner contact:\n${parts.join('\n')}`);
                    return;
                }

                const token = localStorage.getItem('token');
                if (!token) {
                    if (confirm('You must be logged in to contact the owner. Would you like to login now?')) {
                        window.location.href = `../login.html?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                    }
                    return;
                }
                alert('Owner contact is not yet available. Please reserve the property, and the owner will be notified.');
            });
        }
        
        const driverForm = document.getElementById('driverRequestForm');
        if (driverForm) {
            driverForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(driverForm);
                const data = Object.fromEntries(formData.entries());
                
                // You would typically get the pickup location from the listing
                data.pickup = `${listing.neighborhood}, ${listing.city}`;
                // Dropoff would be the user's new address, for now, let's make it a placeholder
                data.dropoff = 'User Destination'; 
                data.truck_size = 'canter'; // Or let user choose

                try {
                    const response = await apiClient.request('/transport/request', 'POST', data);
                    showNotification('Driver request sent successfully! You will be contacted shortly.', 'success');
                    driverForm.reset();
                } catch (err) {
                    console.error('Driver request error:', err);
                    showNotification(err.message || 'Failed to request driver.', 'error');
                }
            });
        }
    };

    const renderAmenities = (amenities) => {
        const amenitiesList = document.getElementById('amenities-list');
        if (!amenitiesList) return;

        if (Array.isArray(amenities) && amenities.length > 0) {
            amenitiesList.innerHTML = amenities.map(amenity => `
                <div class="col-md-6">
                    <i class="fas fa-check text-primary me-2"></i> ${amenity || 'N/A'}
                </div>
            `).join('');
        } else {
            amenitiesList.innerHTML = '<p>No amenities information available.</p>';
        }
    };

    const renderVideos = (videos) => {
        const videoList = document.getElementById('video-list');
        if (!videoList) return;

        if (!Array.isArray(videos) || videos.length === 0) {
            videoList.innerHTML = '<p class="text-muted">No videos available for this listing.</p>';
            return;
        }

        const videoHTML = videos.map(video => {
            const url = video.url;
            let embedHtml = '';

            if (url.includes('youtube.com') || url.includes('youtu.be')) {
                const videoIdMatch = url.match(/(?:v=|\/|embed\/|watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
                const videoId = videoIdMatch ? videoIdMatch[1] : null;
                if (videoId) {
                    const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    embedHtml = `<iframe src="${embedUrl}" title="${video.title || 'YouTube video'}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                }
            } else if (url.includes('vimeo.com')) {
                const videoIdMatch = url.match(/(?:vimeo\.com\/|video\/)(\d+)/);
                const videoId = videoIdMatch ? videoIdMatch[1] : null;
                if (videoId) {
                    const embedUrl = `https://player.vimeo.com/video/${videoId}`;
                    embedHtml = `<iframe src="${embedUrl}" title="${video.title || 'Vimeo video'}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
                }
            } else if (url.endsWith('.mp4') || url.endsWith('.webm') || url.endsWith('.ogg')) {
                embedHtml = `<video controls src="${url}" title="${video.title || 'Listing video'}" style="width: 100%; height: auto;"></video>`;
            } 
            
            if (!embedHtml) {
                // Fallback for unsupported URLs
                return `<div class="col-12"><p>Unsupported video format or invalid URL: <a href="${url}" target="_blank" rel="noopener noreferrer">View video</a></p></div>`;
            }
            
            return `<div class="col-12 mb-3"><div class="ratio ratio-16x9">${embedHtml}</div></div>`;

        }).join('');

        videoList.innerHTML = videoHTML;
    };

    const initMap = (lat, lng) => {
        const mapContainer = document.getElementById('map');
        if (!mapContainer) return;

        // Use default coordinates if lat/lng are invalid
        const latitude = (typeof lat === 'number' && !isNaN(lat)) ? lat : -1.2921;
        const longitude = (typeof lng === 'number' && !isNaN(lng)) ? lng : 36.8219;
        const zoomLevel = (typeof lat === 'number' && !isNaN(lat)) ? 15 : 12;

        try {
            const map = L.map('map').setView([latitude, longitude], zoomLevel);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            if (typeof lat === 'number' && !isNaN(lat)) {
                L.marker([latitude, longitude]).addTo(map);
            } else {
                mapContainer.innerHTML = '<p class="text-center">Map location not available.</p>';
            }
        } catch (error) {
            console.error("Leaflet map initialization failed: ", error);
            mapContainer.innerHTML = '<p class="text-center">Could not load map.</p>';
        }
    };

    const fetchListingDetails = async () => {
        listingDetailsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading details...</p></div>';
        try {
            const listing = await apiClient.request(`/listings/${listingId}`);
            
            // The API response for a single listing is the listing object itself, not nested under 'data'
            if (!listing) {
                throw new Error("Listing data is null or undefined.");
            }

            renderListingDetails(listing);

            // Use optional chaining and nullish coalescing for safety
            renderAmenities(listing.amenities ?? []);
            renderVideos(listing.videos ?? []);

            if (listing.latitude && listing.longitude) {
                initMap(listing.latitude, listing.longitude);
            } else {
                const mapContainer = document.getElementById('map');
                if (mapContainer) {
                    mapContainer.innerHTML = '<p class="text-center">Map location not available.</p>';
                }
            }
        } catch (error) {
            console.error('Failed to fetch listing details:', error);
            showNotification('Error loading listing details.', 'error');
            listingDetailsContainer.innerHTML = '<p class="text-center text-danger">Failed to load listing details. Please try again later.</p>';
        }
    };

    fetchListingDetails();
});
