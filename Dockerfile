FROM php:7.4-apache as base

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
    libsodium-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install -j$(nproc) iconv pgsql \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && pecl install channel://pecl.php.net/mcrypt \
    && docker-php-ext-enable mcrypt \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pcntl \
    && docker-php-ext-enable pcntl \
    && pecl install libsodium \
    && docker-php-ext-enable sodium \
    && pecl install mailparse \
    && docker-php-ext-enable mailparse

# Install GD
RUN docker-php-ext-configure gd \
    --with-jpeg=/usr/include/ \
    --with-freetype=/usr/include/

RUN docker-php-ext-install gd

# Install gearman since the pecl version will not work with PHP7
RUN cd /tmp \
    && git clone https://github.com/php/pecl-networking-gearman.git \
    && cd pecl-networking-gearman \
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
    && cd xhprof/ && git checkout v1.2 \
    && cd extension/ \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable xhprof
# This was causing a segfault
#    \
#    && docker-php-ext-enable xhprof

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

# Copy scripts for interacting with the sever
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

# Set workdir
WORKDIR /var/www/html

EXPOSE 80

HEALTHCHECK CMD bin/netric health/test

ENTRYPOINT ["/start.sh"]

###########################################################################
FROM base as development

###########################################################################
FROM development as test

# Copy everything
COPY . /var/www/html

# Make sure data/log is owned by www-data
RUN chown -R www-data:www-data /var/www/html/data/

# In a base well change this to ONBUILD
#ONBUILD RUN --mount=type=ssh composer install
RUN composer install

###########################################################################
FROM base as release

# TODO: Only copy production files
COPY . /var/www/html

# Make sure data/log is owned by www-data
RUN chown -R www-data:www-data /var/www/html/data/

# In a base well change this to ONBUILD
RUN composer install --no-dev --no-scripts
#ONBUILD RUN --mount=type=ssh composer install --no-dev --no-scripts --optimize-autoloader