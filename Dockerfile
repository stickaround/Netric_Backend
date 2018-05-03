FROM php:7.2-apache

###############################################################################
# Setup PHP and apache
###############################################################################

# install required php extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libz-dev \
    libmemcached-dev \
    libpq-dev \
    libgearman-dev \
    libxml2-dev \
    libneon27-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install -j$(nproc) iconv pgsql \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install channel://pecl.php.net/mcrypt-1.0.1 \
    && docker-php-ext-enable mcrypt \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pcntl \
    && docker-php-ext-enable pcntl \
    && pecl install mailparse \
    && docker-php-ext-enable mailparse

# Install gearman since the pecl version will not work with PHP7
RUN cd /tmp \
    && git clone https://github.com/wcgallego/pecl-gearman.git \
    && cd pecl-gearman \
    && git checkout gearman-2.0.3 \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable gearman

# Install mogilefs since the pecl version will not work with PHP7
RUN cd /tmp \
    && git clone https://github.com/lstrojny/pecl-mogilefs.git \
    && cd pecl-mogilefs \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable mogilefs

# Install xhprof for php7
RUN cd /tmp \
    && git clone https://github.com/longxinH/xhprof.git \
    && cd xhprof/extension/ \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable xhprof

# install PHP PEAR extensions
RUN pear install mail \
    && pear install Auth_SASL \
    && pear install HTTP_Request2 \
    && pear install File_IMC-0.5.0 \
    && pear install mail_mime \
    && pear install Net_SMTP

# Install composer
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer

# Enable required apache modules
RUN ln -s /etc/apache2/mods-available/expires.load /etc/apache2/mods-enabled/
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/
RUN ln -s /etc/apache2/mods-available/ssl.load /etc/apache2/mods-enabled/

# Copy configs for apache and php
COPY docker/server/conf/apache2.conf /etc/apache2/apache2.conf
COPY docker/server/conf/php-devel-ini.conf /usr/local/etc/php/php.ini

# Copy SSL
RUN mkdir -p /etc/apache2/ssl
COPY docker/server/ssl/gd_bundle.crt /etc/apache2/ssl/gd_bundle.crt
COPY docker/server/ssl/netric.crt /etc/apache2/ssl/netric.crt
COPY docker/server/ssl/netric.key /etc/apache2/ssl/netric.key

###############################################################################
# Copy files and run composer to install source
###############################################################################

COPY ./ /var/www/html/
COPY docker/server/bin/netric-setup.sh /
COPY docker/server/bin/netric-update.sh /
COPY docker/server/bin/netric-tests.sh /
COPY docker/server/bin/start.sh /
COPY docker/server/bin/start-daemon.sh /

# Perimissions
RUN chmod +x /netric-setup.sh
RUN chmod +x /netric-update.sh
RUN chmod +x /netric-tests.sh
RUN chmod +x /start.sh
RUN chmod +x /start-daemon.sh

# Make sure data/log is owned by www-data
RUN chown -R www-data:www-data /var/www/html/data/

# Run composer install to get all required dependencies
RUN cd /var/www/html && composer install && composer update

# Update logs to print to stdout so they can be shipped
RUN ln -sf /dev/stderr /var/log/netric
RUN chmod 777 /var/log/netric

EXPOSE 80
EXPOSE 443

CMD ["/start.sh"]