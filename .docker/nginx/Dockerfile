FROM nginx:1.17.6-alpine

COPY .docker/nginx/config/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /app/public

RUN apk add nano
