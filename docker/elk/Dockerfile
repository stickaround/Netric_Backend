FROM sebp/elk

# Add a gelf input listener to sebp/elk
COPY conf/03-input-gelf.conf /etc/logstash/conf.d/03-input-gelf.conf

# Add syslog input listener
COPY conf/04-input-syslog.conf /etc/logstash/conf.d/04-input-syslog.conf

# Overwrite the output file that sebp/elk adds because it has
# index => "%{[@metadata][beat]}-%{+YYYY.MM.dd}"
# Which breaks everything
COPY conf/30-output.conf /etc/logstash/conf.d/30-output.conf

# We made a change to get logstash to listen on IPV4 since sebp/elk defaulted to IPV6
COPY conf/logstash-init /etc/init.d/logstash

# The below is copied from the dockerfile of sebp/elk and is needed because we overwire the init
RUN sed -i -e 's#^LS_HOME=$#LS_HOME='$LOGSTASH_HOME'#' /etc/init.d/logstash \
 && chmod +x /etc/init.d/logstash

# If not set then gelf will only use IPV6 and not listen on IPV4
ENV _JAVA_OPTIONS "-Djava.net.preferIPv4Stack=true"

EXPOSE 12201

# syslog listeners
# We do not listen on the sandard 514 port since there is a high likelyhood that
# it will conflict with a local system resource and it is a protecte port
EXPOSE 5141
EXPOSE 5141/udp