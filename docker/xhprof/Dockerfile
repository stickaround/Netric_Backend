FROM php:7.1-apache

# install required php extensions
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
		libz-dev \
		libmemcached-dev \
		libpq-dev \
		libgearman-dev \
		libxml2-dev \
		libneon27-dev \
		unzip \
		git \
		curl \
		graphviz \
    && docker-php-ext-install -j$(nproc) iconv mcrypt pgsql pdo pdo_pgsql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install xhprof for php7
RUN cd /tmp \
    && git clone https://github.com/longxinH/xhprof.git \
    && cd xhprof/extension/ \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable xhprof

# Enable required apache modules
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

# Clone the latest source code for an updated xhprof UI
RUN  cd /var/www/ && git clone https://github.com/phacility/xhprof

# Copy configs
COPY conf/apache2.conf /etc/apache2/apache2.conf
COPY conf/php-ini.conf /usr/local/etc/php/php.ini
#COPY conf/config.php /var/www/xhprof_lib/config.php

# Make profiles directory - in practice this should be mounted
RUN mkdir -p /profiles

EXPOSE 80