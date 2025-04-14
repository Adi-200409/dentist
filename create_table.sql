CREATE TABLE IF NOT EXISTS appointment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    appointmentdate DATE NOT NULL,
    appointmenttime TIME NOT NULL,
    area VARCHAR(100),
    city VARCHAR(100),
    state VARCHAR(100),
    postcode VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 