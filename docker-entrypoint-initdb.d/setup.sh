#!/bin/bash
set -e

DB_NAME="hive_airport"
DB_USER="admin"
DB_PASSWORD="password123"
LOG_FILE="/var/log/import_errors.log"

echo "[*] Starting smart SQL import..."

for file in $(ls /database/*.sql | sort); do
    basefile=$(basename "$file")
    echo "[*] Importing $basefile..."

    FILE_PATH="$file"

    # Fix for customer.sql
    if [[ "$basefile" == *"customer.sql" ]]; then
        TEMP_FILE="/tmp/fixed_customer.sql"
        sed 's/ALTER TABLE customers/ALTER TABLE passengers/g' "$FILE_PATH" > "$TEMP_FILE"
        sed -i 's/CREATE TABLE IF NOT EXISTS flights (/CREATE TABLE IF NOT EXISTS flight_schedule (/g' "$TEMP_FILE"
        FILE_PATH="$TEMP_FILE"
    fi

    if ! mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME" < "$FILE_PATH"; then
        echo "[-] Failed to import $basefile" >> "$LOG_FILE"
    else
        echo "[+] Imported: $basefile"
    fi
done

echo "[✓] Database setup complete."
