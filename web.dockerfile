# start building image from nginx 1.13
FROM nginx:1.13

# copy site.conf from local directory to /etc/nginx/conf.d/default.conf
ADD ./site.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www