#!/bin/bash

<<<<<<< HEAD
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
=======
# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if mysql command is available
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}[-] mysql command not found. Please install MySQL client.${NC}"
    exit 1
fi

# Database credentials with defaults, can be overridden by environment variables
DB_NAME="${DB_NAME:-hive_airport}"
DB_USER="${DB_USER:-admin}"
DB_PASSWORD="${DB_PASSWORD:-password123}"
DB_HOST="${DB_HOST:-localhost}"
LOG_FILE="import_errors.log"
SQL_DIR="${SQL_DIR:-database}"

echo -e "${GREEN}[*] Starting Hive Airport DB setup...${NC}"

# Drop and recreate database and user with error handling
echo -e "${GREEN}[*] Dropping and recreating database and user...${NC}"
mysql -u root -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2> /dev/null || {
    echo -e "${RED}[-] Failed to drop database ${DB_NAME}. Check your MySQL root access.${NC}"
    exit 1
}
mysql -u root -e "CREATE DATABASE \`${DB_NAME}\`;" 2> /dev/null || {
    echo -e "${RED}[-] Failed to create database ${DB_NAME}.${NC}"
    exit 1
}
mysql -u root -e "DROP USER IF EXISTS '${DB_USER}'@'${DB_HOST}';" 2> /dev/null || true
mysql -u root -e "CREATE USER '${DB_USER}'@'${DB_HOST}' IDENTIFIED BY '${DB_PASSWORD}';" 2> /dev/null || {
    echo -e "${RED}[-] Failed to create user ${DB_USER}@${DB_HOST}.${NC}"
    exit 1
}
mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'${DB_HOST}';" 2> /dev/null || {
    echo -e "${RED}[-] Failed to grant privileges to user ${DB_USER}@${DB_HOST}.${NC}"
    exit 1
}
mysql -u root -e "FLUSH PRIVILEGES;" 2> /dev/null

echo -e "${GREEN}[+] Database and user setup complete.${NC}"

# Start importing SQL files
echo -e "${GREEN}[*] Importing SQL files from directory: ${SQL_DIR}${NC}"
> "$LOG_FILE"

if [ ! -d "$SQL_DIR" ]; then
    echo -e "${RED}[-] SQL directory '${SQL_DIR}' does not exist.${NC}"
    exit 1
fi

shopt -s nullglob
sql_files=("$SQL_DIR"/*.sql)
if [ ${#sql_files[@]} -eq 0 ]; then
    echo -e "${YELLOW}[!] No SQL files found in directory '${SQL_DIR}'.${NC}"
fi

for file in "${sql_files[@]}"; do
    basefile=$(basename "$file")
    echo -e "${YELLOW}[*] Importing $basefile...${NC}"

    FILE_PATH="$file"

    # Apply custom fix to customer.sql if matched
    if [[ "$basefile" == *"customer.sql" ]]; then
        TEMP_FILE="/tmp/fixed_customer.sql"
        sed 's/ALTER TABLE customers/ALTER TABLE passengers/g' "$FILE_PATH" > "$TEMP_FILE"
        FILE_PATH="$TEMP_FILE"
    fi

    if mysql --force -u "$DB_USER" -p"$DB_PASSWORD" -h "$DB_HOST" "$DB_NAME" < "$FILE_PATH" 2>>"$LOG_FILE"; then
        echo -e "${GREEN}[+] Imported: $basefile${NC}"
    else
        echo -e "${RED}[-] Error importing: $basefile (see $LOG_FILE)${NC}"
    fi
done

# Wrap-up
if [[ -s "$LOG_FILE" ]]; then
    echo -e "${RED}[!] Some imports had issues. Check: $LOG_FILE${NC}"
else
    echo -e "${GREEN}[✓] All imports successful. Cleaning up...${NC}"
    rm -f "$LOG_FILE"
fi

exit 0
>>>>>>> c495875 (Add profile_pic column migration and fix profile.php error)
