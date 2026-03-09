CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin', 'pharmacist') DEFAULT 'pharmacist',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE medicines (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(150) NOT NULL,
    category      VARCHAR(100) NOT NULL,
    quantity      INT          NOT NULL DEFAULT 0,
    price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date   DATE         NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id      INT          NOT NULL,
    customer_name    VARCHAR(100),
    quantity_sold    INT          NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);


INSERT INTO users (full_name, username, email, password, role) VALUES
('Administrator', 'admin', 'admin@pharmacy.com', '$2y$10$e0NRm5V3yGmz1234567890uQKj1bYbZmjXRNqYfZg5m8bXkOlzOCK', 'admin');

INSERT INTO medicines (medicine_name, category, quantity, price, expiry_date) VALUES
('Biogesic 500mg',    'Analgesic',        200, 5.50,  '2026-12-31'),
('Amoxicillin 500mg', 'Antibiotic',       150, 12.00, '2026-08-15'),
('Losartan 50mg',     'Antihypertensive', 100, 18.75, '2027-03-20'),
('Cetirizine 10mg',   'Antihistamine',     80, 7.00,  '2026-11-30'),
('Vitamin C 500mg',   'Supplement',       300, 4.00,  '2027-09-15');
