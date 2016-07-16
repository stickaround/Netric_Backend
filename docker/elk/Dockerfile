FROM sebp/elk

# Add a gelf input listener to sebp/elk
ADD conf/03-gelf-input.conf /etc/logstash/conf.d/03-gelf-input.conf

# Overwrite the output file that sebp/elk adds because it has
# index => "%{[@metadata][beat]}-%{+YYYY.MM.dd}"
# Which breaks everything
ADD conf/30-output.conf /etc/logstash/conf.d/30-output.conf

# We made a change to get logstash to listen on IPV4 since sebp/elk defaulted to IPV6
ADD conf/logstash-init /etc/init.d/logstash

# The below is copied from the dockerfile of sebp/elk and is needed because we overwire the init
RUN sed -i -e 's#^LS_HOME=$#LS_HOME='$LOGSTASH_HOME'#' /etc/init.d/logstash \
 && chmod +x /etc/init.d/logstash

# If not set then gelf will only use IPV6 and not listen on IPV4
ENV _JAVA_OPTIONS "-Djava.net.preferIPv4Stack=true"

EXPOSE 12201