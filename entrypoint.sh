#!/bin/bash
set -e

CONFIG_FILE="/run/secrets/mysqli_config_notes"
CONFIG=$(cat "$CONFIG_FILE")
HOST=$(echo "$CONFIG" | jq -r .host)
USER=$(echo "$CONFIG" | jq -r .user)
PASS=$(echo "$CONFIG" | jq -r .pass)
DB=$(echo "$CONFIG" | jq -r .db)

mysql -h "$HOST" -u "$USER" -p"$PASS" "$DB" < /usr/src/app/init.sql

echo "0 * * * * root /usr/local/bin/php /usr/src/app/cron.php" > /etc/cron.d/cron
cron

tail -f /var/log/cron.log
