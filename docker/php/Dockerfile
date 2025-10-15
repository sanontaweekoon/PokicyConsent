FROM php:8.0-fpm-bullseye

ENV DEBIAN_FRONTEND=noninteractive

# 1) base deps
RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libicu-dev g++ curl ca-certificates gnupg2 \
    unixodbc-dev libgssapi-krb5-2; \
  docker-php-ext-install zip intl pdo; \
  rm -rf /var/lib/apt/lists/*

# 2) Microsoft repo + ODBC 17 (เสถียรกับ PHP 8.0 บน bullseye)
RUN set -eux; \
  mkdir -p /usr/share/keyrings; \
  curl -sSL https://packages.microsoft.com/keys/microsoft.asc \
    | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg; \
  echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/11/prod bullseye main" \
    > /etc/apt/sources.list.d/microsoft-prod.list; \
  apt-get update; \
  ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql17; \
  rm -rf /var/lib/apt/lists/*

# 3) เครื่องมือคอมไพล์ PHP extension + sqlsrv/pdo_sqlsrv (pin เวอร์ชัน)
RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
  pecl channel-update pecl.php.net; \
  printf "\n" | pecl install sqlsrv-5.10.1; \
  printf "\n" | pecl install pdo_sqlsrv-5.10.1; \
  docker-php-ext-enable sqlsrv pdo_sqlsrv; \
  rm -rf /tmp/pear; \
  apt-get purge -y --auto-remove $PHPIZE_DEPS; \
  rm -rf /var/lib/apt/lists/*

# 4) ติดตั้ง Composer แบบตรง (ไม่ใช้ multi-stage)
RUN set -eux; \
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php; \
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
  rm -f /tmp/composer-setup.php

WORKDIR /var/www/html
EXPOSE 9000
