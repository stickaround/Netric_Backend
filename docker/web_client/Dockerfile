FROM node:6

# Install build dependencies
RUN npm install -g grunt
RUN npm install -g grunt-cli
RUN npm install -g react-tools
RUN npm install -g bower
RUN npm install

# Copy over source
COPY dist/ /var/www/html/

# Copy over starting service
COPY bin/start.sh /start.sh

# Set permissions
RUN chmod 700 /start.sh

# Build
RUN npm rebuild node-sass
RUN cd /var/www/html/ && grunt build

# Expose the web server
EXPOSE 8000

CMD ["/start.sh"]