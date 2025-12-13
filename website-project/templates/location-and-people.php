<main class="container py-5 location-page">
    <ul class="nav nav-tabs location-nav" id="locationTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="agents-tab" data-bs-toggle="tab" data-bs-target="#agents" type="button" role="tab" aria-controls="agents" aria-selected="true" data-translate="Agents">Agents</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="neighborhoods-tab" data-bs-toggle="tab" data-bs-target="#neighborhoods" type="button" role="tab" aria-controls="neighborhoods" aria-selected="false" data-translate="Neighborhoods">Neighborhoods</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transport-tab" data-bs-toggle="tab" data-bs-target="#transport" type="button" role="tab" aria-controls="transport" aria-selected="false" data-translate="Transport">Transport</button>
        </li>
    </ul>

    <div class="tab-content" id="locationTabContent">
        <!-- Agents Section -->
        <div class="tab-pane fade show active" id="agents" role="tabpanel" aria-labelledby="agents-tab">
            <section class="agents-list" data-aos="fade-up">
                <h2>Meet Our Expert Agents</h2>
                <div class="search-bar">
                    <input type="text" id="agent-search" placeholder="Search by name, location, or specialization">
                    <button id="search-btn">Search</button>
                </div>
                <div class="agent-cards">
                    <?php
                    $agents = App\Models\Agent::getAll();
                    foreach ($agents as $agent) {
                    ?>
                        <div class="agent-card" data-aos="fade-up" data-aos-delay="100">
                            <img src="images/<?php echo $agent['image']; ?>" alt="<?php echo $agent['name']; ?>">
                            <h4><?php echo $agent['name']; ?></h4>
                            <p><?php echo $agent['specialization']; ?> | <?php echo $agent['location']; ?></p>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </section>
            <section class="contact-agent" data-aos="fade-up" data-aos-delay="400">
                <h2>Contact an Agent</h2>
                <form id="contact-form">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </section>
        </div>

        <!-- Neighborhoods Section -->
        <div class="tab-pane fade" id="neighborhoods" role="tabpanel" aria-labelledby="neighborhoods-tab">
            <h1 data-translate="Explore Neighborhoods" data-aos="fade-in">Explore Neighborhoods</h1>
            <div id="neighborhood-list" class="results-grid" data-aos="fade-up"></div>
        </div>

        <!-- Transport Section -->
        <div class="tab-pane fade" id="transport" role="tabpanel" aria-labelledby="transport-tab">
            <section class="transport-hero" data-aos="fade-in">
                <h1>Book Your Move</h1>
                <p>Find reliable and affordable movers for your new home.</p>
            </section>
            <section class="transport-form-section" data-aos="fade-up">
                <form id="transportForm" class="transport-form">
                    <h2>Request a Mover</h2>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="pickup-address">Pickup Address</label>
                        <input type="text" id="pickup-address" name="pickup-address" required>
                    </div>
                    <div class="form-group">
                        <label for="dropoff-address">Dropoff Address</label>
                        <input type="text" id="dropoff-address" name="dropoff-address" required>
                    </div>
                    <div class="form-group">
                        <label for="moving-date">Moving Date</label>
                        <input type="date" id="moving-date" name="moving-date" required>
                    </div>
                    <div class="form-group">
                        <label for="items">Items to Move</label>
                        <textarea id="items" name="items" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="cta-btn">Request a Quote</button>
                </form>
                <div id="transportMsg" class="mt-3 text-center"></div>
            </section>
            <section class="available-drivers" data-aos="fade-up">
                <h2>Available Drivers</h2>
                <div id="driver-list" class="driver-list">
                    </div>
            </section>
        </div>
    </div>
</main>
