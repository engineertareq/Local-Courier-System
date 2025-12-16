-- Create the database
CREATE DATABASE IF NOT EXISTS local_courier_db;
USE local_courier_db;

-- 1. Users Table (Admins, Staff, Regular Customers)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Riders Table (The Riders/Drivers)
CREATE TABLE riders (
    rider_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Link to users table for login
    vehicle_type ENUM('bike', 'bicycle', 'van', 'truck') DEFAULT 'bike',
    vehicle_plate_number VARCHAR(50),
    status ENUM('available', 'busy', 'offline') DEFAULT 'offline',
    current_latitude DECIMAL(10, 8),
    current_longitude DECIMAL(11, 8),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. Parcels Table (The Shipments)
CREATE TABLE parcels (
    parcel_id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL, 
    sender_name VARCHAR(100) NOT NULL,
    sender_phone VARCHAR(20) NOT NULL,
    sender_address TEXT NOT NULL,

    receiver_name VARCHAR(100) NOT NULL,
    receiver_phone VARCHAR(20) NOT NULL,
    receiver_address TEXT NOT NULL,
    
    -- Logistics Details
    weight_kg DECIMAL(5, 2), 
    delivery_type ENUM('Regular','Standard','Express') DEFAULT 'Standard',
    price DECIMAL(10, 2) NOT NULL,
    
    -- Status Management
    current_status ENUM('pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'returned', 'cancelled') DEFAULT 'pending',
    
    -- [FIXED HERE] Renamed from 'rider_rider_id' to match the Foreign Key below
    assigned_rider_id INT NULL, 
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Now this line works because 'assigned_rider_id' exists above
    FOREIGN KEY (assigned_rider_id) REFERENCES riders(rider_id) ON DELETE SET NULL
);

-- 4. Parcel History (Tracking Logs)
-- This allows customers to see the timeline of their package
CREATE TABLE parcel_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    parcel_id INT NOT NULL,
    status VARCHAR(50) NOT NULL, -- e.g., "Arrived at Hub", "Picked Up"
    description TEXT, -- Optional notes like "Customer not home"
    location VARCHAR(100), -- Where this event happened
    updated_by_user_id INT, -- Who updated this status?
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parcel_id) REFERENCES parcels(parcel_id) ON DELETE CASCADE
);

-- 5. Payments Table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    parcel_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash_on_delivery', 'online_gateway', 'bank_transfer') DEFAULT 'cash_on_delivery',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parcel_id) REFERENCES parcels(parcel_id) ON DELETE CASCADE
);