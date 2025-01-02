FROM php:8.2-cli
RUN apt-get update
RUN apt-get -y install apt-utils
RUN docker-php-ext-install mysqli
RUN apt-get -y install cron   
RUN apt-get -y install nano 

#Angeblich geht das erst wenn man zur Laufzeit die Datei ändert.
RUN touch /var/log/cron.log

COPY . /usr/src/app

WORKDIR /usr/src/app
CMD echo "0 * * * * root /usr/local/bin/php /usr/src/app/cron.php" >> /etc/cron.d/cron && cron && tail -f /dev/null