# 1st stage: Get latest dockle bin
FROM goodwithtech/dockle:latest AS dockle-base

# 2nd stage:
FROM keinos/mini-php7:v1.0.0-beta

COPY src /app/htdocs
COPY --from=dockle-base /usr/local/bin/dockle /usr/local/bin/dockle

ENTRYPOINT [ "runsvdir", "-P", "/etc/service" ]
