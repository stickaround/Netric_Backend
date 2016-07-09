FROM node:6

# Copy over source
COPY dist/ /var/www/html/
RUN rm -rf /var/www/html/node_modules

# Install build dependencies
RUN npm install

# Copy over starting service
COPY bin/start.sh /start.sh

# Set permissions
RUN chmod 700 /start.sh

# Expose the web server
EXPOSE 8080

CMD ["/start.sh"]