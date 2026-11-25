-- MEMOLOG PostgreSQL Schema (1NF, 2NF, 3NF)

CREATE TABLE IF NOT EXISTS roles (
    role_id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    role_id INT NOT NULL REFERENCES roles(role_id) ON DELETE RESTRICT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS riders (
    rider_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE RESTRICT,
    rider_code VARCHAR(30) NOT NULL UNIQUE,
    contact_no VARCHAR(30) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS customers (
    customer_id SERIAL PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    contact_no VARCHAR(30) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS delivery_statuses (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS deliveries (
    delivery_id SERIAL PRIMARY KEY,
    tracking_code VARCHAR(40) NOT NULL UNIQUE,
    customer_id INT NOT NULL REFERENCES customers(customer_id) ON DELETE RESTRICT,
    rider_id INT NULL REFERENCES riders(rider_id) ON DELETE RESTRICT,
    status_id INT NOT NULL REFERENCES delivery_statuses(status_id) ON DELETE RESTRICT,
    parcel_description VARCHAR(255) NOT NULL,
    delivery_address TEXT NOT NULL,
    proof_note TEXT NULL,
    verified_by INT NULL REFERENCES users(user_id) ON DELETE RESTRICT,
    verified_at TIMESTAMPTZ NULL,
    rate_applied DECIMAL(10,2) NULL CHECK (rate_applied >= 0),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS delivery_events (
    event_id SERIAL PRIMARY KEY,
    delivery_id INT NOT NULL REFERENCES deliveries(delivery_id) ON DELETE CASCADE,
    status_id INT NOT NULL REFERENCES delivery_statuses(status_id) ON DELETE RESTRICT,
    changed_by INT NOT NULL REFERENCES users(user_id) ON DELETE RESTRICT,
    note TEXT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(60) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    updated_by INT NULL REFERENCES users(user_id) ON DELETE RESTRICT,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS rider_applications (
    application_id SERIAL PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    contact_no VARCHAR(30) NOT NULL,
    vehicle_type VARCHAR(60) NOT NULL DEFAULT 'Motorcycle',
    license_no VARCHAR(60) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Rejected')),
    admin_note TEXT NULL,
    reviewed_by INT NULL REFERENCES users(user_id) ON DELETE RESTRICT,
    reviewed_at TIMESTAMPTZ NULL,
    email_sent BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_deliveries_tracking_code ON deliveries(tracking_code);
CREATE INDEX IF NOT EXISTS idx_deliveries_status_id ON deliveries(status_id);
CREATE INDEX IF NOT EXISTS idx_deliveries_rider_id ON deliveries(rider_id);
CREATE INDEX IF NOT EXISTS idx_deliveries_customer_id ON deliveries(customer_id);
CREATE INDEX IF NOT EXISTS idx_deliveries_created_at ON deliveries(created_at);
CREATE INDEX IF NOT EXISTS idx_delivery_events_delivery_id ON delivery_events(delivery_id);
CREATE INDEX IF NOT EXISTS idx_rider_applications_status ON rider_applications(status);
