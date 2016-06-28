FROM debian:jessie

RUN apt-get update && apt-get install -y \
    cpanminus \
    build-essential \
    sudo \
    libpq-dev \
    libdbd-pg-perl \
    postgresql-client \
    sysstat \
    sqlite3

RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir -p /etc/mogilefs
RUN mkdir -p /var/mogdata/dev1
RUN mkdir -p /var/mogdata/dev2

RUN cpanm install --force MogileFS::Server \
  && cpanm install DBD::SQLite \
  && cpanm install MogileFS::Utils

# Install old version of syscall since there is a bug with mogilefs
RUN cpanm http://search.cpan.org/CPAN/authors/id/B/BR/BRADFITZ/Sys-Syscall-0.23.tar.gz

RUN mogdbsetup --type=SQLite --yes --dbname=/var/mogdata/mogilefs.sqlite3

# Add user and set ownership
RUN adduser mogile --system --disabled-password
RUN chown -R mogile /var/mogdata

ADD conf/mogilefs.conf /etc/mogilefs/mogilefs.conf
ADD conf/mogilefsd.conf /etc/mogilefs/mogilefsd.conf
ADD conf/mogstored.conf /etc/mogilefs/mogstored.conf
ADD bin/start.sh /start.sh

# Tracker port
EXPOSE 7001

ENTRYPOINT ["/start.sh"]