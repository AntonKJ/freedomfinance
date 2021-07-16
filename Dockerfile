FROM php:7.2-fpm

# Install modules
RUN apt-get update && yes|apt-get install -y \
	apt-utils \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
	libzip-dev \
	libonig-dev \
	libpq-dev \
	zlib1g-dev \
        wget \
        git 
#            --no-install-recommends

RUN apt-get update && yes|apt-get install -y libpq-dev

RUN docker-php-ext-install pdo pgsql pdo_mysql pdo_pgsql zip exif \
	&& docker-php-ext-configure gd \
	&& docker-php-ext-install gd 

RUN pecl install -o -f xdebug \
    && rm -rf /tmp/pear

RUN usermod -u 1000 www-data

VOLUME /root/.composer

WORKDIR /home/test/

EXPOSE 9000
CMD ["php-fpm"]
