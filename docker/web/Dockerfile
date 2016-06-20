FROM php:5.6-apache
COPY conf/apache2.conf /etc/apache2/apache2.conf
COPY conf/php-devel-ini.conf /usr/local/etc/php/

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
    && docker-php-ext-install -j$(nproc) iconv mcrypt pgsql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
	&& pecl install memcache \
	&& docker-php-ext-enable memcache \
	&& pecl install gearman \
    && docker-php-ext-enable gearman \
    && pecl install mogilefs-0.9.2 \
    && docker-php-ext-enable mogilefs


# install PHP PEAR extensions
RUN pear install mail \
	&& pear install Auth_SASL \
	&& pear install HTTP_Request2 \
	&& pear install File_IMC-0.5.0 \
	&& pear install mail_mime \
	&& pear install Net_SMTP

# Enable required apache modules
RUN ln -s /etc/apache2/mods-available/expires.load /etc/apache2/mods-enabled/
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

COPY dist/ /var/www/html/
COPY bin/netric-setup.sh /
COPY bin/netric-tests.sh /

# Run composer install to get all required dependencies
RUN cd /var/www/html && php composer.phar install

EXPOSE 80
EXPOSE 443

CMD ["apache2-foreground"]