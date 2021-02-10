FROM vladyslavromanenko/marine-farming-api:base-43e0b492de7848e899722495f1b2b37a86cac18c

ENV MASTER_DIR=/var/www/marine-farming-api

COPY . ${MASTER_DIR}

WORKDIR ${MASTER_DIR}

RUN composer install --ignore-platform-reqs -d ${MASTER_DIR}

RUN php artisan key:generate

RUN chown -R www-data:www-data ${MASTER_DIR}/storage ${MASTER_DIR}/bootstrap/cache
RUN chmod -R 775 ${MASTER_DIR}/storage ${MASTER_DIR}/bootstrap/cache

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
