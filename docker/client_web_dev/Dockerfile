FROM node:6

# This container is used to build and run unit tests in jenkins.
# It installs via NPM, then executes the tests and then closes with
# docker build .
# docker run -rm -v .:/var/www/thml/test/tmp

# Copy over source
COPY . /var/www/html

# Remove node modules in case it was built outside the container
RUN rm -rf /var/www/html/node_modules

# Install build dependencies
RUN npm install
RUN npm update

WORKDIR /var/www/html/

CMD ["npm", "test-single-run"]
