FROM php:8.2-cli

WORKDIR /app

COPY . .

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libpq-dev \
	&& docker-php-ext-install pdo pdo_pgsql \
	&& rm -rf /var/lib/apt/lists/*

RUN composer install --no-dev 2>/dev/null || true

EXPOSE $PORT

CMD php -S 0.0.0.0:${PORT:-8080} -t .
