#!/bin/bash

GREEN="\033[1;32m"
RED="\033[1;31m"
RESET="\033[0m"

file=$(ls database/migrations/*_create_zaplish_tables.php 2>/dev/null)

if [ ! -f "$file" ]; then
  echo -e "${RED}✖ Migration file not found.${RESET}"
  exit 1
fi

filename=$(basename "$file")
migration_name="${filename%.php}"

cp .env .env.backup
cp .env.artisan .env

# Remove entry from the migrations table
php artisan tinker --execute="DB::table('migrations')->where('migration', '$migration_name')->delete();"

# Run just that one migration
php artisan migrate --path="database/migrations/$filename" && \
php artisan db:seed

# Clear all caches
php artisan optimize:clear
php artisan cache:clear

# Handle env files
cp .env.backup .env
rm .env.backup
echo -e "${GREEN}✔ Re-ran migration $filename and seeded database.${RESET}"
