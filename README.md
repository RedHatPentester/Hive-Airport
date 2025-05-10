# Hive Airport Vulnerable Web Application ✈️

Welcome to the **Hive Airport** vulnerable web application! This intentionally insecure PHP app is designed for security enthusiasts, developers, and penetration testers to explore and learn about common web vulnerabilities in a realistic airport management context. 🛠️

## What’s Inside? 🕵️‍♂️

- **Employees and Flights Database:** Simulated with real-world-like data to manage airport staff and flight schedules. 🗂️
- **Vulnerabilities Galore:** From Cross-Site Scripting (XSS) to Authentication Bypass, NoSQL Injection, and more — this app is a playground for discovering and exploiting security flaws. 🐞
- **Hidden Directories & Secrets:** Find the admin panel tucked away, exposed backup files, and hardcoded credentials waiting to be uncovered. 🔒
- **Outdated Libraries:** Old versions of jQuery and other scripts to test your skills against known exploits. 📜
- **Insecure Defaults:** Weak configurations and test credentials to challenge your security auditing abilities. ⚠️

## Features Overview 🚀

### Customer Portal 🛍️
- **Flight Booking:** Search and book flights with ease. ✈️
- **Feedback System:** Share your experience or report issues. 📝
- **Profile Management:** Update personal details and view booking history. 👤
- **Store:** Purchase travel-related items and souvenirs. 🛒

### Staff Portal 🧑‍💼
- **Flight Management:** Manage flight schedules, delays, and cancellations. 🕒
- **Messaging Center:** Communicate with other staff members securely. 💬
- **Security Alerts:** Stay updated on airport security notifications. 🚨

### Admin Dashboard 🛠️
- **User Management:** Add, edit, or remove staff and customer accounts. 👥
- **System Tools:** Access terminal commands and system logs. 🖥️
- **Activity Logs:** Monitor user activities for suspicious behavior. 📜
- **Maintenance Mode:** Enable or disable the site for updates. 🛑

### Security Challenges 🔐
- **SQL Injection:** Exploit poorly sanitized database queries. 💉
- **Cross-Site Scripting (XSS):** Inject malicious scripts into forms and URLs. 🖊️
- **Authentication Bypass:** Test weak login mechanisms. 🔓
- **File Upload Vulnerabilities:** Exploit unrestricted file uploads. 📂

### Hidden Gems 💎
- **Backup Files:** Discover sensitive information in exposed backups. 🗄️
- **Hardcoded Credentials:** Find and exploit weak default passwords. 🔑
- **Outdated Libraries:** Test vulnerabilities in old jQuery versions. 📜

---

## Why Use Hive Airport? 🤔

- **Learn by Doing:** Hands-on experience with real vulnerabilities in a controlled environment. 🧪
- **Test Your Tools:** Perfect for practicing with Burp Suite, OWASP ZAP, and other security testing tools. 🛠️
- **Understand Impact:** See how vulnerabilities can affect a complex system like an airport management app. 🌐

## Getting Started 🏁

1. **Setup the Environment:**
   - Run `setup.sh` to install dependencies, create the database, and import all necessary data. ⚙️
   - Start the PHP built-in server with:  
     `php -S 127.0.0.1:9000`

2. **Explore the App:**
   - Visit `http://127.0.0.1:9000` in your browser. 🌐
   - Try logging in, submitting feedback, and poking around the admin area. 🔍

3. **Try to Break It:**
   - Exploit the XSS on the main portal. 🖊️
   - Bypass authentication and escalate privileges. 🔓
   - Intercept and manipulate traffic with your favorite proxy tools. 🛠️

## Important Notes ⚠️

- **Do NOT use this app in production.** It is intentionally vulnerable and unsafe for real-world use. ❌
- **Use responsibly.** This app is for educational purposes only. 📚

---

## How to Contribute 🤝

We welcome contributions to improve the app or add new challenges. Feel free to fork the repository and submit a pull request. For major changes, please open an issue first to discuss what you would like to change. 🛠️

## Have Fun! 🎉

Dive in, experiment, and learn. Happy hacking! 🚀

---

For questions or contributions, please contact the maintainer. 📧
