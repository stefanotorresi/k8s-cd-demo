FROM nginx:alpine

COPY nginx.conf /etc/nginx/default.tmpl

ENV PHP_FPM_HOST=127.0.0.1 PHP_FPM_PORT=9000

CMD [ "/bin/sh", "-c", "envsubst '${PHP_FPM_HOST} ${PHP_FPM_PORT}' < /etc/nginx/default.tmpl > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;' || cat /etc/nginx/conf.d/default.conf" ]
