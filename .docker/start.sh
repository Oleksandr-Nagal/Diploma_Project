#!/bin/bash
set -e

rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

sed -i "s/\${PORT}/${PORT:-8080}/g" /etc/apache2/sites-available/000-default.conf
echo "Listen ${PORT:-8080}" > /etc/apache2/ports.conf

php bin/console doctrine:schema:update --force --no-interaction
php bin/console doctrine:fixtures:load --append --no-interaction
php bin/console app:update-games --no-interaction

export MERCURE_PUBLISHER_JWT_KEY="${MERCURE_JWT_SECRET:-!ChangeThisMercureHubJWTSecretKey!}"
export MERCURE_SUBSCRIBER_JWT_KEY="${MERCURE_JWT_SECRET:-!ChangeThisMercureHubJWTSecretKey!}"
export MERCURE_TRANSPORT_URL="bolt:///tmp/mercure.db"

mercure run --config /var/www/html/.docker/Caddyfile &

sleep 2

apache2-foreground
