FROM php:5.6-apache
COPY conf/apache2.conf /etc/apache2/apache2.conf
COPY conf/php-devel-ini.conf /usr/local/etc/php/
#COPY ./index.php /var/www/html/
#ADD . /code
#WORKDIR /code
#RUN pip install -r requirements.txt

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
		libz-dev \
		libmemcached-dev \
		libpq-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt pgsql \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
	&& pecl install memcache \
	&& docker-php-ext-enable memcache


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

EXPOSE 80
