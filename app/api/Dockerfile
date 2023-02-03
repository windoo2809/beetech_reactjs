FROM centos:7

WORKDIR /app

ENV TZ="Asia/Tokyo"

RUN yum update -y && \
  yum clean all

RUN yum install -y http://rpms.famillecollet.com/enterprise/remi-release-7.rpm && \
  yum clean all

RUN yum install -y httpd && \
  yum clean all

RUN yum install -y --enablerepo=remi-php80 \
  php php-cli php-common php-devel php-gd php-mbstring php-mcrypt php-mysqlnd php-opcache php-pdo php-pear \
  php-pecl-apfd php-pecl-http php-pecl-igbinary php-pecl-jsonc php-pecl-jsonc-devel php-pecl-json-post \
  php-pecl-msgpack php-pecl-propro php-pecl-raphf php-pecl-zip \
  php-process php-fpm \
  supervisor \
  vim net-tools \
  # 開発及びSTG環境
  php-pecl-xdebug \
  && \
  yum clean all

RUN ln -s /dev/stdout /var/log/httpd/access_log
RUN ln -s /dev/stderr /var/log/httpd/error_log
RUN ln -s /dev/stderr /var/log/php-fpm/error.log
RUN ln -s /dev/stderr /var/log/php-fpm/www-error.log

COPY conf/php.ini /etc/php.ini
COPY conf/php-fpm.conf /etc/php-fpm.conf
COPY conf/php-fpm.d/www.conf /etc/php-fpm.d/www.conf
COPY conf/supervisord.conf /etc/supervisord.conf
COPY conf/supervisord.d/laravel-worker.conf /etc/supervisord.d/laravel-worker.conf
COPY conf/conf.d/api.conf /etc/httpd/conf.d/api.conf
COPY conf/conf.d/php.conf /etc/httpd/conf.d/php.conf
COPY start.sh /start.sh
COPY install.sh /install.sh

# composerのインストール必要ない場合は外す
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

EXPOSE 80
RUN mkdir /run/php-fpm
RUN chmod 755 /start.sh
RUN chmod 755 /install.sh

ENTRYPOINT /start.sh
