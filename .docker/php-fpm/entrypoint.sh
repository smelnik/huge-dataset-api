#!/bin/sh
set -e

#################################################
# Current script is written only for local usage #
#################################################

# Validate composer.lock
#composer validate --no-check-all --no-check-publish
#composer install --no-scripts --no-progress

# Prepare required directories
mkdir -p var/cache var/log
chmod -R 777 var/cache var/log

#php bin/console doctrine:migrations:migrate --no-interaction
exec "$@"
