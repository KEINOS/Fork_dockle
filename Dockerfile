# 1st stage: Get latest dockle bin
FROM goodwithtech/dockle:latest AS dockle-base

# 2nd stage: Copy bin to Server Container Image
FROM keinos/mini-php7:latest

COPY --from=dockle-base /usr/local/bin/dockle /usr/local/bin/dockle
COPY src /app/htdocs
COPY conf/lighttpd.conf /etc/lighttpd/lighttpd.conf
COPY conf/php.ini /etc/php7/php.ini
COPY conf/www.conf /etc/php7/php-fpm.d/www.conf

RUN apk add --no-cache \
      curl \
      php7-ctype \
      php7-json \
      php7-curl \
    && mkdir -m 0777 -p /.cache \
    && mkdir -m 0777 -p /app/data \
    && rm -rf /var/cache/apk/*

CMD [ "runsvdir", "-P", "/etc/service" ]

HEALTHCHECK --interval=30m --timeout=3s --start-period=1m CMD curl -f http://localhost/ || exit 1

USER root
