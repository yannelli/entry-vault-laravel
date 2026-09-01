#!/usr/bin/env bash
# Idempotent Cloud Agent setup for the Entry Vault Laravel package.
# Installs PHP 8.4 (matching CI) with the required extensions, Composer, and
# then the project's Composer dependencies. Safe to run repeatedly.
set -euo pipefail

PHP_VERSION="8.4"

echo "==> Ensuring PHP ${PHP_VERSION} and Composer are installed"

if ! command -v php >/dev/null 2>&1 || ! php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'; then
    export DEBIAN_FRONTEND=noninteractive

    # PHP 8.4 is not in the default Ubuntu 24.04 repos; use the ondrej/php PPA.
    if [ ! -f /etc/apt/sources.list.d/ondrej-ubuntu-php-*.sources ] && \
       ! ls /etc/apt/sources.list.d/ 2>/dev/null | grep -qi 'ondrej'; then
        sudo add-apt-repository -y ppa:ondrej/php
    fi
    sudo apt-get update -qq

    # Extensions mirror the CI matrix (.github/workflows/run-tests.yml).
    sudo apt-get install -y --no-install-recommends \
        "php${PHP_VERSION}-cli" \
        "php${PHP_VERSION}-dom" \
        "php${PHP_VERSION}-curl" \
        "php${PHP_VERSION}-xml" \
        "php${PHP_VERSION}-mbstring" \
        "php${PHP_VERSION}-zip" \
        "php${PHP_VERSION}-sqlite3" \
        "php${PHP_VERSION}-bcmath" \
        "php${PHP_VERSION}-soap" \
        "php${PHP_VERSION}-intl" \
        "php${PHP_VERSION}-gd" \
        "php${PHP_VERSION}-imagick" \
        unzip
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "==> Installing Composer"
    php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
    php /tmp/composer-setup.php --quiet --install-dir=/tmp --filename=composer
    sudo mv /tmp/composer /usr/local/bin/composer
    rm -f /tmp/composer-setup.php
fi

echo "==> PHP: $(php --version | head -n1)"
echo "==> Composer: $(composer --version)"

echo "==> Installing Composer dependencies"
cd "$(dirname "$0")/.."
composer install --no-interaction --prefer-dist

echo "==> Setup complete"
