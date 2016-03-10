FROM php:5.6-apache
COPY conf/apache2.conf /etc/apache2/apache2.conf
COPY conf/php-devel-ini.conf /usr/local/etc/php/php.ini
#ADD . /code
#WORKDIR /code
#RUN pip install -r requirements.txt

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

EXPOSE 80
