#Quickfix - Basebox for PHP7.2 Library now uses Debian "10" Buster, superceeding #libcurl3, stretch is most compatible at this time whilst devs workout backport.
#https://github.com/docker-library/php/issues/865

FROM php:7.2-apache-stretch

#Surpresses debconf complaints of trying to install apt packages interactively
#https://github.com/moby/moby/issues/4032#issuecomment-192327844

ARG DEBIAN_FRONTEND=noninteractive


RUN sed -i s/deb.debian.org/archive.debian.org/g /etc/apt/sources.list
RUN sed -i s/security.debian.org/archive.debian.org/g /etc/apt/sources.list
RUN sed -i s/stretch-updates/stretch/g /etc/apt/sources.list

RUN apt-get -y update --fix-missing --no-install-recommends
RUN apt-get -y upgrade


# Install useful tools
RUN apt-get -yq install apt-utils nano wget dialog cron nano net-tools wget libxrender1 xvfb wkhtmltopdf pdftk imagemagick

# Install important libraries
RUN apt-get -y install --fix-missing apt-utils build-essential git curl libcurl3 libcurl3-dev zip openssl

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install xdebug
RUN pecl install xdebug-2.6.0
RUN docker-php-ext-enable xdebug

# Install redis
RUN pecl install redis-4.3.0
RUN docker-php-ext-enable redis


RUN wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
RUN  tar xvf wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
RUN  mv wkhtmltox/bin/wkhtmlto* /usr/bin/


# Other PHP7 Extensions

RUN apt-get -y install libsqlite3-dev libsqlite3-0 mysql-client
RUN docker-php-ext-install pdo_mysql 
RUN docker-php-ext-install pdo_sqlite
RUN docker-php-ext-install mysqli

RUN docker-php-ext-install curl
RUN docker-php-ext-install tokenizer
RUN docker-php-ext-install json

RUN apt-get -y install zlib1g-dev
RUN docker-php-ext-install zip

RUN apt-get -y install libicu-dev
RUN docker-php-ext-install -j$(nproc) intl

RUN docker-php-ext-install mbstring
RUN docker-php-ext-install gettext
RUN docker-php-ext-install pcntl


RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ 
RUN docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install opcache

RUN apt-get -y update && apt install libaprutil1-dbd-mysql apache2-utils libapache2-mod-auth-cas  -y

# Enable apache modules
RUN a2enmod rewrite headers


# Enable apache modules
RUN a2enmod rewrite headers
RUN a2enmod authnz_ldap

RUN a2enmod dbd
RUN a2enmod authn_dbd
RUN a2enmod authz_dbd


RUN a2enmod  session
RUN a2enmod  session_cookie
RUN a2enmod  session_crypto
RUN a2enmod  auth_form
RUN a2enmod  request


# Enable, to cache auth info (for high traffic website,  lower db pressure), this is actually not implemented in our config files above.
RUN  a2enmod authn_socache
RUN  a2enmod session

 #RUN a2enmod session
 #RUN a2enmod session_cookie
 #RUN a2enmod request
 #RUN a2enmod auth_form

RUN  a2enmod proxy \
&& a2enmod proxy_http \
&& a2enmod ssl \
&& a2enmod rewrite \
&& a2enmod http2 \
&& a2enmod proxy_wstunnel


#COPY ./config/dbd_mysql.conf  /etc/apache2/conf-available/dbd_mysql.conf

# RUN a2enconf dbd_mysql

# Cleanup
RUN rm -rf /usr/src/*


RUN a2enmod ssl && a2enmod rewrite

# Give execution rights on the cron job

# RUN chmod 0644 /etc/cron.d/cache-cron

# Apply cron job
# RUN crontab /etc/cron.d/cache-cron





# Install librdkafka1, dependency of the PHP extension
RUN apt-get update ;\
    apt-get install -y --no-install-recommends unzip librabbitmq4 librabbitmq-dev ;\
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false ;\
    rm -rf /var/lib/apt/lists/*



# RUN pecl install amqp && echo "extension=amqp" > "/usr/local/etc/php/conf.d/amqp.ini"
# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs


# Run Webpack Encore
RUN npm run dev



EXPOSE 80
#EXPOSE 443

