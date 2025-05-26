DROP TABLE IF EXISTS bookings;

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    flight_id INT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Confirmed',
    notes TEXT,
    FOREIGN KEY (username) REFERENCES customers(username),
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);

-- Sample bookings data
INSERT INTO bookings (username, flight_id, status, notes) VALUES
('johndoe', 1, 'Confirmed', 'Window seat requested'),
('janedoe', 2, 'Cancelled', 'Flight cancelled due to weather'),
('alice', 3, 'Confirmed', 'Vegetarian meal requested'),
('bob', 4, 'Confirmed', 'Extra baggage allowance'),
('charlie', 5, 'Pending', 'Awaiting payment confirmation'),
('david', 6, 'Confirmed', 'Aisle seat requested'),
('eve', 7, 'Confirmed', 'No special requests'),
('frank', 8, 'Cancelled', 'Flight delayed'),
('grace', 9, 'Confirmed', 'Extra legroom requested'),
('heidi', 10, 'Pending', 'Awaiting payment confirmation');
