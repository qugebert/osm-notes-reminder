FROM php:8.2-cli
RUN apt-get update
RUN apt-get -y install apt-utils
RUN docker-php-ext-install mysqli
RUN apt-get -y install cron   
RUN apt-get -y install nano 
RUN apt-get -y install default-mysql-client jq
RUN apt-get -y install supervisor


#Angeblich geht das erst wenn man zur Laufzeit die Datei ändert.
RUN touch /var/log/cron.log
COPY --chown=www-data:www-data . /usr/src/app
WORKDIR /usr/src/app

RUN chmod +x /usr/src/app/entrypoint.sh

CMD ["/usr/src/app/entrypoint.sh"]
