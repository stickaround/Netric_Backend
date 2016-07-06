FROM node:6

# Install build dependencies
RUN npm install -g grunt
RUN npm install -g grunt-cli
RUN npm install -g react-tools
RUN npm install -g bower
RUN npm install

# Build
RUN grunt build

# Expose the web server
EXPOSE 8000

CMD ["grunt", "start"]