FROM node:6

# Install build dependencies
RUN npm install -g react-tools
RUN npm install

# Copy over source
COPY dist/ /var/www/html/

# Copy over starting service
COPY bin/start.sh /start.sh

# Set permissions
RUN chmod 700 /start.sh

# Expose the web server
EXPOSE 8080

CMD ["/start.sh"]