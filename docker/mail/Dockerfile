FROM debian:jessie

RUN apt-get update && apt-get install -y \
    postfix-pgsql \
    sasl2-bin \
    libsasl2-modules \
    libpam-pgsql \
    dovecot-pgsql \
    dovecot-imapd \
    dovecot-pop3d \
    rsyslog

ADD postfix /etc/postfix
ADD dovecot /etc/dovecot

RUN groupadd -g 5000 vmail && \
    useradd -g vmail -u 5000 vmail -d /home/vmail -m && \
    chgrp postfix /etc/postfix/pgsql-*.cf && \
    chgrp vmail /etc/dovecot/dovecot.conf && \
    chmod g+r /etc/dovecot/dovecot.conf

RUN postconf -e virtual_uid_maps=static:5000 && \
    postconf -e virtual_gid_maps=static:5000 && \
    postconf -e virtual_mailbox_domains=pgsql:/etc/postfix/pgsql-virtual-mailbox-domains.cf && \
    postconf -e virtual_mailbox_maps=pgsql:/etc/postfix/pgsql-virtual-mailbox-maps.cf && \
    postconf -e virtual_alias_maps=pgsql:/etc/postfix/pgsql-virtual-alias-maps.cf && \
    postconf -e virtual_transport=dovecot && \
    postconf -e dovecot_destination_recipient_limit=1 && \
    # specially for docker
    postconf -F '*/*/chroot = n'

RUN echo "dovecot   unix  -       n       n       -       -       pipe"  >> /etc/postfix/master.cf && \
    echo '    flags=DRhu user=vmail:vmail argv=/usr/lib/dovecot/deliver -d ${recipient}' >> /etc/postfix/master.cf

ADD bin/start.sh /start.sh

###############################################################################
# Make sure that windows has not screwed up the script line endings
###############################################################################
RUN apt-get install -y dos2unix
RUN find /etc/postfix -type f -print0 | xargs -0 dos2unix
RUN find /etc/dovecot -type f -print0 | xargs -0 dos2unix
RUN dos2unix /start.sh
RUN apt-get --purge remove -y dos2unix

# SMTP ports
EXPOSE 25
EXPOSE 587

# POP and IMAP ports
EXPOSE 110
EXPOSE 143
EXPOSE 995
EXPOSE 993

CMD sh start.sh