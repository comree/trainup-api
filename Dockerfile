FROM php:8.2-cli

WORKDIR /app

COPY . .

RUN composer install --no-dev 2>/dev/null || true

EXPOSE $PORT

CMD php -S 0.0.0.0:${PORT:-8080} -t .
