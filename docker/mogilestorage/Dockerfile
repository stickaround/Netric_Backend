FROM debian:jessie

RUN apt-get update && apt-get install -y \
    cpanminus \
    build-essential \
    sudo \
    libpq-dev \
    libdbd-pg-perl \
    postgresql-client \
    sysstat

RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN adduser mogile --system --disabled-password

RUN mkdir -p /etc/mogilefs
RUN mkdir -p /var/mogdata/dev1
RUN mkdir -p /var/mogdata/dev2
RUN chown mogile /var/mogdata/dev1/
RUN chown mogile /var/mogdata/dev2/

RUN cpanm install --force MogileFS::Server \
  && cpanm install DBD::Pg \
  && cpanm install MogileFS::Utils

ADD conf/mogilefs.conf /etc/mogilefs/mogilefs.conf
ADD conf/mogilefsd.conf /etc/mogilefs/mogilefsd.conf
ADD conf/mogstored.conf /etc/mogilefs/mogstored.conf

EXPOSE 7500
EXPOSE 7501

ENTRYPOINT ["mogstored", "-c", "/etc/mogilefs/mogstored.conf"]