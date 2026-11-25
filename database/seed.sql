-- MEMOLOG Seed Data (PostgreSQL)

INSERT INTO roles (role_name) VALUES
('Administrator'),
('Rider')
ON CONFLICT (role_name) DO NOTHING;

INSERT INTO delivery_statuses (status_name) VALUES
('Registered'),
('Assigned'),
('Out for Delivery'),
('Delivered (Pending Verification)'),
('Verified'),
('Verification Rejected'),
('Failed'),
('Cancelled')
ON CONFLICT (status_name) DO NOTHING;

-- Default password for all seeded accounts: Password123!
-- Hash generated with PHP password_hash('Password123!', PASSWORD_BCRYPT)
INSERT INTO users (role_id, full_name, email, password_hash, is_active) VALUES
((SELECT role_id FROM roles WHERE role_name = 'Administrator'), 'System Administrator', 'admin@memolog.local', '$2y$10$8Snvcb/W6wsXCVgMDdt4YOiKLfTM42ieR1/qqnyw2SP3xcaeepL2.', TRUE),
((SELECT role_id FROM roles WHERE role_name = 'Rider'), 'Marco Santos', 'marco@memolog.local', '$2y$10$8Snvcb/W6wsXCVgMDdt4YOiKLfTM42ieR1/qqnyw2SP3xcaeepL2.', TRUE),
((SELECT role_id FROM roles WHERE role_name = 'Rider'), 'Elena Cruz', 'elena@memolog.local', '$2y$10$8Snvcb/W6wsXCVgMDdt4YOiKLfTM42ieR1/qqnyw2SP3xcaeepL2.', TRUE)
ON CONFLICT (email) DO NOTHING;

INSERT INTO riders (user_id, rider_code, contact_no, is_active) VALUES
((SELECT user_id FROM users WHERE email = 'marco@memolog.local'), 'RDR-001', '09171234567', TRUE),
((SELECT user_id FROM users WHERE email = 'elena@memolog.local'), 'RDR-002', '09189876543', TRUE)
ON CONFLICT (rider_code) DO NOTHING;

INSERT INTO customers (full_name, contact_no, is_active) VALUES
('Juan Dela Cruz', '09175550101', TRUE),
('Maria Clara', '09175550102', TRUE),
('Jose Rizal', '09175550103', TRUE);

INSERT INTO settings (setting_key, setting_value, updated_by) VALUES
('delivery_rate', '75.00', (SELECT user_id FROM users WHERE email = 'admin@memolog.local')),
('daily_quota', '10', (SELECT user_id FROM users WHERE email = 'admin@memolog.local'))
ON CONFLICT (setting_key) DO NOTHING;

-- Sample Deliveries
INSERT INTO deliveries (tracking_code, customer_id, rider_id, status_id, parcel_description, delivery_address, proof_note, verified_by, verified_at, rate_applied)
VALUES
(
    'MEM-2026-0001',
    (SELECT customer_id FROM customers WHERE full_name = 'Juan Dela Cruz' LIMIT 1),
    (SELECT rider_id FROM riders WHERE rider_code = 'RDR-001'),
    (SELECT status_id FROM delivery_statuses WHERE status_name = 'Verified'),
    'Box of documents and contracts',
    '123 Mabini St, Quezon City',
    'Signed by recipient at front desk (ID: 8821)',
    (SELECT user_id FROM users WHERE email = 'admin@memolog.local'),
    NOW() - INTERVAL '1 day',
    75.00
),
(
    'MEM-2026-0002',
    (SELECT customer_id FROM customers WHERE full_name = 'Maria Clara' LIMIT 1),
    (SELECT rider_id FROM riders WHERE rider_code = 'RDR-001'),
    (SELECT status_id FROM delivery_statuses WHERE status_name = 'Delivered (Pending Verification)'),
    'Electronics parcel - Fragile',
    '456 Ayala Ave, Makati City',
    'Left with lobby guard, code 4490',
    NULL,
    NULL,
    NULL
),
(
    'MEM-2026-0003',
    (SELECT customer_id FROM customers WHERE full_name = 'Jose Rizal' LIMIT 1),
    (SELECT rider_id FROM riders WHERE rider_code = 'RDR-002'),
    (SELECT status_id FROM delivery_statuses WHERE status_name = 'Assigned'),
    'Medical supplies kit',
    '789 Taft Ave, Manila',
    NULL,
    NULL,
    NULL,
    NULL
),
(
    'MEM-2026-0004',
    (SELECT customer_id FROM customers WHERE full_name = 'Juan Dela Cruz' LIMIT 1),
    NULL,
    (SELECT status_id FROM delivery_statuses WHERE status_name = 'Registered'),
    'Office printer toner',
    '123 Mabini St, Quezon City',
    NULL,
    NULL,
    NULL,
    NULL
)
ON CONFLICT (tracking_code) DO NOTHING;

-- Sample Audit Trail Events
INSERT INTO delivery_events (delivery_id, status_id, changed_by, note)
SELECT d.delivery_id, s.status_id, u.user_id, 'Initial delivery registered'
FROM deliveries d
JOIN delivery_statuses s ON s.status_name = 'Registered'
JOIN users u ON u.email = 'admin@memolog.local'
WHERE d.tracking_code IN ('MEM-2026-0001', 'MEM-2026-0002', 'MEM-2026-0003', 'MEM-2026-0004');

INSERT INTO delivery_events (delivery_id, status_id, changed_by, note)
SELECT d.delivery_id, s.status_id, u.user_id, 'Assigned to rider'
FROM deliveries d
JOIN delivery_statuses s ON s.status_name = 'Assigned'
JOIN users u ON u.email = 'admin@memolog.local'
WHERE d.tracking_code IN ('MEM-2026-0001', 'MEM-2026-0002', 'MEM-2026-0003');

INSERT INTO delivery_events (delivery_id, status_id, changed_by, note)
SELECT d.delivery_id, s.status_id, u.user_id, 'Delivered with proof note'
FROM deliveries d
JOIN delivery_statuses s ON s.status_name = 'Delivered (Pending Verification)'
JOIN users u ON u.email = 'marco@memolog.local'
WHERE d.tracking_code IN ('MEM-2026-0001', 'MEM-2026-0002');

INSERT INTO delivery_events (delivery_id, status_id, changed_by, note)
SELECT d.delivery_id, s.status_id, u.user_id, 'Proof verified by Administrator; rate 75.00 applied'
FROM deliveries d
JOIN delivery_statuses s ON s.status_name = 'Verified'
JOIN users u ON u.email = 'admin@memolog.local'
WHERE d.tracking_code = 'MEM-2026-0001';
