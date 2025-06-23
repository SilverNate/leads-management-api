-- This script is for the logging 'error_logs' database.

-- Create the database if it does not exist
-- For manual creation: CREATE DATABASE error_logs;

-- Connect to the error_logs database (assuming it's already created or being created)
-- \c error_logs;

-- Drop table if it already exists to ensure a clean slate for re-initialization
DROP TABLE IF EXISTS error_logs CASCADE;

-- Create the error_logs table
CREATE TABLE error_logs (
    id SERIAL PRIMARY KEY,
    error_message TEXT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    status_code INTEGER,
    timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Populate error_logs table with sample data
INSERT INTO error_logs (error_message, endpoint, status_code) VALUES
('Failed to connect to Mailchimp API', '/api/leads', 500),
('Validation failed: email already exists', '/api/leads', 422),
('Lead with ID 999 not found', '/api/leads/999', 404),
('Unauthorized access attempt', '/api/leads', 401),
('Redis connection error during cache operation', '/api/leads', 500);