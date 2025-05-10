#!/bin/bash

# Hive Airport Vulnerable Site Setup Script
# This script is intentionally vulnerable. Do not use in production environments.

echo -e "\033[36m🚀 Starting Hive Airport setup...\033[0m"

# Check if MySQL is installed and install if necessary
if ! command -v mysql &> /dev/null; then
    echo -e "\033[33m⚠ MySQL not found, attempting to install...\033[0m"
    if apt-cache show mysql-server &> /dev/null; then
        apt-get install -y mysql-server php php-mysql php-mysqli
    elif apt-cache show default-mysql-server &> /dev/null; then
        apt-get install -y default-mysql-server php php-mysql php-mysqli
    else
        echo -e "\033[31m✘ MySQL server packages not found. Exiting...\033[0m"
        exit 1
    fi
else
    echo -e "\033[32m✔ MySQL is already installed.\033[0m"
fi

# Start MySQL service if not running
if ! systemctl is-active --quiet mysql; then
    echo -e "\033[33m⚠ MySQL service not running, attempting to start...\033[0m"
    if ! sudo systemctl start mysql; then
        echo -e "\033[31m✘ Failed to start MySQL service. Exiting...\033[0m"
        exit 1
    fi
    echo -e "\033[32m✔ MySQL service started successfully.\033[0m"
fi

# Database credentials (intentionally hardcoded for vuln lab)
DB_NAME="hive_airport"
DB_USER="admin"
DB_PASS="password123"
DB_HOST="localhost"
LOG_FILE="import_errors.log"

# Create database and user (with sudo for root access)
echo -e "\033[33m🔑 Setting up database and user...\033[0m"

sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" || {
    echo -e "\033[31m✘ Failed to create database. Exiting...\033[0m"
    exit 1
}

echo -e "\033[32m✔ Database $DB_NAME created or already exists.\033[0m"

sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';" || {
    echo -e "\033[31m✘ Failed to create user. Exiting...\033[0m"
    exit 1
}

echo -e "\033[32m✔ User $DB_USER created or already exists.\033[0m"

sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'$DB_HOST';" || {
    echo -e "\033[31m✘ Failed to grant privileges. Exiting...\033[0m"
    exit 1
}

echo -e "\033[32m✔ Privileges granted to $DB_USER.\033[0m"

# Import database schema and data
MYSQL_IMPORT_CMD="mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME --force"
echo -e "\033[33m📦 Importing database schema and data...\033[0m"

> "$LOG_FILE"  # Clear the log file at the start

for sqlfile in database/setup.sql database/missing_tables.sql database/staff.sql database/customer.sql database/flights.sql database/admin_features.sql database/feedback.sql; do
    echo -e "\033[33m🔄 Processing $sqlfile...\033[0m"
    checksum_file=".${sqlfile//\//_}.md5"
    if [ ! -f "$sqlfile" ]; then
        echo -e "\033[31m⚠ $sqlfile not found, skipping.\033[0m"
        echo "$(date '+%Y-%m-%d %H:%M:%S') ⚠ $sqlfile not found, skipping." >> "$LOG_FILE"
        continue
    fi
    # Import the file if checksum has changed or is missing
    current_md5=$(md5sum "$sqlfile" | awk '{ print $1 }')
    if [ ! -f "$checksum_file" ] || [ "$current_md5" != "$(cat "$checksum_file")" ]; then
        if $MYSQL_IMPORT_CMD < "$sqlfile" 2>>"$LOG_FILE"; then
            echo -e "\033[32m✔ Successfully imported $sqlfile.\033[0m"
            echo "$current_md5" > "$checksum_file"
        else
            echo -e "\033[31m✘ Error importing $sqlfile. Check $LOG_FILE for details.\033[0m"
        fi
    else
        echo -e "\033[32m✔ $sqlfile unchanged, skipping import.\033[0m"
    fi
done

echo -e "\033[36m📄 Database import process complete. Check $LOG_FILE for any errors.\033[0m"

echo -e "\033[32m✔ Setup complete. You can now configure your web server to serve the site.\033[0m"
echo -e "Run: php -S 127.0.0.1:9000 to start the server."
