// users.js - Broken authentication simulation

// Simulated user login function with broken auth logic
function login(username, password) {
    // Hardcoded users
    const users = {
        admin: 'adminpass',
        user: 'userpass'
    };

    // Broken auth: password check is case-insensitive and allows empty password for 'user'
    if (users[username]) {
        if (username === 'user' && password === '') {
            return true; // Broken: empty password allowed for 'user'
        }
        if (users[username].toLowerCase() === password.toLowerCase()) {
            return true;
        }
    }
    return false;
}

// Example usage
const username = prompt('Username:');
const password = prompt('Password:');

if (login(username, password)) {
    console.log('Login successful');
} else {
    console.log('Login failed');
}
