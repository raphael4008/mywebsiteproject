-- Migration to add performance indexes and refactor users table

-- Part 1: Add Performance Indexes
-- Index on foreign keys and commonly searched columns in the listings table
CREATE INDEX idx_listings_owner_id ON listings(owner_id);
CREATE INDEX idx_listings_agent_id ON listings(agent_id);
CREATE INDEX idx_listings_neighborhood_id ON listings(neighborhood_id);
CREATE INDEX idx_listings_city ON listings(city);
CREATE INDEX idx_listings_rent_amount ON listings(rent_amount);
CREATE INDEX idx_listings_status ON listings(status);

-- Index on the reviews table for user-specific lookups
CREATE INDEX idx_reviews_user_id ON reviews(user_id);


-- Part 2: Refactor Agents and Drivers
-- Add a JSON column to the users table to hold role-specific profile data
ALTER TABLE users ADD COLUMN profile_data JSON;

-- Migrate data from the old 'agents' table into the 'users' table
-- This assumes that the agent emails do not already exist in the users table.
-- A more complex migration would handle potential duplicates.
INSERT INTO users (name, email, password, role, profile_data)
SELECT
    name,
    email,
    'password_placeholder', -- A temporary placeholder password
    'agent',
    JSON_OBJECT(
        'bio', bio,
        'phone', phone,
        'specialization', specialization,
        'rating', rating,
        'review_count', review_count,
        'image', image
    )
FROM agents;

-- Migrate data from the old 'drivers' table into the 'users' table
INSERT INTO users (name, email, password, role, profile_data)
SELECT
    name,
    CONCAT('driver_', REPLACE(LOWER(name), ' ', '_'), '@example.com'), -- Generate a placeholder email
    'password_placeholder',
    'driver',
    JSON_OBJECT(
        'vehicle', vehicle,
        'rating', rating,
        'image', image
    )
FROM drivers;

-- Part 3: Update Foreign Key and Drop Redundant Tables
-- To update the agent_id in the listings table, we must first drop the existing
-- foreign key constraint that links it to the old 'agents' table.
ALTER TABLE listings DROP FOREIGN KEY listings_ibfk_2;

-- Now, update the agent_id to point to the correct user ID in the 'users' table.
-- We join through the 'agents' table one last time to get the email for the lookup.
UPDATE listings l
JOIN agents a ON l.agent_id = a.id
JOIN users u ON a.email = u.email AND u.role = 'agent'
SET l.agent_id = u.id;

-- With the agent_id updated, we can now create a new foreign key constraint
-- that correctly links the listings table to the users table.
ALTER TABLE listings ADD CONSTRAINT fk_listings_agent_id FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL;

-- Drop the now-redundant tables
DROP TABLE IF EXISTS agents;
DROP TABLE IF EXISTS drivers;
