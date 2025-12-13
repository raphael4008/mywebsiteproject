-- HouseHunter MySQL schema

CREATE DATABASE IF NOT EXISTS househunter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE househunter;

-- users table (includes owners and admins via role)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','owner','admin') DEFAULT 'user',
  is_active TINYINT(1) DEFAULT 1,
  has_paid TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- neighborhoods table
CREATE TABLE IF NOT EXISTS neighborhoods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  description TEXT,
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- listings table
CREATE TABLE IF NOT EXISTS listings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  neighborhood_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  city VARCHAR(100),
  htype VARCHAR(50),
  style VARCHAR(100),
  furnished TINYINT(1) DEFAULT 0,
  rent_amount DECIMAL(10, 2) DEFAULT 0,
  deposit_amount DECIMAL(10, 2) DEFAULT 0,
  verified TINYINT(1) DEFAULT 0,
  is_published TINYINT(1) DEFAULT 0,
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  status ENUM('AVAILABLE', 'PENDING', 'RESERVED') DEFAULT 'AVAILABLE',
  is_reserved TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (neighborhood_id) REFERENCES neighborhoods(id) ON DELETE SET NULL,
  FULLTEXT KEY `ft_listings` (`title`, `description`, `city`)
);

-- images table
CREATE TABLE IF NOT EXISTS images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  display_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- amenities table
CREATE TABLE IF NOT EXISTS amenities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

-- listing_amenities pivot table
CREATE TABLE IF NOT EXISTS listing_amenities (
  listing_id INT NOT NULL,
  amenity_id INT NOT NULL,
  PRIMARY KEY (listing_id, amenity_id),
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
  FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

-- reservations table
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- reservation_fees table
CREATE TABLE IF NOT EXISTS reservation_fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  method VARCHAR(50) DEFAULT 'mpesa',
  status VARCHAR(20) DEFAULT 'PENDING',
  mpesa_code VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);

-- owner_payments table
CREATE TABLE IF NOT EXISTS owner_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  method VARCHAR(50) DEFAULT 'mpesa',
  status VARCHAR(20) DEFAULT 'PENDING',
  mpesa_code VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- reviews table
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  reviewer_name VARCHAR(191),
  rating SMALLINT,
  title VARCHAR(255),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- messages table
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add indexes for optimization
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_status ON listings(status);

-- sample seed data
INSERT INTO users (name, email, password, role) VALUES
('Demo User','user@example.com', '$2y$10$examplehashedpasswordexamplehash', 'user'),
('Demo Owner','owner@example.com', '$2y$10$examplehashedpasswordexamplehash', 'owner'),
('Admin','admin@example.com', '$2y$10$examplehashedpasswordexamplehash', 'admin');

INSERT INTO neighborhoods (name, city, description) VALUES
('Roysambu', 'Nairobi', 'A bustling neighborhood with a mix of residential and commercial properties.'),
('Kilimani', 'Nairobi', 'An upscale neighborhood known for its quiet streets and modern apartments.'),
('Westlands', 'Nairobi', 'A vibrant neighborhood with shopping malls and nightlife.'),
('Nyali', 'Mombasa', 'A serene neighborhood near the beach with luxury homes.');

INSERT INTO listings (owner_id, neighborhood_id, title, htype, style, furnished, rent_amount, deposit_amount, verified, status)
VALUES
(2, 1, '1BR Apartment near TRM', 'ONE_BEDROOM', 'Modern', 0, 25000.00, 5000.00, 1, 'AVAILABLE'),
(2, 2, 'Studio in Kilimani', 'STUDIO', 'Contemporary', 1, 32000.00, 8000.00, 1, 'AVAILABLE'),
(2, 3, '2BR Apartment in Westlands', 'TWO_BEDROOM', 'Luxury', 1, 75000.00, 15000.00, 1, 'AVAILABLE'),
(2, 4, 'Beachfront Villa in Nyali', 'VILLA', 'Modern', 1, 120000.00, 30000.00, 1, 'AVAILABLE');

INSERT INTO amenities (name) VALUES ('WiFi'), ('AC'), ('Parking'), ('Swimming Pool'), ('Gym'), ('Balcony'), ('Garden');

INSERT INTO listing_amenities (listing_id, amenity_id) VALUES (1, 1), (2, 2), (3, 5), (4, 6);

INSERT INTO images (listing_id, image_path) VALUES
(1, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb'),
(2, 'https://images.unsplash.com/photo-1460518451285-97b6aa326961');