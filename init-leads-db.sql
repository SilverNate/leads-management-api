-- This script is for the primary 'leads_db' database.

-- Create the database if it does not exist
-- This command is typically run by your database management system or Docker setup.
-- For manual creation: CREATE DATABASE leads_db;

-- Connect to the leads_db database (assuming it's already created or being created)
-- \c leads_db;

-- Drop table if it already exists to ensure a clean slate for re-initialization
DROP TABLE IF EXISTS leads CASCADE;

-- Create the leads table
CREATE TABLE leads (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    source VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create a function to update the updated_at column automatically
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create a trigger to call the update_updated_at_column function before each update
CREATE TRIGGER update_leads_updated_at
BEFORE UPDATE ON leads
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();


-- Populate leads table with sample data
INSERT INTO leads (name, email, phone, source, message) VALUES
('Alice Smith', 'alice.smith@example.com', '111-222-3333', 'website', 'Inquiry about pricing for enterprise plan.'),
('Bob Johnson', 'bob.j@example.com', '444-555-6666', 'social_media', 'Question about product features.'),
('Charlie Brown', 'charlie.b@example.com', NULL, 'referral', 'Looking for a demo.'),
('Diana Prince', 'diana.p@example.com', '777-888-9999', 'advertisement', 'Interested in partnership opportunities.'),
('Eve Adams', 'eve.a@example.com', '101-202-3030', 'website', 'General question about services.');
