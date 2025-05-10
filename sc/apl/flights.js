// flights.js - NoSQL injection vulnerability simulation

// Simulated flight data query function vulnerable to NoSQL injection
function findFlights(query) {
    // Vulnerable: directly using user input in query without sanitization
    const filter = { destination: query.destination };

    // Simulated database find operation
    return db.find(filter);
}

// Example usage: user input from URL parameter 'dest'
const urlParams = new URLSearchParams(window.location.search);
const dest = urlParams.get('dest');

// Vulnerable call allowing NoSQL injection
const flights = findFlights({ destination: dest });

console.log('Flights found:', flights);
